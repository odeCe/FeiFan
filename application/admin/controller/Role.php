<?php
/**
 * Created by PhpStorm.
 * User: 伍先生
 * QQ  : 3383600886
 * Date: 2019/9/2
 * Time: 21:18
 * Info: 权限管理
 */

namespace app\admin\controller;


use app\jwcode\controller\AdminBasic;

class Role extends AdminBasic
{
    /**
     * Info: 角色列表
     * Argument :
     * User: 伍先生
     * Date: 2019/9/2
     * Time: 21:22
     */
    public function index(){
        if($this->request->post()){
            $postData = input('post.');

            $page = 0;
            $limit = 10;
            if(input('?page') && input('?limit')){
                $page = ($postData['page']-1)*10;
                $limit = $postData['limit'];
            }

            $list = db('role')->limit($page,$limit)->select();

            $count = db('role')->count();
            return $this->tableList(0,'成功',$list,$count);
        }


        return $this->fetch();
    }

    /**
     * Info: 添加角色
     * Argument :
     * User: 伍先生
     * Date: 2019/9/4
     * Time: 23:02
     */
    public function add(){
        if($this->request->post()){

            $dataPost = input('post.');

            $dataPost['role'] = implode(",", $dataPost['role']);
            $dataPost['addtime'] = time();

            $bool = db('role')->insert($dataPost);
            if($bool){
                $this->result($bool,1,'添加成功');
            }else{
                $this->result('',-1,'添加失败');
            }

        }else{

            $list = authority();
            $this->assign('list',$list);
            return $this->fetch();
        }
    }

    /**
     * Info: 修改角色
     * Argument :
     * User: 伍先生
     * Date: 2019/9/4
     * Time: 23:02
     */
    public function edit(){
        if($this->request->post()){

            $dataPost = input('post.');

            $dataPost['role'] = implode(",", $dataPost['role']);
            $dataPost['addtime'] = time();

            $bool = db('role')->where(['id'=>$dataPost['id']])->update($dataPost);
            if($bool){
                $this->result($bool,1,'添加成功');
            }else{
                $this->result('',-1,'添加失败');
            }

        }else{
            $id = input('id');
            $data = db('role')->where(['id'=>$id])->find();
            $list = authority();
            $this->assign('list',$list);
            $this->assign('data',$data);
            return $this->fetch();
        }
    }

    /**
     * Info: 修改角色状态
     * Argument :
     * User: 伍先生
     * Date: 2019/9/4
     * Time: 23:02
     */
    public function statu(){
        $postData = input('post.');

        //var_dump($postData);
        if(input('?id') && !empty($postData['id'])){
            db('role')->where(['id'=>$postData['id']])->update(['state'=>$postData['del']]);
            $this->result('',1,'成功');
        }
        $this->result('',-1,'参数错误');
    }

    /**
     * Info: 删除角色
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/12 22:52
     * Function:  del
     */
    public function del(){
        $dataPost = input('post.');

        $bool = db('role')->where(['id'=>$dataPost['id']])->delete();
        if($bool){
            $this->result($bool,1,'删除成功');
        }else{
            $this->result('',-1,'删除失败');
        }
    }


/******************************************************************************/

    public function kind(){
        if($this->request->post()){
            $postData = input('post.');

            $page = 0;
            $limit = 10;
            if(input('?page') && input('?limit')){
                $page = ($postData['page']-1)*10;
                $limit = $postData['limit'];
            }

            $bool = db('authority')->where(['grade'=>0])->limit($page,$limit)->select();
            $count = db('authority')->where(['grade'=>0])->count();
            if($bool){

                return $this->tableList(0,'成功',$bool,$count);
            }else{
                $this->result('',-1,'没有数据');
            }

        }else{
            return $this->fetch();
        }
    }


    public function addkind(){
        if($this->request->post()){

            $dataPost = input('post.');

            if(empty($dataPost['name'])){
                $this->result('',-1,'名称不能为空');
            }
            $dataPost['addtime'] = time();
            $bool = db('authority')->insert($dataPost);
            if($bool){
                $this->result($bool,1,'添加成功');
            }else{
                $this->result('',-1,'添加失败');
            }

        }else{
            return $this->fetch();
        }
    }

    public function editkind(){
        if($this->request->post()){

            $dataPost = input('post.');

            $bool = db('authority')->where(['id'=>$dataPost['id']])->update($dataPost);
            if($bool){
                $this->result($bool,1,'修改成功');
            }else{
                $this->result('',-1,'修改失败');
            }

        }else{
            $id = input('id');
            $data = db('authority')->where(['id'=>$id])->find();
            $this->assign($data);

            return $this->fetch();
        }
    }

    /**
     * Info: 分类显示
     * Argument :
     * User: 伍先生
     * Date: 2019/8/29
     * Time: 23:26
     */
    public function state(){
        $postData = input('post.');

        //var_dump($postData);
        if(input('?id') && !empty($postData['id'])){
            db('authority')->where(['id'=>$postData['id']])->update(['display'=>$postData['state']]);
            $this->result('',1,'成功');
        }
        $this->result('',-1,'参数错误');
    }


/*******************************************************************************/


    /**
     * Info: 权限管理
     * Argument :
     * User: 伍先生
     * Date: 2019/9/2
     * Time: 21:22
     */
    public function authority(){
        if($this->request->post()){
            $postData = input('post.');

            $page = 0;
            $limit = 10;
            if(input('?page') && input('?limit')){
                $page = ($postData['page']-1)*10;
                $limit = $postData['limit'];
            }

            $bool = db('authority')->where('grade','<>',0)->limit($page,$limit)->select();
            $count = db('authority')->where('grade','<>',0)->count();
            if($bool){

                return $this->tableList(0,'成功',$bool,$count);
            }else{
                $this->result('',-1,'没有数据');
            }

        }else{
            return $this->fetch();
        }
    }

    public function append(){
        if($this->request->post()){

            $dataPost = input('post.');
            if(empty($dataPost['name'])){
                $this->result('',-1,'名称不能为空');
            }
            if(empty($dataPost['way'])){
                $this->result('',-1,'规则不能为空');
            }
            $dataPost['addtime'] = time();
            $bool = db('authority')->insert($dataPost);
            if($bool){
                $this->result($bool,1,'添加成功');
            }else{
                $this->result('',-1,'添加失败');
            }

        }else{
            $list = db('authority')->where('grade',0)->select();
            $this->assign('list',$list);
            return $this->fetch();
        }
    }

    /**
     * Info: 修改权限
     * Argument :
     * User: 伍先生
     * Date: 2019/9/2
     * Time: 21:54
     */
    public function revamp(){
        if($this->request->post()){

            $dataPost = input('post.');



            $bool = db('authority')->where(['id'=>$dataPost['id']])->find();
            if($bool){
                $this->result($bool,1,'成功');
            }else{
                $this->result('',-1,'添加失败');
            }

        }else{

            $id = input('id');
            $data = db('authority')->where(['id'=>$id])->find();
            $this->assign('data',$data);


            $list = db('authority')->where('grade',0)->select();
            $this->assign('list',$list);

            return $this->fetch();
        }

    }

    /**
     * Info: 修改权限提交
     * Argument :
     * User: 伍先生
     * Date: 2019/9/2
     * Time: 23:29
     */
    public function revampPost(){
        $dataPost = input('post.');
        //var_dump($dataPost);die;
        $bool = db('authority')->where(['id'=>$dataPost['id']])->update($dataPost);
        if($bool){
            $this->result($bool,1,'修改成功');
        }else{
            $this->result('',-1,'修改失败');
        }

    }

    /**
     * Info: 删除权限
     * Argument :
     * User: 伍先生
     * Date: 2019/9/2
     * Time: 22:01
     */
    public function remove(){
        $dataPost = input('post.');

        $bool = db('authority')->where(['id'=>$dataPost['id']])->delete();
        if($bool){
            $this->result($bool,1,'删除成功');
        }else{
            $this->result('',-1,'删除失败');
        }
    }

    public function icon(){
        return $this->fetch();
    }
}