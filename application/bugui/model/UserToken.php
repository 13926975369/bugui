<?php
/**
 * Created by PhpStorm.
 * User: 63254
 * Date: 2018/1/16
 * Time: 14:49
 */

namespace app\bugui\model;


use app\bugui\exception\LoginException;
use app\bugui\exception\TokenException;
use app\bugui\exception\WeChatException;
use app\bugui\validate\IDMustBeNumber;
use think\Db;
use think\Exception;

class UserToken extends Token
{
    protected $secret;
    protected $uid;
    protected $code;
    protected $wxAppID;
    protected $wxAppSecret;
    protected $wxLoginurl;

    public function gett($code){
        $this->code = $code;
        $this->wxAppID = config('wx.app_id');
        $this->wxAppSecret = config('wx.app_secret');
        $this->wxLoginurl = sprintf(config('wx.login_url'),
            $this->wxAppID,$this->wxAppSecret,$this->code);
        $result = curl_get($this->wxLoginurl);
        //返回的字符串变成数组,true是数组，false是对象
        $wxResult = json_decode($result, true);
        if (empty($wxResult)){
            throw new Exception('获取session_key及openID时异常，微信内部错误');
        }
        else{
            $loginFail = array_key_exists('errcode',$wxResult);
            if ($loginFail){
                $this->processLoginError($wxResult);
            }else{
                //检测没有报错的话就去取token
                return $this->grantToken($wxResult);
            }
        }
    }

    public function grantToken($wxResult){
        $openid = $wxResult['openid'];
        $user = Db::table('user')->where([
            'openid' => $openid
        ])->field('id')->find();
        if (!$user){
            $get_id = Db::table('user')->insertGetId([
                'openid' => $openid
            ]);
            $this->uid = $get_id;
            //1代表首次登录
            $type = 1;
        }else{
            $this->uid = $user['id'];
            //2代表再次登录
            $type = 2;
        }

        //这是一个拼接token的函数，32随机+时间戳+salt
        //key就是token，value包含uid，scope
        //拿到钥匙
        $key = $this->gettoken();
        $cachedValue['id'] = $this->uid;
        //scope为权限
        $cachedValue['secret'] = 16;
        $this->secret  = 16;
        $value = json_encode($cachedValue);
        //设置存活时间
        $expire_in = config('setting.token_expire_in');
        //存入缓存
        $request = cache($key, $value, $expire_in);
        if (!$request){
            throw new TokenException([
                'msg' => '服务器缓存异常',
            ]);
        }
        return [
            'token' => $key,
            'type' => $type
        ];
    }

    /*
     * 登录错误
     * @param    wxResult：微信小程序接口网页获取的信息
     * @return   抛出错误
     * */
    private function processLoginError($wxResult){
        throw new WeChatException([
            'msg' => $wxResult['errmsg']
        ]);
    }
}