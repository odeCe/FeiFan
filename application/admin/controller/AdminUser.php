<?php
/**
 * Created by PhpStorm.
 * User: 伍先生
 * QQ  : 3383600886
 * Date: 2019/8/31
 * Time: 19:26
 * Info: 后台管理员操作
 */

namespace app\admin\controller;


use app\jwcode\controller\AdminBasic;

class AdminUser extends AdminBasic
{

    /**
     * Info: 管理员列表
     * Argument :
     * User: 伍先生
     * Date: 2019/8/29
     * Time: 23:25
     */
    public function adminList(){
        if($this->request->post()){
            $postData = input('post.');

            $page = 0;
            $limit = 10;
            if(input('?page') && input('?limit')){
                $page = ($postData['page']-1)*10;
                $limit = $postData['limit'];
            }

            $order = ['id'=>'desc','addtime'=>'desc'];
            if(input('?field') && input('?order')){
                $order = [$postData['field']=>$postData['order']];
            }


            $where['is_del'] = 1;

            $table = db('admin')->where('super',1);
            if(isset($postData['username'])){
                $table->where('account|username|email|mobile','like','%'.$postData['username'].'%');
            }
            $userlist= $table
                ->limit($page,$limit)
                ->order($order)
                ->select();

            $count = $table->count();
            return $this->tableList(0,'成功',$userlist,$count);
        }
        return $this->fetch();
    }

    /**
     * Info: 管理员查看
     * Argument :
     * User: 伍先生
     * Date: 2019/8/29
     * Time: 23:26
     */
    public function detail(){
        if($this->request->post()){
            $id = input('id');
            if($id){
                $data = db('admin')
                    ->where(['id'=>$id])
                    ->field('id,username,account,email,mobile,is_del,addtime,lasttime,first_ip,role,last_ip')
                    ->find();

                $this->result($data,1,'成功');
            }else{
                $this->result('',-1,'无数据');
            }


        }else{
            return  $this->fetch();
        }


    }

    /**
     * Info: 管理员添加
     * Argument :
     * User: 伍先生
     * Date: 2019/8/29
     * Time: 23:26
     */
    public function add(){
        if($this->request->post()){
            $postData = array_remove_empty(input('post.'));

            $rule =   [
                'account|账号'  => 'require|length:6,16',
                'username|用户名'   => 'require|length:2,10',
                'email'   => 'email',
                'pass' => 'require|length:6,16',
                'repass' => 'require|length:6,16',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('',-1,$validate->getError());
            }

            if(account_heavy($postData['account'])){
                $this->result('',-1,'该账号已存在');
            }

            if($postData['pass'] != $postData['repass']){
                $this->result('',-1,'两次密码不一样');
            }

            $inertData['super'] = 1;
            $inertData['role'] = 1;
            $inertData['account'] = $postData['account'];
            $inertData['username'] = $postData['username'];
            $inertData['email'] = $postData['email'];
            $inertData['password'] = set_password($postData['pass']);


            $inertData['addtime'] = $inertData['lasttime'] = time();
            $inertData['first_ip'] = $inertData['last_ip'] = get_client_ip();

            $bool = db('admin')->insert($inertData);

            if($bool){
                $this->result('',1,'会员添加成功');
            }else{
                $this->result('',-1,'会员添加失败');
            }



        }
        return $this->fetch();
    }

    /**
     * Info: 管理员修改
     * Argument :
     * User: 伍先生
     * Date: 2019/8/29
     * Time: 23:26
     */
    public function edit(){
        if($this->request->post()){
            $postData = input('post.');

            $updata = array_remove_empty($postData);

            if($updata['id'] && $updata['account']){

                if(isset($updata['pass'])){
                    $updata['password'] = set_password($updata['pass']);
                    unset($updata['pass']);
                }

                $where['id'] = $updata['id'];
                $where['account'] = $updata['account'];

                unset($updata['account']);
                $bool = db('admin')->where($where)->update($updata);
                if($bool){
                    $this->result('',1,'更新成功');
                }else{
                    $this->result('',-1,'更新失败');
                }

            }else{
                $this->result('',-1,'参数错误');
            }

        }else{
            return $this->fetch();
        }
    }

    /**
     * Info: 管理员删除
     * Argument :
     * User: 伍先生
     * Date: 2019/8/29
     * Time: 23:26
     */
    public function del(){
        $postData = input('post.');
        if(input('?id') && !empty($postData['id'])){
            db('admin')->where(['id'=>$postData['id']])->update(['is_del'=>-1]);

            db('session')->where('admin_id',$postData['id'])->delete();

            $this->result('',1,'已拉黑');
        }
        $this->result('',-1,'参数错误');
    }

    /**
     * Info: 管理员状态
     * Argument :
     * User: 伍先生
     * Date: 2019/8/29
     * Time: 23:26
     */
    public function state(){
        $postData = input('post.');

        if(input('?id') && !empty($postData['id'])){
            db('admin')->where(['id'=>$postData['id']])->update(['is_del'=>$postData['del']]);
            $this->result('',1,'成功');
        }
        $this->result('',-1,'参数错误');
    }


}