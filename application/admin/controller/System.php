<?php
/**
 * Created by PhpStorm.
 * User: 伍先生
 * QQ  : 3383600886
 * Date: 2019/9/7
 * Time: 22:33
 * Info: 系统设置
 */

namespace app\admin\controller;


use app\jwcode\controller\AdminBasic;
use email\Email;

class System extends AdminBasic
{
    /**
     * Info: 网站SEO
     * Argument :
     * User: 伍先生
     * Date: 2019/9/8
     * Time: 15:13
     */
    public function seo(){
        if($this->request->post()){


            $postData = array_remove_empty(input('post.'));

            $rule =   [
                'title|标题'  => 'require|length:2,16',
                'keyword|关键字'   => 'require|length:2,50',
                'info|描述'   => 'require|length:2,100',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('',-1,$validate->getError());
            }



            $up['title'] = $postData['title'];
            $up['keyword'] = $postData['keyword'];
            $up['info'] = $postData['info'];

            $update['json'] = json_encode($up);

            $bool = db('System')->where('title','seo')->update($update);

            if($bool){
                $this->result('',1,'更新成功');
            }else{
                $this->result('',-1,'无数据');
            }


        }else{
            $data = db('System')->where('title','seo')->value('json');
            $data = json_decode($data,1);
            $this->assign($data);
            return  $this->fetch();
        }
    }

    /**
     * Info: 邮件设置
     * Argument :
     * User: 伍先生
     * Date: 2019/9/8
     * Time: 15:14
     */
    public function email(){
        if($this->request->post()){


            $postData = array_remove_empty(input('post.'));

            $rule =   [
                'launch|发件方名称'  => 'require|length:1,16',
                'site|邮箱地址'   => 'require|length:2,50',
                'host|服务器地址'   => 'require|length:2,100',
                'account|邮箱账号'   => 'require|length:2,100',
                'pass|邮箱密码'   => 'require|length:2,100'
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('',-1,$validate->getError());
            }



            $up['launch'] = $postData['launch'];
            $up['site'] = $postData['site'];
            $up['host'] = $postData['host'];
            $up['account'] = $postData['account'];
            $up['pass'] = $postData['pass'];

            $update['json'] = json_encode($up);

            $bool = db('System')->where('title','email')->update($update);

            if($bool){
                $this->result('',1,'更新成功');
            }else{
                $this->result('',-1,'无数据');
            }


        }else{
            $data = db('System')->where('title','email')->value('json');
            $data = json_decode($data,1);
            $this->assign($data);
            return  $this->fetch();
        }
    }


    /**
     * Info: 测试邮件
     * Argument :
     * User: 伍先生
     * Date: 2019/9/8
     * Time: 21:38
     */
    public function testEmail(){
        //邮件配置
        $data = db('System')->where('title','email')->value('json');
        $data = json_decode($data,1);
        $email = new Email();


        $bool = $email->emailSend($data['account'],$data['pass'],$data['site'],$data['host']);
        if($bool){
            $this->success('发送成功');
        }else{
            $this->error('发送失败');
        }

    }

    /**
     * Info: 站点设置
     * Argument :
     * User: 伍先生
     * Date: 2019/9/8
     * Time: 15:14
     */
    public function option(){

    }

    /**
     * Info: IP访问限制
     * Argument :
     * User: 伍先生
     * Date: 2019/9/8
     * Time: 15:16
     */
    public function ip(){
        if($this->request->post()){
            $list = db('ip_black')->select();

            $count = db('ip_black')->count();
            return $this->tableList(0,'成功',$list,$count);

        }else{
            return  $this->fetch();
        }
    }

    /**
     * Info: 添加 IP 访问限制
     * Argument :
     * User: 伍先生
     * Date: 2019/9/8
     * Time: 15:16
     */
    public function addip(){
        if($this->request->post()){

            $postData = array_remove_empty(input('post.'));

            $rule =   [
                'ip'  => 'require|ip',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);


            if (!$validate->check($postData)) {
                $this->result('',-1,$validate->getError());
            }

            $insData['ip'] = $postData['ip'];
            $insData['info'] = $postData['info']??'无';
            $insData['addtime'] = time();
            $bool = db('ip_black')->insert($insData);
            if($bool){
                $this->result('',1,'添加成功');
            }else{
                $this->result('',-1,'添加失败');
            }

        }else{
            return  $this->fetch();
        }
    }

    public function delip(){
        $id = input('id');
        $bool = db('ip_black')->where('id',$id)->delete();
        if($bool){
            $this->result('',1,'删除成功');
        }else{
            $this->result('',-1,'删除失败');
        }
    }

	/**
	 * Info: 添加 IP 访问限制
	 * Argument :
	 * User: 伍先生
	 * Date: 2019/9/8
	 * Time: 15:16
	 */
    public function website(){
        if($this->request->post()){


            $postData = array_remove_empty(input('post.'));

            $rule =   [
                'title|网站名称'  => 'require|length:2,16',
                'icp|ICP备'   => 'length:0,50',
                'security|公网安备'   => 'length:0,100',
                'email|邮箱'   => 'email',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);


            if (!$validate->check($postData)) {
                $this->result('',-1,$validate->getError());
            }



            $up['title'] = $postData['title'];
            $up['icp'] = $postData['icp']??'';
            $up['security'] = $postData['security']??'';
            $up['email'] = $postData['email'];

            $update['json'] = json_encode($up);

            $bool = db('System')->where('title','website')->update($update);

            if($bool){
                $this->result('',1,'更新成功');
            }else{
                $this->result('',-1,'更新失败');
            }


        }else{
            $data = db('System')->where('title','website')->value('json');
            $data = json_decode($data,1);
            $this->assign($data);
            return  $this->fetch();
        }
    }


    /**
     * Info: 支付配置
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/13 17:15
     * Function:  setpay
     */
    public function setpay()
    {
        if($this->request->post()){


            $postData = array_remove_empty(input('post.'));

            $rule =   [
                'wxpay|微信支付二维码'  => 'require',
                'alipay|支付宝支付二维码'   => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);


            if (!$validate->check($postData)) {
                $this->result('',-1,$validate->getError());
            }



            $up['wxpay'] = $postData['wxpay'];
            $up['alipay'] = $postData['alipay']??'';

            $update['json'] = json_encode($up);

            $bool = db('System')->where('title','pay')->update($update);

            if($bool){
                $this->result('',1,'更新成功','json');
            }else{
                $this->result('',-1,'更新失败','json');
            }


        }else{
            $data = db('System')->where('title','pay')->value('json');
            $data = json_decode($data,1);
            $this->assign($data);
            return  $this->fetch();
        }
    }

}