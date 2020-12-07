<?php
/**
 * User: 伍先生
 * DateTime: 2020/9/12 23:10
 * Class:  Register
 * Info:
 */

namespace app\admin\controller;


use think\captcha\Captcha;
use think\Controller;

class Register extends Controller
{
    public function initialize()
    {
        //系统信息
        $data = db('System')->where('title','website')->value('json');
        $data = json_decode($data,1);
        $this->assign("website",$data);
    }

    public function verify()
    {
        $captcha = new Captcha();
        return $captcha->entry();
    }


    public function index(){
        if($this->request->post()){

            $postData = array_remove_empty(input('post.'));

            $rule =   [
                'account|账号'  => 'require|length:6,16',
                'username|用户名'  => 'require|length:6,16',
                'password|设置密码' => 'require|length:6,16',
                'pass|确认密码' => 'require|length:6,16',
//                'vercode|验证码' => 'require|captcha',
                '__token__' => 'token',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $token = $this->request->token('__token__', 'sha1');
                $this->result($token,-1,$validate->getError(),'json');
            }

            if($postData['password'] != $postData['pass']){
                $token = $this->request->token('__token__', 'sha1');
                $this->result($token,-1,"两次密码不一致",'json');
            }

            $where['account'] = $postData['account'];

            if(account_heavy($postData['account'])){
                $token = $this->request->token('__token__', 'sha1');
                $this->result($token,-1,'该账号已被注册','json');
            }

            $ins['super'] = 2;
            $ins['account'] = $postData['account'];
            $ins['username'] = $postData['username'];
            $ins['password'] = set_password($postData['pass']);
            $ins['addtime'] = $ins['lasttime'] = time();
            $ins['first_ip'] = $ins['last_ip'] = get_client_ip();


            $data = db('admin')->insert($ins);
            if($data){
                $this->result(6,1,'注册成功','json');
            }else{
                $token = $this->request->token('__token__', 'sha1');
                $this->result($token,-1,'注册失败','json');
            }

        }else{
            $token = $this->request->token('__token__', 'sha1');
            $this->assign('token', $token);
            return $this->fetch();
        }
    }
}