<?php
/**
 * User: 伍先生
 * DateTime: 2020/8/30 13:47
 * Class:  Company
 * Info: 公司信息管理
 */

namespace app\admin\controller;


use app\jwcode\controller\AdminBasic;

class Company extends AdminBasic
{
    /**
     * Info: 公司信息
     * Argument :
     * User: 伍先生
     * DateTime: 2020/8/30 13:52
     * Function:  lists
     */
    public function lists(){
        if($this->request->post()){
            $postData = input('post.');

            $page = 0;
            $limit = 10;
            $where=[];
            if(input('?page') && input('?limit')){
                $page = ($postData['page']-1)*10;
                $limit = $postData['limit'];
            }

            $order = ['id'=>'desc','addtime'=>'desc'];
            if(input('?field') && input('?order')){
                $order = [$postData['field']=>$postData['order']];
            }
            $userlist = db('company');

            if(input('?username') && !empty($postData['username'])){
                $userlist->where('tissue|principal|email|mobile','like','%'.$postData['username'].'%');
            }

            $userlist = $userlist->where($where)
                ->alias('c')
                ->join('role r','r.id = c.role_id')
                ->limit($page,$limit)
                ->order($order)
                ->field('c.*,r.name as rolename')
                ->select();

            $count = db('company')->where($where)->count();
            return $this->tableList(0,'成功',$userlist,$count);
        }
        return $this->fetch();
    }

    public function add(){
        if($this->request->post()){
            $postData = input('post.');

            $rule =   [
                'tissue|组织'  => 'require|length:2,30',
                'principal|负责人' => 'require',
                'mobile'   => 'require|mobile',
                'role_id'   => 'require',
            ];

            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('',-1,$validate->getError(),"json");
            }

            $inertData['tissue'] = $postData['tissue'];
            $inertData['principal'] = $postData['principal'];
            $inertData['mobile'] = $postData['mobile'];
            $inertData['role_id'] = $postData['role_id'];

            if(isset($postData['site'])){
                $inertData['site'] = $postData['site'];
            }

            if(isset($postData['email'])){
                $inertData['email'] = $postData['email'];
            }

            if(isset($postData['describe'])){
                $inertData['describe'] = $postData['describe'];
            }


            $inertData['addtime'] = date('Y-m-d H:i:s');

            $bool = db('company')->insert($inertData);

            if($bool){
                $this->result('',1,'添加成功','json');
            }else{
                $this->result('',-1,'添加失败','json');
            }

        }

        $list = db('role')->select();
        $this->assign('role',$list);
        return $this->fetch();
    }

    public function edit(){
        if($this->request->post()){
            $postData = input('post.');
            $rule =   [
                'id'  => 'require',
                'tissue|组织'  => 'require|length:2,30',
                'principal|负责人' => 'require',
                'mobile'   => 'require|mobile',
                'role_id'   => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('',-1,$validate->getError(),"json");
            }

            $updataData['tissue'] = $postData['tissue'];
            $updataData['principal'] = $postData['principal'];
            $updataData['mobile'] = $postData['mobile'];
            $updataData['role_id'] = $postData['role_id'];

            if(isset($postData['site'])){
                $updataData['site'] = $postData['site'];
            }

            if(isset($postData['email'])){
                $updataData['email'] = $postData['email'];
            }

            if(isset($postData['describe'])){
                $updataData['describe'] = $postData['describe'];
            }

            $bool = db('company')->where('id',$postData['id'])->update($updataData);

            if($bool){
                $this->result('',1,'成功','json');
            }else{
                $this->result('',-1,'失败','json');
            }
        }

        $list = db('role')->select();
        $this->assign('role',$list);

        $id = input('id',0);
        $company = db('company')->where('id',$id)->find();
        $this->assign($company);
        return $this->fetch();
    }

    public function del(){
        $where['id'] = input('id');
        $bool = db('company')->where($where)->delete();

        if($bool){
            $this->result('',1,'成功');
        }else{
            $this->result('',-1,'失败');
        }
    }
}