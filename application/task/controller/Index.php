<?php


namespace app\task\controller;


use app\common\controller\TestServer;
use think\Controller;

class Index extends Controller
{


    public function runs()
    {
        cache("run_test_key",1);
        if (cache("run_test_key") == 1) {
            $request = db("test_task")->where('task_statu', 1)->select();
            if ($request) {
                echo  "任务处理开始\n";
                cache("run_test_key", 2);
                foreach ($request as $key => $val) {
                    $this->requestFile($val);
                }
                cache("run_test_key", 2);
                echo  "任务处理完成\n";
            }else{
                echo  "暂无数据\n";
            }
        }else{
            echo  "暂无任务\n";
        }
    }

    /**
     * Info: 解析文件
     * Argument :
     * User: 伍先生
     * DateTime: 2020/10/13 21:24
     * Function:  requestFile
     */
    public function requestFile($dataArr)
    {
        db('test_task')->where('task_id', $dataArr['task_id'])->update(['task_statu' => 2]);
        //读文件
        $dataCsv = $this->fopenfile($dataArr);
        //设置参数
        $param = db('param')->where('serve_id', $dataArr['task_serve_id'])->select();
        $serverName = db('serve')->where('id', $dataArr['task_serve_id'])->value('serve_code');

        $number = 0; //条数
        $success = 0; //成功
        foreach ($dataCsv as $key => $val) {
            if ($key == 0) {
                continue;
            }
            if(empty($val)){
                continue;
            }
            $number++;
            foreach ($param as $kk => $vv) {
                $insertData[$vv['param_code']] = iconv('gb2312', 'utf-8', $val[$kk]);
            }

            $dataInfo = db('admin')
                ->where('id',$dataArr['task_admin_id'])
                ->cache(120)->find();
            $retu = $this->TestHttp($serverName, $insertData,$dataInfo);
            $testinsertData['data_status'] = 3;
            if ($retu['code'] == 1000) {
                $success++;
                $testinsertData['data_status'] = 1;
            } else {
                $testinsertData['data_status'] = 2;
            }

//            添加单条测试记录
            $testinsertData['data_type'] = 2;
            $testinsertData['data_serve_id'] = $dataArr['task_serve_id'];
            $testinsertData['data_task_id'] = $dataArr['task_id'];
            $testinsertData['data_test_id'] = $dataArr['task_test_id'];

            if (is_array($retu)) {
                $retuJson = json_encode($retu['data'], 256);
            }

            $testinsertData['data_requ'] = json_encode($insertData, 256);
            $testinsertData['data_resp'] = $retuJson;
            $testinsertData['data_admin_id'] = $dataArr['task_admin_id'];
            $testinsertData['data_addtime'] = date("Y-m-d H:i:s");

            db('test_data')->insert($testinsertData);

        }

        $update['task_num'] = $number;
        $update['task_ok'] = $number;
        $update['task_success'] = $success;
        $update['task_statu'] = 3;
        db('test_task')->where('task_id', $dataArr['task_id'])->update($update);


    }

    public function fopenfile($dataArr)
    {
        $file_name = $dataArr['task_file'];
        return cache($file_name);
    }

    public function TestHttp($serverName, $param,$data)
    {
        $TestServer = new TestServer();
        $TestServer->setting($serverName,$data);
        return $TestServer->$serverName($param);


    }


//    public function test()
//    {
//        $json = '{"loginName":"feifan","pwd":"Ff202083.","serviceName":"IdNamePhoneCheck","param":{"idCard":"130821198711193787","nameCard":"\u4faf\u7acb\u8f89","mobile":"15851569929"}}';
//        $FeiFanLost = new FeiFanLost();
//        $data = $FeiFanLost->get($json, "https://120.25.167.151:9032/api/IdNamePhoneCheck");
//        var_dump(
//            $data
//        );
//    }

}