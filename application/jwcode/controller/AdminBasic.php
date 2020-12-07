<?php
/**
 * Created by PhpStorm.
 * User: 伍先生
 * QQ  : 3383600886
 * Date: 2019/8/29
 * Time: 22:48
 * Info: 说明
 */

namespace app\jwcode\controller;


use think\Controller;
use think\facade\View;
use think\Request;

class AdminBasic extends Controller
{

    public $uid = 0;
    public $super = 0;
    public $role = 0;
    public $data = 0;

    protected function initialize()
    {

        if(is_login() == false){
            if($this->request->isPost()){
                $this->result('',-1,"您已下线,请重新登录",'json');
            }
            $this->redirect('/admin/login/index');
        }

        //后台用户信息
        $data = db('admin')
            ->where('id',session('admin'))
            ->cache(60)->find();

        $this->data = $data;

        $this->uid = $data['id'];
        $this->super = $data['super'];
        $this->role = $data['role'];
        $this->assign('userinfo',$data);

        if($data['super'] == 2){
            $this->role = db('company')->where('id',$data['company_id'])->cache(60)->value("role_id");
        }


        //系统信息
        $data = db('System')->where('title','website')->cache(60)->value('json');
        $data = json_decode($data,1);
        $this->assign("website",$data);

        //后台权限
        $this->is_role();
        if($this->uid == 0 ||  $this->super == 0 ||  $this->role == 0 ) {
            $this->error("登录状态异常","https://www.baidu.com/");
        }
    }

    public function tableList($code=200,$msg='成功',$data=null,$count=0){

        if(!is_array($data)){
            $data = json_decode($data);
        }
        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['count'] = $count;
        $result['data'] = $data;
        return $result;
    }

    /**
     * Info: 获取当前访问路由
     * Argument :
     * User: 伍先生
     * Date: 2019/9/8
     * Time: 10:57
     */
    private function getActionUrl()
    {
        $module     = request()->module();
        $controller = request()->controller();
        $controller = strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $controller), "_"));
        $action     = request()->action();
        $url        = '/'.$module.'/'.$controller.'/'.$action;
        //return $url;
        return strtolower($url);
    }

    /**
     * Info: 权限操作
     * Argument :
     * User: 伍先生
     * Date: 2019/9/8
     * Time: 14:55
     */
    public function is_role(){
        $url  = $this->getActionUrl();
        $id = $this->role;
        $super = session('account')['super'];
        $authority = db('authority')->where('way',$url)->value('id');
        //没有记录的  谁都可以操作
        if($authority == NUll){
            return true;
        }

        if ($super == 1){
            return true;
        }

        $data= db('role')->where('id',$id)->cache(60)->find();
        if($data['state'] != 1){
            $this->error("没有权限操作","https://www.baidu.com/");
        }
        $role = $data['role'];

        $arr = explode(",", $role);

        if(in_array($authority,$arr)){
            return true;
        }else{
            $this->error('暂无权限操作');
        }



    }


}