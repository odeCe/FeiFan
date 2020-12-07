<?php
/**
 * Created by PhpStorm.
 * User: 伍先生
 * QQ  : 3383600886
 * Date: 2019/9/6
 * Time: 23:24
 * Info: 说明
 */

namespace app\admin\controller;




use think\captcha\Captcha;
use think\Controller;

class Login extends Controller
{
    public function initialize()
    {
        //系统信息
        $data = db('System')->where('title','website')->value('json');
        $data = json_decode($data,1);
        $this->assign("website",$data);
    }
    /**
     * Info: 登录页
     * Argument :
     * User: 伍先生
     * Date: 2019/9/6
     * Time: 23:25
     */
    public function index(){

        if(is_login()){
            $this->redirect('/admin/index/index');
        }

        if($this->request->post()){

            $postData = array_remove_empty(input('post.'));

            $rule =   [
                'username|账号'  => 'require|length:6,16',
                'password|密码' => 'require|length:6,16',
                'vercode|验证码' => 'require|captcha',
                '__token__' => 'token',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $token = $this->request->token('__token__', 'sha1');
                $this->result('',-1,$validate->getError(),'json');
            }

            $where['account'] = $postData['username'];
            $where['password'] = set_password($postData['password']);
            $data = db('admin')->where($where)->find();
            if($data){

                if ($data['is_del'] == 2){
                    $token = $this->request->token('__token__', 'sha1');
                    $this->result($token,-1,'你已被拉黑,请联系管理员','json');
                }

                if($data['super']==2 && empty($data['company_id'])){
                    $token = $this->request->token('__token__', 'sha1');
                    $this->result($token,-1,'请联系管理员,设置公司之后在登录','json');
                }

                $diMag = "账号为".$postData['username']."登录日期为".date("Y-m-d H:i:s");
                didi($diMag);
                $this->set($data);
                $this->result(6,1,'登录成功','json');
            }else{
                $token = $this->request->token('__token__', 'sha1');
                $this->result($token,-1,'登录失败','json');
            }

        }else{
            $token = $this->request->token('__token__', 'sha1');
            $this->assign('token', $token);
            return $this->fetch();
        }
    }

    public function verify()
    {
        $captcha = new Captcha();
        return $captcha->entry();
    }

    private function set($data){

	    $setups = db('System')->where('title','setup')->value('json');
	    $setup = $setups?json_decode($setups,1):1;


        cookie('admin',$data['id']);
        session('admin',$data['id']);
        session('account',$data);
        $id = $data['id'];
        $key = set_session();
	    $time= time() + ($setup['enter'] * 3600);
        $bool = db('session')->where('admin_id',$id)->update(['val'=>$key,'deltime'=>$time]);
        if(!$bool){
            db('session')->insert(['admin_id'=>$id,'val'=>$key,'deltime'=>$time]);
        }
        $updata['lasttime'] = time();
        $updata['last_ip'] = get_client_ip();
        db('admin')->where('id',$id)->update($updata);
    }

    /**
     * Info: 退出登录
     * Argument :
     * User: 伍先生
     * Date: 2019/9/7
     * Time: 0:47
     */
    public function dropout(){
        db('session')->where('admin_id',session('admin'))->update(['deltime'=>0]);
        $this->redirect('/admin/login/index');
    }

}