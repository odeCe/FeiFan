<?php
/**
 * User: 伍先生
 * DateTime: 2019/12/12 17:48
 * Class:  Article
 * Info: 文章管理
 */


namespace app\admin\controller;


use app\jwcode\controller\AdminBasic;

class Article extends AdminBasic
{
    /**
     * Info: 文章列表
     * Argument :
     * User: 伍先生
     * DateTime: 2019/12/12 17:49
     * Function:  index
     */
    public function index(){
        if($this->request->post()){
            $list = db('article')
                ->alias('a')
                ->join("article_kind k", "a.kid=k.id")
                ->field('a.*,k.title kind')
                ->order("a.id","desc")
                ->select();

            $count = db('article')->count();
            return $this->tableList(0,'成功',$list,$count);

        }else{
            return  $this->fetch();
        }
    }

    /**
     * Info: 文章添加
     * Argument :
     * User: 伍先生
     * DateTime: 2019/12/12 17:50
     * Function:  add
     */
    public function add(){
        if($this->request->post()){
            $postData = input('post.');

            $rule =   [
                'title|文章标题'  => 'require|length:1,100',
                'kid|分类'   => 'require|number',
                'content|文章内容'   => 'require',
                'abstract|文章简介'   => 'require',
                'seo|seo' => 'require',
                'state' => 'require',
                'push' => 'require',
                'shrinkage|缩率图' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('',-1,$validate->getError());
            }

            $inertData['kid'] = $postData['kid'];
            $inertData['title'] = $postData['title'];
            $inertData['content'] = $postData['content'];
            $inertData['abstract'] = $postData['abstract'];
            $inertData['shrinkage'] = $postData['shrinkage'];
            $inertData['seo'] = $postData['seo'];

            $inertData['state'] = $postData['state'];
            $inertData['push'] = $postData['push'];


            $inertData['addtime'] = $inertData['uptime'] = time();

            $bool = db('article')->insert($inertData);

            if($bool){
                $this->result('',1,'添加成功');
            }else{
                $this->result('',-1,'添加失败');
            }
        }
        $kindlist =  db('article_kind')->select();
        $this->assign('kindlist',$kindlist);
        return $this->fetch();
    }

    /**
     * Info: 文章修改
     * Argument :
     * User: 伍先生
     * DateTime: 2019/12/12 17:50
     * Function:  edit
     */
    public function edit(){
        if($this->request->post()){
            $postData = input('post.');

            $rule =   [
                'title|文章标题'  => 'require|length:1,100',
                'kid|分类'   => 'require|number',
                'content|文章内容'   => 'require',
                'abstract|文章简介'   => 'require',
                'seo|seo' => 'require',
                'state' => 'require',
                'push' => 'require',
                'shrinkage|缩率图' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('',-1,$validate->getError());
            }

            $updata['kid'] = $postData['kid'];
            $updata['title'] = $postData['title'];
            $updata['content'] = $postData['content'];
            $updata['abstract'] = $postData['abstract'];
            $updata['shrinkage'] = $postData['shrinkage'];
            $updata['seo'] = $postData['seo'];

            $updata['state'] = $postData['state'];
            $updata['push'] = $postData['push'];
            $updata['uptime'] = time();
            $where['id'] = $postData['id'];

            $bool = db('article')->where($where)->update($updata);

            if($bool){
                $this->result('',1,'添加成功');
            }else{
                $this->result('',-1,'添加失败');
            }
        }
        $kindlist =  db('article_kind')->select();
        $this->assign('kindlist',$kindlist);

        $where['id'] = input('id');
        $article = db('article')->where($where)->find();
        $this->assign($article);

        return $this->fetch();
    }

    /**
     * Info: 发布状态
     * Argument :
     * User: 伍先生
     * DateTime: 2019/12/24 16:49
     * Function:  state
     */
    public function state(){
        $updata['state'] = input('state');
        $where['id'] = input('id');
        $bool = db('article')->where($where)->update($updata);

        if($bool){
            $this->result('',1,'更新成功');
        }else{
            $this->result('',-1,'更新失败');
        }
    }

    /**
     * Info: 推荐
     * Argument :
     * User: 伍先生
     * DateTime: 2019/12/24 16:49
     * Function:  push
     */
    public function push(){
        $updata['push'] = input('push');
        $where['id'] = input('id');
        $bool = db('article')->where($where)->update($updata);

        if($bool){
            $this->result('',1,'更新成功');
        }else{
            $this->result('',-1,'更新失败');
        }
    }



    /**    分类操作    **/

    /**
     * Info: 分类列表
     * Argument :
     * User: 伍先生
     * DateTime: 2019/12/12 17:52
     * Function:  kind
     */
    public function kind(){
        if($this->request->post()){
            $list = db('article_kind')->select();

            $count = db('article_kind')->count();
            return $this->tableList(0,'成功',$list,$count);

        }else{
            return  $this->fetch();
        }
    }

    /**
     * Info: 添加分类
     * Argument :
     * User: 伍先生
     * DateTime: 2019/12/12 17:52
     * Function:  addkinf
     */
    public function addkind(){
        if($this->request->post()){
            $postData = input('post.');

            $rule =   [
                'title|分类标题'  => 'require|length:1,100',
                'abstract|分类说明'   => 'require',
                'state|状态'   => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('',-1,$validate->getError());
            }

            $inertData['title'] = $postData['title'];
            $inertData['abstract'] = $postData['abstract'];
            $inertData['state'] = $postData['state'];
            $inertData['addtime']  = date("Y-m-d H:i:s");

            $bool = db('article_kind')->insert($inertData);

            if($bool){
                $this->result('',1,'添加成功');
            }else{
                $this->result('',-1,'添加失败');
            }
        }
        return $this->fetch();
    }

    /**
     * Info: 修改分类
     * Argument :
     * User: 伍先生
     * DateTime: 2019/12/12 17:53
     * Function:  editkind
     */
    public function editkind(){
        if($this->request->post()){
            $postData = input('post.');

            $rule =   [
                'title|分类标题'  => 'require|length:1,100',
                'abstract|分类说明'   => 'require',
                'state|状态'   => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('',-1,$validate->getError());
            }

            $where['id'] = $postData['id'];

            $updata['title'] = $postData['title'];
            $updata['abstract'] = $postData['abstract'];
            $updata['state'] = $postData['state'];

            $bool = db('article_kind')->where($where)->update($updata);

            if($bool){
                $this->result('',1,'更新成功');
            }else{
                $this->result('',-1,'更新失败');
            }
        }
        $where['id'] = input('id');
        $data = db('article_kind')->where($where)->find();
        $this->assign($data);
        return $this->fetch();
    }

    /**
     * Info: 修改分类状态
     * Argument :
     * User: 伍先生
     * DateTime: 2019/12/24 16:48
     * Function:  kindstate
     */
    public function kindstate()
    {
        $updata['state'] = input('state');
        $where['id'] = input('id');
        $bool = db('article_kind')->where($where)->update($updata);

        if($bool){
            $this->result('',1,'更新成功');
        }else{
            $this->result('',-1,'更新失败');
        }
    }



    /***** 资源上传 *****/

    /**
     * Info: 图片上传
     * Argument :
     * User: 伍先生
     * DateTime: 2019/12/12 18:00
     * Function:  image
     */
    public function image(){
        $file = request()->file('file');
        // 移动到框架应用根目录/uploads/ 目录下
        $filesize = $file->getInfo();
        //获取系统设置的大小
        $setups = db('System')->where('title','setup')->value('json');
        $setup = $setups?json_decode($setups,1):1;
        if($setup != 1){
            $size = $setup['image'] * 1024*1024;
            $sizes = $setup['image'];
        }else{
            $size = 1 * 1024*1024;
            $sizes = 1;
        }

        if($filesize['size'] > $size){
            $this->result('',-1,"不能大于 $sizes M",'json');
        }

        $postfix = explode('.',$filesize['name']);

        if(!in_array($postfix[1],['jpg','png','gif','jpeg'])){
            $this->result('',-1,"请按着要求上传",'json');
        }

        $info = $file->move( '../public/uploads/images/',time()*rand(1000,9999)+rand(100,999));

        if($info){
            $data['name'] = $filesize['name'];
            $data['src'] = input('server.REQUEST_SCHEME') . '://' . input('server.SERVER_NAME').'/uploads/images/'.$info->getSaveName();
            $this->result($data,0,"上传成功",'json');
        }else{
            $this->result("",-1,$file->getError(),'json');
        }
    }

    /**
     * Info: 上传视频
     * Argument :
     * User: 伍先生
     * DateTime: 2019/12/12 18:01
     * Function:  video
     */
    public function video(){
        $file = request()->file('file');
        // 移动到框架应用根目录/uploads/ 目录下
        $filesize = $file->getInfo();
        //获取系统设置的大小
        $setups = db('System')->where('title','setup')->value('json');
        $setup = $setups?json_decode($setups,1):1;
        if($setup != 1){
            $size = $setup['video'] * 1024*1024;
            $sizes = $setup['video'];
        }else{
            $size = 1 * 1024*1024;
            $sizes = 1;
        }

        if($filesize['size'] > $size){
            $this->result('',-1,"不能大于 $sizes M",'json');
        }

        $postfix = explode('.',$filesize['name']);

        if(!in_array($postfix[1],['avi','mp4','mov','wmv','flv'])){
            $this->result('',-1,"请按着要求上传",'json');
        }

        $info = $file->move( '../public/uploads/video/',time()*rand(1000,9999)+rand(100,999));

        if($info){
            $data['name'] = $filesize['name'];
            $data['src'] = input('server.REQUEST_SCHEME') . '://' . input('server.SERVER_NAME').'/uploads/video/'.$info->getSaveName();
            $this->result($data,0,"上传成功",'json');
        }else{
            $this->result("",-1,$file->getError(),'json');
        }
    }

    /**
     * Info:压缩包上传
     * Argument :
     * User: 伍先生
     * DateTime: 2019/12/12 18:03
     * Function:  packet
     */
    public function packet(){
        $file = request()->file('file');
        // 移动到框架应用根目录/uploads/ 目录下
        $filesize = $file->getInfo();
        //获取系统设置的大小
        $setups = db('System')->where('title','setup')->value('json');
        $setup = $setups?json_decode($setups,1):1;
        if($setup != 1){
            $size = $setup['file'] * 1024*1024;
            $sizes = $setup['file'];
        }else{
            $size = 1 * 1024*1024;
            $sizes = 1;
        }

        if($filesize['size'] > $size){
            $this->result('',-1,"不能大于 $sizes M",'json');
        }

        $postfix = explode('.',$filesize['name']);

        if(!in_array($postfix[1],['zip','rar','JAR','ISO','7z'])){
            $this->result('',-1,"请按着要求上传",'json');
        }

        $info = $file->move( '../public/uploads/file/',time()*rand(1000,9999)+rand(100,999));

        if($info){
            $data['name'] = $filesize['name'];
            $data['src'] = input('server.REQUEST_SCHEME') . '://' . input('server.SERVER_NAME').'/uploads/file/'.$info->getSaveName();
            $this->result($data,0,"上传成功",'json');
        }else{
            $this->result("",-1,$file->getError(),'json');
        }
    }




}