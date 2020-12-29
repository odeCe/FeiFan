<?php
/**
 * User: 伍先生
 * DateTime: 2020/8/30 14:56
 * Class:  ApiTest
 * Info: API测试管理
 */

namespace app\admin\controller;


use app\common\controller\TestServer;
use app\common\tool\Desensitization;
use app\jwcode\controller\AdminBasic;
use think\facade\Env;

class Apitest extends AdminBasic
{
    /**
     * info: 测试分类列表
     * User: 伍先生
     * Date: 2020/9/29
     * Time: 15:27
     */
    public function kindtest()
    {
        if ($this->request->post()) {
            $postData = input('post.');
            $limit = input('limit', 10);
            $page = (input('page', 1) - 1) * $limit;
            $order = ['kind_id' => 'desc'];


            $where = [];

            if (isset($postData['kind_title']) && !empty($postData['kind_title'])) {
                $where['kind_title'] = $postData['kind_title'];
            }

            $table = db('test_kind')
                ->where($where);

            $userlist = $table
                ->limit($page, $limit)
                ->order($order)
                ->select();

            $count = $table->count();
            return $this->tableList(0, '成功', $userlist, $count);
        }
        return $this->fetch();
    }

    /**
     * info: 测试分类添加
     * User: 伍先生
     * Date: 2020/9/29
     * Time: 15:25
     */
    public function kindtestadd()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'kind_title|分类名称' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), 'json');
            }

            $inertData['kind_title'] = $postData['kind_title'];

            $inertData['kind_addtime'] = date("Y-m-d H:i:s");

            $bool = db('test_kind')->insert($inertData);

            if ($bool) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }
        }
        return $this->fetch();
    }

    /**
     * info: 测试分类修改
     * User: 伍先生
     * Date: 2020/9/29
     * Time: 15:27
     */
    public function kindtestedit()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'kind_id|分类ID' => 'require',
                'kind_title|分类名称' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), 'json');
            }

            $updateData['kind_title'] = $postData['kind_title'];

            $bool = db('test_kind')->where('kind_id', $postData['kind_id'])->update($updateData);

            if ($bool) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }
        }

        $where['kind_id'] = input('id');
        $request = db('test_kind')->where($where)->find();
        $this->assign($request);

        return $this->fetch();
    }


    /**
     * info: 测试api列表
     * User: 伍先生
     * Date: 2020/9/29
     * Time: 15:28
     */
    public function testapilist()
    {
        $order = ['test_id' => 'desc'];
        $where = [];
        $where['test_kind_id'] = input('id');
        $p = input('page', 1);


        $table = db('test_http')
            ->where($where)
            ->alias('h')
            ->Join('test_kind k', 'k.kind_id = h.test_kind_id')
            ->Join('serve s', 's.id = h.test_serve_id');

        $userlist = $table
            ->order($order)
            ->page($p)
            ->paginate(8, false, ['query' => request()->param()]);


        $page = $userlist->render();
        $this->assign('page', $page);

        $this->assign('userlist', $userlist);

        $kind = db('test_kind')->select();
        $this->assign('kind', $kind);

        $kindname = db('test_kind')->where("kind_id", input('id'))->value("kind_title");
        $this->assign('kindname', $kindname);

        return $this->fetch();
    }

    /**
     * info: 测试api添加
     * User: 伍先生
     * Date: 2020/9/29
     * Time: 15:28
     */
    public function testapiadd()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'test_serve_id|APi服务' => 'require',
                'test_kind_id|APi测试分类' => 'require',
                'test_img|APi图标' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), 'json');
            }

            $inertData['test_serve_id'] = $postData['test_serve_id'];
            $inertData['test_kind_id'] = $postData['test_kind_id'];
            $inertData['test_img'] = $postData['test_img'];

            $inertData['test_http_addtime'] = date("Y-m-d H:i:s");

            $bool = db('test_http')->insert($inertData);

            if ($bool) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }
        }
        $serve = db('serve')->select();
        $this->assign("serve", $serve);

        $kind = db('test_kind')->select();
        $this->assign('kind', $kind);

        return $this->fetch();
    }

    /**
     * info: 测试api修改
     * User: 伍先生
     * Date: 2020/9/29
     * Time: 15:29
     */
    public function testapiedit()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'test_id|更新ID' => 'require',
                'test_serve_id|APi服务' => 'require',
                'test_kind_id|APi测试分类' => 'require',
                'test_img|APi图标' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), 'json');
            }

            $updataData['test_serve_id'] = $postData['test_serve_id'];
            $updataData['test_kind_id'] = $postData['test_kind_id'];
            $updataData['test_img'] = $postData['test_img'];

            $bool = db('test_http')->where('test_id', $postData['test_id'])->update($updataData);

            if ($bool) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }
        }

        $where['test_id'] = input('id');
        $request = db('test_http')->where($where)->find();
        $this->assign($request);


        $serve = db('serve')->select();
        $this->assign("serve", $serve);

        $kind = db('test_kind')->select();
        $this->assign('kind', $kind);

        return $this->fetch();
    }


    /**
     * Info: 添加单条测试
     * Argument :
     * User: 伍先生
     * DateTime: 2020/10/12 21:38
     * Function:  testoneadd
     */
    public function testoneadd()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'data_serve_id|服务ID' => 'require',
                'data_test_id|测试ID' => 'require',
            ];

            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), 'json');
            }

            $insertData['data_type'] = 1;
            $insertData['data_status'] = 3;

            $insertData['data_serve_id'] = $postData['data_serve_id'];
            $insertData['data_test_id'] = $postData['data_test_id'];

            $data_serve_id = $postData['data_serve_id'];
            unset($postData['data_serve_id']);
            unset($postData['data_test_id']);

            $insertData['data_requ'] = json_encode($postData, 256);

            $insertData['data_addtime'] = date("Y-m-d H:i:s");
            $insertData['data_admin_id'] = $this->uid;

            $id = db('test_data')->insertGetId($insertData);

            //发起测试
            $url = db("serve")->where('id', $data_serve_id)->value('serve_code');
            $data = $this->TestHttp($url, $postData);


            if ($data['code'] == 1000) {
                if (is_array($data)) {
                    $dataJosn = json_encode($data['data'], 256);
                }
                db('test_data')->where('data_id', $id)->update(['data_resp' => $dataJosn, "data_status" => 1]);
            } else {
                if (is_array($data)) {
                    $dataJosn = json_encode($data['data'], 256);
                }
                db('test_data')->where('data_id', $id)->update(['data_resp' => $dataJosn, "data_status" => 2]);
            }


            if ($data['code'] == 1000) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }
        }

        $where['test_id'] = input('id');
        $request = db('test_http')
            ->alias('h')
            ->Join('test_kind k', 'k.kind_id = h.test_kind_id')
            ->Join('serve s', 's.id = h.test_serve_id')
            ->where($where)->find();
        $this->assign($request);


        $param = db('param')->where('serve_id', $request['id'])->select();
        $this->assign('param', $param);
        return $this->fetch();
    }

    /**
     * info: 单条记录
     * User: 伍先生
     * Date: 2020/9/30
     * Time: 15:06
     */
    public function testone()
    {
        if ($this->request->post()) {

            $test_id = input('get.id');

            $postData = input('post.');
            $limit = input('limit', 10);
            $page = (input('page', 1) - 1) * $limit;
            $order = ['data_id' => 'desc'];

            $where = [];

            $where['data_test_id'] = $test_id;
            $where['data_type'] = 1;
            //判断管理员还是客户
            if ($this->super == 2) {
                $where['data_admin_id'] = $this->uid;
            }
            if (isset($postData['data_task_id']) && !empty($postData['data_task_id'])) {
                $where['data_task_id'] = $postData['data_task_id'];
                $where['data_type'] = 2;
            }

            $table = db('test_data')
                ->where($where);

            if (isset($postData['search']) && !empty($postData['search'])) {
                $table = $table->where("data_requ", 'like', '%' . $postData['search'] . '%');
            }

            $userlist = $table
                ->limit($page, $limit)
                ->order($order)
                ->select();

            foreach ($userlist as $key => $val) {
                $val['data_resp'] = $this->expor($val);
                $val['data_requ'] = (new Desensitization)->selectName(json_decode($val['data_requ'], 1));
                $userlist[$key] = array_merge($val, $val['data_requ']);
            }

            $count = $table->count();
            return $this->tableList(0, '成功', $userlist, $count);
        }
        $test_id = input('get.id');
        $this->assign('dataid', $test_id);
        $where['test_id'] = $test_id;

        $param = db("test_http")
            ->alias('h')
            ->join('param p', 'p.serve_id = h.test_serve_id')
            ->where($where)
            ->select();
        $js = "";
        if ($param) {
            foreach ($param as $key => $val) {
                $js = $js . ", {field: '{$val['param_code']}',  title: '{$val['param_name']}'}";
            }
        }
        $this->assign('js', $js);
        return $this->fetch();
    }

    /**
     * Info: 添加多条任务
     * Argument :
     * User: 伍先生
     * DateTime: 2020/10/12 21:38
     * Function:  testtwoadd
     */
    public function testtwoadd()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'test_id|测试ID' => 'require',
                'task_name|测试任务名' => 'require',
                'task_file|测试文件' => 'require',
                'task_serve_id|服务ID' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), 'json');
            }

            $insertData['task_name'] = $postData['task_name'];
            $insertData['task_test_id'] = $postData['test_id'];
            $insertData['task_serve_id'] = $postData['task_serve_id'];
            $insertData['task_file'] = $postData['task_file'];
            $insertData['task_file_cache'] = $postData['task_file'];
            $insertData['task_admin_id'] = $this->uid;
            $insertData['task_statu'] = 1;

            $insertData['task_addtime'] = date("Y-m-d H:i:s");

            $bool = db('test_task')->insert($insertData);

            $this->fopenfile($postData['task_file']);

            if ($bool) {
                cache("run_test_key", 1, 83600);
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }
        }

        $where['test_id'] = input('id');
        $request = db('test_http')
            ->alias('h')
            ->Join('test_kind k', 'k.kind_id = h.test_kind_id')
            ->Join('serve s', 's.id = h.test_serve_id')
            ->where($where)->find();
        $this->assign($request);


        $param = db('param')->where('serve_id', $request['id'])->select();
        $this->assign('param', $param);
        return $this->fetch();
    }

    /**
     * info:所有记录
     * User: 伍先生
     * Date: 2020/9/30
     * Time: 15:06
     */
    public function testall()
    {
        if ($this->request->post()) {
            $postData = input('post.');
            $limit = input('limit', 10);
            $page = (input('page', 1) - 1) * $limit;
            $order = ['task_id' => 'desc'];

            $where = [];
            if ($this->super == 2) {
                $where['task_admin_id'] = $this->uid;
            }
            $where['test_id'] = input('get.id');

            if (isset($postData['task_name']) && !empty($postData['task_name'])) {
                $where['task_name'] = $postData['task_name'];
            }
//
//            if (isset($postData['test_serve_id']) && !empty($postData['test_serve_id'])) {
//                $where['test_serve_id'] = $postData['test_serve_id'];
//            }
//
//            if (isset($postData['test_kind_id']) && !empty($postData['test_kind_id'])) {
//                $where['test_kind_id'] = $postData['test_kind_id'];
//            }

            $table = db('test_task')
                ->alias('t')
                ->join('test_http h', 'h.test_id = t.task_test_id')
                ->Join('test_kind k', 'k.kind_id = h.test_kind_id')
                ->Join('serve s', 's.id = t.task_serve_id')
                ->where($where);

            $userlist = $table
                ->limit($page, $limit)
                ->order($order)
                ->select();

            $count = $table->count();
            return $this->tableList(0, '成功', $userlist, $count);
        }

        $test_id = input('get.id');
        $this->assign('tid', $test_id);

        return $this->fetch();
    }


    /**
     * Info: 批量记录内容
     * Argument :
     * User: 伍先生
     * DateTime: 2020/10/12 21:35
     * Function:  testallinfo
     */
    public function testallinfo()
    {
        if ($this->request->post()) {

            $test_id = input('get.id');
            $task_id = input('get.tkid');

            $postData = input('post.');
            $limit = input('limit', 10);
            $page = (input('page', 1) - 1) * $limit;
            $order = ['data_id' => 'desc'];

            $where = [];

            $where['data_test_id'] = $test_id;
            $where['data_task_id'] = $task_id;
            $where['data_type'] = 2;
            if ($this->super == 2) {
                $where['data_admin_id'] = $this->uid;
            }
            $table = db('test_data')
                ->where($where);

            if (isset($postData['search']) && !empty($postData['search'])) {
                $table = $table->where("data_requ", 'like', '%' . $postData['search'] . '%');
            }

            $userlist = $table
                ->limit($page, $limit)
                ->order($order)
                ->select();

            foreach ($userlist as $key => $val) {
                $val['data_resp'] =  $this->expor($val);
                $val['data_requ'] = (new Desensitization)->selectName(json_decode($val['data_requ'], 1));
                $userlist[$key] = array_merge($val, $val['data_requ']);
            }

            $count = $table->count();
            return $this->tableList(0, '成功', $userlist, $count);
        }
        $test_id = input('get.id');
        $this->assign('dataid', $test_id);

        $tkid = input('get.tkid');
        $this->assign('tkid', $tkid);

        $where['test_id'] = $test_id;

        $param = db("test_http")
            ->alias('h')
            ->join('param p', 'p.serve_id = h.test_serve_id')
            ->where($where)
            ->select();


        $js = "";
        if ($param) {
            foreach ($param as $key => $val) {
                $js = $js . ", {field: '{$val['param_code']}',  title: '{$val['param_name']}'}";
            }
        }
        $this->assign('js', $js);
        return $this->fetch();
    }


    /**
     * info: 表格上传
     * User: 伍先生
     * Date: 2020/10/13
     * Time: 9:42
     */
    public function filesave()
    {
        $file = request()->file('file');
        // 移动到框架应用根目录/uploads/ 目录下
        $filesize = $file->getInfo();

        $size = 5 * 1024 * 1024;
        $sizes = 5;

        if ($filesize['size'] > $size) {
            $this->result('', -1, "不能大于 $sizes M", 'json');
        }

        $postfix = explode('.', $filesize['name']);

        if (!in_array($postfix[1], ['csv'])) {
            $this->result('', -1, "请按着要求上传", 'json');
        }

        $info = $file->move(Env::get('root_path') . 'public/uploads/testapifile/',
            time() * rand(1000, 9999) + rand(100, 999));

        if ($info) {
            $data['name'] = $filesize['name'];
            $data['src'] = Env::get('root_path') . 'public/uploads/testapifile/' . $info->getSaveName();
            $this->result($data, 1, "上传成功", 'json');
        } else {
            $this->result("", -1, $file->getError(), 'json');
        }
    }

    public function getfiledownload()
    {
        $param = db('param')->where('serve_id', input('id'))->select();
        $servename = db("serve")->where('id', input('id'))->value('serve_name');
        var_dump(array_column($param, "param_name"));
        $title = "$servename-批量测试模板";
        $head = array_column($param, "param_name");
        csvsend([], $head, $title);
    }

    public function TestHttp($serverName, $param)
    {
        $TestServer = new TestServer();
        $TestServer->setting($serverName, $this->data);
        return $TestServer->$serverName($param);

    }

    public function fopenfile($file)
    {
        $handle = fopen($file, 'r');
        $dataCsv = inputCsv($handle);
        cache($file, $dataCsv, 83600);
    }


    public function expor($data)
    {
        if (is_array($data)) {
            switch ($data['data_serve_id']) {
                case 5:
                    if ($data['data_status'] == 1) {
                        $arr = json_decode($data['data_resp'], 1);
                        return isset($arr['mobile']) ? $arr['mobile'] : "";
                    } else {
                        return "无结果";
                    }
                case 4:
                    if ($data['data_status'] == 1) {
                        $arr = json_decode($data['data_resp'], 1);

                        $msg = (isset($arr['MESSAGE']) ? $arr['MESSAGE'] . "/" : "") . (isset($arr['isp']) ? $arr['isp'] : "");
                        return $msg;
                    } else {
                        return "无结果";
                    }
                case 3:
                    if ($data['data_status'] == 1) {
                        $arr = json_decode($data['data_resp'], 1);
                        $msg = (isset($arr['detail']['resultMsg']) ? $arr['detail']['resultMsg'] . "/" : "") . (isset($arr['detail']['resultInfo']['isp']) ? $arr['detail']['resultInfo']['isp'] : "");
                        return $msg;
                    } else {
                        return "无结果";
                    }
                case 2:
                    if ($data['data_status'] == 1) {
                        $arr = json_decode($data['data_resp'], 1);

                        $msg = (isset($arr['MESSAGE']) ? $arr['MESSAGE'] . "/" : "") . (isset($arr['isp']) ? $arr['isp'] : "");
                        return $msg;
                    } else {
                        return "无结果";
                    }
                default:
                    return "";
            }
        }
        return $data;
    }
/*
    public function Desensitization($arr)
    {
        foreach ($arr as $key=>$val){
            if ($key === "idCard" || $key === "mobile"){

                $arr[$key] = $this->IdCard($arr[$key]);
            }
            if ($key === "name"){
                $arr[$key] = $this->NameCard($arr[$key]);
            }
            if (is_array($val)){
                $arr[$key] = $this->Desensitization($val);
            }
        }
        return $arr;
    }

    function IdCard($id){
        $str = substr($id,0,3);
        $str = $str."***".substr($id,-4);
        return $str;
    }
    function NameCard($name){
        $name = trim($name);
        $str = mb_substr($name, 0, 1, 'utf8');
        return $str."**";
    }
*/
}