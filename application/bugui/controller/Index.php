<?php
/**
 * Created by PhpStorm.
 * User: 63254
 * Date: 2018/2/1
 * Time: 22:26
 */

namespace app\bugui\controller;
use app\bugui\exception\BaseException;
use app\bugui\exception\UpdateException;
use app\bugui\exception\UserException;
use app\bugui\model\AdminToken;
use app\bugui\model\Super;
use app\bugui\model\Token;
use app\bugui\model\User;
use app\bugui\model\UserToken;
use app\bugui\validate\LoginValidate;
use think\Cache;
use think\Collection;
use think\Db;
use think\Request;
use think\Validate;

class Index extends Collection
{
    /**
     *  $token
     *  $type
     *  $data
     */

    public function login(){
        $post = input('post.');
        if (!$post){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入任何参数！'
            ]));
        }
        if (!array_key_exists('data',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '第二项参数缺失，禁止请求！'
            ]));
        }
        $data = $post['data'];
        $TokenModel = new Token();
        //获得token
        $tk = $TokenModel->get_token($data['code']);
        return json_encode([
            'code' => 200,
            'msg' => $tk
        ]);
    }

    public function first_time(){
        $post = input('post.');
        if (!$post){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入任何参数！'
            ]));
        }
        if (!array_key_exists('data',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '第二项参数缺失，禁止请求！'
            ]));
        }
        $TokenModel = new Token();
        $data = $post['data'];
        $name = $data['name'];
        $number = $data['number'];
        $grade = $data['grade'];
        $phone = $data['phone'];
        $user = new User();
        $uid = $TokenModel->get_id();
        $check = Db::table('user')->where([
            'id' => $uid
        ])->field('name,number,grade,phone')->find();
        if ($check['name']==$name&&$check['number']==$number&&$check['grade']==$grade&&$check['phone']==$phone){
            return json_encode([
                'code' => 200,
                'msg' => '成功'
            ]);
        }else{
            $result = $user->save([
                'name'  => $name,
                'number' => $number,
                'grade' => $grade,
                'phone' => $phone
            ],['id' => $uid]);
            if (!$result){
                throw new UpdateException();
            }
            return json_encode([
                'code' => 200,
                'msg' => '成功'
            ]);
        }
    }

    public function publish_good(){
        $post = input('post.');
        if (!$post){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入任何参数！'
            ]));
        }
        if (!array_key_exists('data',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '第二项参数缺失，禁止请求！'
            ]));
        }
        $TokenModel = new Token();
        //接收参数
        $data = $post['data'];
        $name = $data['name'];
        $type = $data['type'];
        $description = $data['description'];
        $price = $data['price'];
        $new = $data['new'];
        $address = $data['address'];
        $cost = $data['cost'];
        $time = (int)time();
        $uid = $TokenModel->get_id();
        //接收图片
        $photo = Request::instance()->file('photo');
        if ($photo){
            $info = $photo->validate(['size'=> 7242880,'ext'=>'jpg,jpeg,png,bmp,gif'])->move('upload');
            if ($info && $info->getPathname()) {
                $url = $info->getPathname();
                $result = Db::table('good')
                    ->insert([
                        'name' => $name,
                        'type' => $type,
                        'description' => $description,
                        'publish_id' => $uid,
                        'photo' => $url,
                        'price' => $price,
                        'new' => $new,
                        'address' => $address,
                        'cost' => $cost,
                        'time' => $time
                    ]);
                if (!$result){
                    if (is_file(COMMON.$url)){
                        unlink(COMMON.$url);
                    }
                    throw new UpdateException();
                }
            } else {
                throw new BaseException([
                    'msg' => '请检验上传图片格式（jpg,jpeg,png,bmp,gif）！'
                ]);
            }
        }else{
            $result = Db::table('good')
                ->insert([
                    'name' => $name,
                    'type' => $type,
                    'description' => $description,
                    'publish_id' => $uid,
                    'price' => $price,
                    'new' => $new,
                    'address' => $address,
                    'cost' => $cost,
                    'time' => $time
                ]);
            if (!$result){
                throw new UpdateException();
            }
        }

        return json_encode([
            'code' => 200,
            'msg' => '发布成功！'
        ]);
    }

    public function show_class(){
        $uid = (new Token())->get_id();
        $result = Db::table('good')
            ->distinct('type')
            ->field('type')
            ->select();
        $r = [];
        $i = 0;
        foreach ($result as $v){
            $r[$i]['type'] = $v['type'];
            $i++;
        }

        return json_encode([
            'code' => 200,
            'msg' => $r
        ]);
    }

    public function show_class_good(){
        $post = input('post.');
        if (!$post){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入任何参数！'
            ]));
        }
        if (!array_key_exists('data',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '第二项参数缺失，禁止请求！'
            ]));
        }
        $data = $post['data'];
        $class = $data['type'];
        $uid = (new Token())->get_id();

        $result = Db::table('good')
            ->where([
                'type' => $class,
                'status' => 0
            ])
            ->select();
        $r = [];
        $i = 0;
        foreach ($result as $v){
            $c = Db::table('collect')
                ->where([
                    'u_id' => $uid,
                    'g_id' => $v['id']
                ])->find();
            if ($c){
                $r[$i]['is_collect'] = 1;
            }else{
                $r[$i]['is_collect'] = 0;
            }
            $r[$i]['id'] = $v['id'];
            $r[$i]['name'] = $v['name'];
            $r[$i]['type'] = $v['type'];
            $r[$i]['description'] = $v['description'];
            $r[$i]['publish_id'] = $v['publish_id'];
            $d = Db::table('user')
                ->where([
                    'id' => $v['publish_id']
                ])->find();
            $r[$i]['user_name'] = $d['name'];
            $r[$i]['user_grade'] = $d['grade'];
            $r[$i]['user_number'] = $d['number'];
            $r[$i]['user_phone'] = $d['phone'];
            $r[$i]['photo'] = config('setting.image_root').$v['phone'];
            $r[$i]['price'] = $v['price'];
            $r[$i]['new'] = $v['new'];
            $r[$i]['address'] = $v['address'];
            $r[$i]['cost'] = $v['cost'];
            $r[$i]['time'] = date('Y-m-d',$v['time']);
            $i++;
        }

        return json_encode([
            'code' => 200,
            'msg' => $r
        ]);
    }

    public function recommend(){
        $post = input('post.');
        if (!$post){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入任何参数！'
            ]));
        }
        $uid = (new Token())->get_id();

        $result = Db::table('good')
            ->order([
                'time' => 'desc',
                'status' => 0
            ])
            ->select();
        $r = [];
        $i = 0;
        foreach ($result as $v){
            $c = Db::table('collect')
                ->where([
                    'u_id' => $uid,
                    'g_id' => $v['id']
                ])->find();
            if ($c){
                $r[$i]['is_collect'] = 1;
            }else{
                $r[$i]['is_collect'] = 0;
            }
            $r[$i]['id'] = $v['id'];
            $r[$i]['name'] = $v['name'];
            $r[$i]['type'] = $v['type'];
            $r[$i]['description'] = $v['description'];
            $r[$i]['publish_id'] = $v['publish_id'];
            $d = Db::table('user')
                ->where([
                    'id' => $v['publish_id']
                ])->find();
            $r[$i]['user_name'] = $d['name'];
            $r[$i]['user_grade'] = $d['grade'];
            $r[$i]['user_number'] = $d['number'];
            $r[$i]['user_phone'] = $d['phone'];
            $r[$i]['photo'] = config('setting.image_root').$v['phone'];
            $r[$i]['price'] = $v['price'];
            $r[$i]['new'] = $v['new'];
            $r[$i]['address'] = $v['address'];
            $r[$i]['cost'] = $v['cost'];
            $r[$i]['time'] = date('Y-m-d',$v['time']);
            $i++;
        }

        return json_encode([
            'code' => 200,
            'msg' => $r
        ]);
    }

    public function collect(){
        $uid = (new Token())->get_id();
        $post = input('post.');
        if (!$post){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入任何参数！'
            ]));
        }
        if (!array_key_exists('data',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '第二项参数缺失，禁止请求！'
            ]));
        }
        $data = $post['data'];
        if (!array_key_exists('good_id',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入商品id'
            ]));
        }
        $good_id = $data['good_id'];
        $result = Db::table('collect')
            ->insert([
                'u_id' => $uid,
                'g_id' => $good_id
            ]);
        if (!$result){
            throw new UpdateException([
                'msg' => '收藏失败'
            ]);
        }
        return json_encode([
            'code' => 200,
            'msg' => '收藏成功'
        ]);
    }

    public function search(){
        $uid = (new Token())->get_id();
        $post = input('post.');
        if (!$post){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入任何参数！'
            ]));
        }
        if (!array_key_exists('data',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '第二项参数缺失，禁止请求！'
            ]));
        }
        $data = $post['data'];
        if (!array_key_exists('search_word',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入搜索关键词'
            ]));
        }
        $key = $data['search_word'];
        $result = Db::table('good')
            ->where('name','like','%'.$key.'%')
            ->select();
        if (!$result){
            exit(json_encode([
                'code' => 400,
                'msg' => '未找到'
            ]));
        }

        $r = [];
        $i = 0;
        foreach ($result as $v){
            $c = Db::table('collect')
                ->where([
                    'u_id' => $uid,
                    'g_id' => $v['id']
                ])->find();
            if ($c){
                $r[$i]['is_collect'] = 1;
            }else{
                $r[$i]['is_collect'] = 0;
            }
            $r[$i]['id'] = $v['id'];
            $r[$i]['name'] = $v['name'];
            $r[$i]['type'] = $v['type'];
            $r[$i]['description'] = $v['description'];
            $r[$i]['publish_id'] = $v['publish_id'];
            $d = Db::table('user')
                ->where([
                    'id' => $v['publish_id']
                ])->find();
            $r[$i]['user_name'] = $d['name'];
            $r[$i]['user_grade'] = $d['grade'];
            $r[$i]['user_number'] = $d['number'];
            $r[$i]['user_phone'] = $d['phone'];
            $r[$i]['photo'] = config('setting.image_root').$v['phone'];
            $r[$i]['price'] = $v['price'];
            $r[$i]['new'] = $v['new'];
            $r[$i]['address'] = $v['address'];
            $r[$i]['cost'] = $v['cost'];
            $r[$i]['time'] = date('Y-m-d',$v['time']);
            if ((int)$v['status']==1){
                $r[$i]['status'] = '已卖出';
            }else{
                $r[$i]['status'] = '未卖出';
            }

            $i++;
        }

        return json_encode([
            'code' => 200,
            'msg' => $r
        ]);
    }

    public function my_publish(){
        $uid = (new Token())->get_id();
        $result = Db::table('good')
            ->where([
                'publish_id' => $uid
            ])->select();
        if (!$result){
            exit(json_encode([
                'code' => 400,
                'msg' => '您尚未发布宝贝'
            ]));
        }

        $r = [];
        $i = 0;
        foreach ($result as $v){
            $c = Db::table('collect')
                ->where([
                    'u_id' => $uid,
                    'g_id' => $v['id']
                ])->find();
            if ($c){
                $r[$i]['is_collect'] = 1;
            }else{
                $r[$i]['is_collect'] = 0;
            }
            $r[$i]['id'] = $v['id'];
            $r[$i]['name'] = $v['name'];
            $r[$i]['type'] = $v['type'];
            $r[$i]['description'] = $v['description'];
            $r[$i]['publish_id'] = $v['publish_id'];
            $d = Db::table('user')
                ->where([
                    'id' => $v['publish_id']
                ])->find();
            $r[$i]['user_name'] = $d['name'];
            $r[$i]['user_grade'] = $d['grade'];
            $r[$i]['user_number'] = $d['number'];
            $r[$i]['user_phone'] = $d['phone'];
            $r[$i]['photo'] = config('setting.image_root').$v['phone'];
            $r[$i]['price'] = $v['price'];
            $r[$i]['new'] = $v['new'];
            $r[$i]['address'] = $v['address'];
            $r[$i]['cost'] = $v['cost'];
            $r[$i]['time'] = date('Y-m-d',$v['time']);
            if ((int)$v['status']==1){
                $r[$i]['status'] = '已卖出';
            }else{
                $r[$i]['status'] = '未卖出';
            }
            $i++;
        }

        return json_encode([
            'code' => 200,
            'msg' => $r
        ]);
    }

    public function delete_good(){
        $uid = (new Token())->get_id();
        $post = input('post.');
        if (!$post){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入任何参数！'
            ]));
        }
        if (!array_key_exists('data',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '第二项参数缺失，禁止请求！'
            ]));
        }
        $data = $post['data'];
        if (!array_key_exists('good_id',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入宝贝id'
            ]));
        }
        $good_id = $data['good_id'];

        $result = Db::table('good')
            ->where([
                'id' => $good_id,
                'publish_id' => $uid
            ])->find();
        if (!$result){
            throw new BaseException([
                'msg' => '未找到该宝贝，或者这件宝贝并非你发布的'
            ]);
        }
        //开启事务
        Db::startTrans();
        $r = Db::table('good')
            ->where([
                'id' => $good_id
            ])->delete();

        if (!$r){
            Db::rollback();
            throw new UpdateException([
                'msg' => '删除失败'
            ]);
        }

        $rr = Db::table('orders')
            ->where('good_id')
            ->delete();
        if (!$rr){
            Db::rollback();
            throw new UpdateException([
                'msg' => '删除失败'
            ]);
        }
        Db::commit();

        return json_encode([
            'code' => 200,
            'msg' => '删除成功'
        ]);
    }

    public function show_collect(){
        $uid = (new Token())->get_id();
        $rr = Db::table('collect')
            ->where([
                'u_id' => $uid
            ])->select();
        if (!$rr){
            exit(json_encode([
                'code' => 400,
                'msg' => '您尚未收藏宝贝'
            ]));
        }
        $r = [];
        $i = 0;
        foreach ($rr as $k){
            $id = $k['g_id'];
            $v = Db::table('good')
                ->where([
                    'id' => $id
                ])->find();
            $c = Db::table('collect')
                ->where([
                    'u_id' => $uid,
                    'g_id' => $v['id']
                ])->find();
            if ($c){
                $r[$i]['is_collect'] = 1;
            }else{
                $r[$i]['is_collect'] = 0;
            }
            $r[$i]['id'] = $v['id'];
            $r[$i]['name'] = $v['name'];
            $r[$i]['type'] = $v['type'];
            $r[$i]['description'] = $v['description'];
            $r[$i]['publish_id'] = $v['publish_id'];
            $d = Db::table('user')
                ->where([
                    'id' => $v['publish_id']
                ])->find();
            $r[$i]['user_name'] = $d['name'];
            $r[$i]['user_grade'] = $d['grade'];
            $r[$i]['user_number'] = $d['number'];
            $r[$i]['user_phone'] = $d['phone'];
            $r[$i]['photo'] = config('setting.image_root').$v['phone'];
            $r[$i]['price'] = $v['price'];
            $r[$i]['new'] = $v['new'];
            $r[$i]['address'] = $v['address'];
            $r[$i]['cost'] = $v['cost'];
            $r[$i]['time'] = date('Y-m-d',$v['time']);
            if ((int)$v['status']==1){
                $r[$i]['status'] = '已卖出';
            }else{
                $r[$i]['status'] = '未卖出';
            }
            $i++;
        }

        return json_encode([
            'code' => 200,
            'msg' => $r
        ]);
    }

    public function delete_collect(){
        $uid = (new Token())->get_id();
        $post = input('post.');
        if (!$post){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入任何参数！'
            ]));
        }
        if (!array_key_exists('data',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '第二项参数缺失，禁止请求！'
            ]));
        }
        $data = $post['data'];
        if (!array_key_exists('good_id',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入宝贝id'
            ]));
        }
        $good_id = $data['good_id'];

        $check =  Db::table('collect')
            ->where([
                'u_id' => $uid,
                'g_id' => $good_id
            ])->delete();
        if (!$check){
            throw new UpdateException([
                'msg' => '取消收藏失败'
            ]);
        }
        return json_encode([
            'code' => 200,
            'msg' => '成功'
        ]);
    }
    //获取消息
    public function get_message(){
        $uid = (new Token())->get_id();
        $result = Db::table('message')
            ->where([
                'u_id' => $uid
            ])->order([
                'time' => 'desc'
            ])->select();
        if (!$result){
            exit(json_encode([
                'code' => 400,
                'msg' => '暂无消息'
            ]));
        }
        $r = [];
        $i = 0;
        foreach ($result as $v){
            $r[$i]['id'] = $v['id'];
            $r[$i]['text'] = $v['text'];
            $r[$i]['u_id'] = $v['u_id'];
            $r[$i]['time'] = date('Y-m-d',$v['time']);
            $i++;
        }

        return json_encode([
            'code' => 200,
            'msg' => $r
        ]);
    }
    //确认购买
    public function confirm(){
        $uid = (new Token())->get_id();
        $post = input('post.');
        if (!$post){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入任何参数！'
            ]));
        }
        if (!array_key_exists('data',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '第二项参数缺失，禁止请求！'
            ]));
        }
        $data = $post['data'];
        if (!array_key_exists('good_id',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入宝贝id'
            ]));
        }
        $good_id = $data['good_id'];

        $check = Db::table('good')
            ->where([
                'id' => $good_id
            ])->find();
        $s_id = $check['publish_id'];
        $b_id = $uid;
        $time = time();


        $result = Db::table('orders')
            ->insert([
                'good_id' => $good_id,
                's_id' => $s_id,
                'b_id' => $b_id,
                'status' => '确认购买',
                'time' => $time
            ]);
        if (!$result){
            throw new UpdateException([
                'msg' => '生成订单失败'
            ]);
        }

        return json_encode([
            'code' => 200,
            'msg' => '成功'
        ]);
    }
    //发货
    public function deliver(){
        $uid = (new Token())->get_id();
        $post = input('post.');
        if (!$post){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入任何参数！'
            ]));
        }
        if (!array_key_exists('data',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '第二项参数缺失，禁止请求！'
            ]));
        }
        $data = $post['data'];
        if (!array_key_exists('good_id',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入宝贝id'
            ]));
        }
        $good_id = $data['good_id'];


        $check = Db::table('orders')
            ->where([
                'good_id' => $good_id,
                's_id' => $uid
            ])->find();
        $status = $check['status'];
        if ($status != '已发货'){
            $result = Db::table('orders')
                ->where([
                    'good_id' => $good_id,
                    's_id' => $uid
                ])
                ->update([
                    'status' => '已发货'
                ]);
            if (!$result){
                throw new UpdateException([
                    'msg' => '确认失败'
                ]);
            }
        }

        return json_encode([
            'code' => 200,
            'msg' => '成功'
        ]);
    }

    //收货
    public function take(){
        $uid = (new Token())->get_id();
        $post = input('post.');
        if (!$post){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入任何参数！'
            ]));
        }
        if (!array_key_exists('data',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '第二项参数缺失，禁止请求！'
            ]));
        }
        $data = $post['data'];
        if (!array_key_exists('good_id',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入宝贝id'
            ]));
        }
        $good_id = $data['good_id'];


        $check = Db::table('orders')
            ->where([
                'good_id' => $good_id,
                'b_id' => $uid
            ])->find();
        $status = $check['status'];
        if ($status != '已收货'){
            $result = Db::table('orders')
                ->where([
                    'good_id' => $good_id,
                    'b_id' => $uid
                ])
                ->update([
                    'status' => '已收货'
                ]);
            if (!$result){
                throw new UpdateException([
                    'msg' => '确认失败'
                ]);
            }
        }

        return json_encode([
            'code' => 200,
            'msg' => '成功'
        ]);
    }

    //评论
    public function comment(){
        $uid = (new Token())->get_id();
        $post = input('post.');
        if (!$post){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入任何参数！'
            ]));
        }
        if (!array_key_exists('data',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '第二项参数缺失，禁止请求！'
            ]));
        }
        $data = $post['data'];
        if (!array_key_exists('good_id',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入宝贝id'
            ]));
        }
        if (!array_key_exists('comment',$post)){
            exit(json_encode([
                'code' => 403,
                'msg' => '未传入评论内容'
            ]));
        }
        $good_id = $data['good_id'];
        $comment = $data['comment'];


        $check = Db::table('good')
            ->where([
                'id' => $good_id,
            ])->find();
        $publish_id = $check['publish_id'];
        $status = $check['status'];


        Db::startTrans();
        $r = Db::table('comment')
            ->insert([
                's_id' => $publish_id,
                'b_id' => $uid,
                'comment' => $comment
            ]);
        if (!$r){
            Db::rollback();
            throw new UpdateException([
                'msg' => '评论失败'
            ]);
        }

        if ($status != '已评论'){
            $result = Db::table('orders')
                ->where([
                    'good_id' => $good_id,
                    'b_id' => $uid
                ])
                ->update([
                    'status' => '已评论'
                ]);
            if (!$result){
                Db::rollback();
                throw new UpdateException([
                    'msg' => '评论失败'
                ]);
            }
        }
        Db::commit();

        return json_encode([
            'code' => 200,
            'msg' => '成功'
        ]);
    }

    //我卖出的
    public function my_out(){
        $uid = (new Token())->get_id();
        $check = Db::table('orders')
            ->where([
                's_id' => $uid
            ])->select();
        if (!$check){
            exit(json_encode([
                'code' => 403,
                'msg' => '还没有卖出的宝贝'
            ]));
        }
        $r = [];
        $i = 0;
        foreach ($check as $k){
            $good_id = $k['good_id'];
            $v = Db::table('good')
                ->where([
                    'id' => $good_id
                ])->find();
            $c = Db::table('collect')
                ->where([
                    'u_id' => $uid,
                    'g_id' => $v['id']
                ])->find();
            if ($c){
                $r[$i]['is_collect'] = 1;
            }else{
                $r[$i]['is_collect'] = 0;
            }
            $r[$i]['id'] = $v['id'];
            $r[$i]['name'] = $v['name'];
            $r[$i]['type'] = $v['type'];
            $r[$i]['description'] = $v['description'];
            $r[$i]['publish_id'] = $v['publish_id'];
            $d = Db::table('user')
                ->where([
                    'id' => $v['publish_id']
                ])->find();
            $r[$i]['user_name'] = $d['name'];
            $r[$i]['user_grade'] = $d['grade'];
            $r[$i]['user_number'] = $d['number'];
            $r[$i]['user_phone'] = $d['phone'];
            $r[$i]['photo'] = config('setting.image_root').$v['phone'];
            $r[$i]['price'] = $v['price'];
            $r[$i]['new'] = $v['new'];
            $r[$i]['address'] = $v['address'];
            $r[$i]['cost'] = $v['cost'];
            $r[$i]['time'] = date('Y-m-d',$v['time']);
            $r[$i]['status'] = $k['status'];
            $i++;
        }
    }

    //我买到的
    public function my_in(){
        $uid = (new Token())->get_id();
        $check = Db::table('orders')
            ->where([
                'b_id' => $uid
            ])->select();
        if (!$check){
            exit(json_encode([
                'code' => 403,
                'msg' => '还没有卖出的宝贝'
            ]));
        }
        $r = [];
        $i = 0;
        foreach ($check as $k){
            $good_id = $k['good_id'];
            $v = Db::table('good')
                ->where([
                    'id' => $good_id
                ])->find();
            $c = Db::table('collect')
                ->where([
                    'u_id' => $uid,
                    'g_id' => $v['id']
                ])->find();
            if ($c){
                $r[$i]['is_collect'] = 1;
            }else{
                $r[$i]['is_collect'] = 0;
            }
            $r[$i]['id'] = $v['id'];
            $r[$i]['name'] = $v['name'];
            $r[$i]['type'] = $v['type'];
            $r[$i]['description'] = $v['description'];
            $r[$i]['publish_id'] = $v['publish_id'];
            $d = Db::table('user')
                ->where([
                    'id' => $v['publish_id']
                ])->find();
            $r[$i]['user_name'] = $d['name'];
            $r[$i]['user_grade'] = $d['grade'];
            $r[$i]['user_number'] = $d['number'];
            $r[$i]['user_phone'] = $d['phone'];
            $r[$i]['photo'] = config('setting.image_root').$v['phone'];
            $r[$i]['price'] = $v['price'];
            $r[$i]['new'] = $v['new'];
            $r[$i]['address'] = $v['address'];
            $r[$i]['cost'] = $v['cost'];
            $r[$i]['time'] = date('Y-m-d',$v['time']);
            $r[$i]['status'] = $k['status'];
            $i++;
        }
    }
}