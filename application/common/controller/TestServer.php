<?php
/**
 * User: 伍先生
 * DateTime: 2020/10/13 21:41
 * Class:  TestServer
 * Info:
 */

namespace app\common\controller;



use think\Controller;

class TestServer extends Controller
{
    protected $uid = "用户ID";
    protected $userserveid = "用户服务ID";
    protected $serveid = "服务ID";
    protected $AdminInfo = []; //账号信息
    protected $randomSeed = '随机种子'; //随机种子
    protected $RequTime = '获取当前请求毫秒时间'; //获取当前请求毫秒时间
    protected $RequActon = '获取当前请求api方法'; //获取当前请求api方法
    protected $RequIP = '获取当前请求IP'; //获取当前请求IP
    protected $servePay = [];



    public function setting($func,$data)
    {
        // 初始化
        $this->RequTime = get_msectime(); // 请求时间
        $this->randomSeed = get_random_seed(); //随机种子
        $this->RequActon = $func; //请求api方法
        $this->RequIP = get_client_ip();

        $this->AdminInfo = $data;
        $this->uid = $data['id'];


        //判断这个人是否有使用权限
        $this->isAction();


    }

    //非凡大数据修复_查询数据
    public function IdNamePhoneMapV1($postData)
    {
        $param = [["no" => $postData['no'], "idCard" => $postData['idCard'], "name" => $postData['name']]];
        $this->Requ(['param'=>$param]);
        $FeiFanLost = new FeiFanLost();
        $dataData = $FeiFanLost->getData($param);
        if (isset($dataData['RESULT']) && $dataData['RESULT'] == 1 && isset($dataData['guid'])) {
            sleep(1);
            $dataStatus = $FeiFanLost->getStatus($dataData['guid']);
            $dataStatus['guid'] = $dataData['guid'];
            $time = 30*24*3600;
            cache($dataData['guid'],$dataStatus,$time);
            if (isset($dataStatus['data'][0]['Tm_myMobile'][0]['phone'])) {
                return $this->ReturnInfo(1000, ["guid"=>$dataData['guid'],"mobile"=>$dataStatus['data'][0]['Tm_myMobile'][0]['num']]);
            } else {
                return $this->ReturnInfo(1003, ["guid"=>$dataData['guid']]);
            }
        } else {
            return $this->ReturnInfo(1003, $dataData ? $dataData : "");
        }
    }

    //非凡大数据修复_查询结果
    public function SpecialBigDataGetStatus($param)
    {
        $FeiFanLost = new FeiFanLost();
        $this->Requ($param);
        return $FeiFanLost->getStatus($param['guid']);
    }

    //运营商三要素实名认证简版
    public function IdNamePhoneCheck($postData)
    {
        $param = [
            "idCard" => $postData['idCard'],
            "name" => $postData['name'],
            "mobile" => $postData['mobile'],
        ];
        $this->Requ($param);
        $feifan = new FeiFanLost();
        $dataData = $feifan->idNamePhoneCheck($param);
        if (isset($dataData['RESULT']) && $dataData['RESULT'] > 0) {
            return $this->ReturnInfo(1000, $dataData);
        } else {
            return $this->ReturnInfo(1003, $dataData ? $dataData : "");
        }
    }

    //运营商三要素实名认证详版
    public function IdNamePhoneDetailCheck($postData)
    {
        $param = [
            "idCard" => $postData['idCard'],
            "name" => $postData['name'],
            "mobile" => $postData['mobile'],
        ];
        $this->Requ($param);
        $feifan = new FeiFanLost();
        $dataData = $feifan->IdNamePhoneDetailCheck($param);
        if (isset($dataData['RESULT']) && $dataData['RESULT'] > 0) {
            return $this->ReturnInfo(1000, $dataData);
        } else {
            return $this->ReturnInfo(1003, $dataData ? $dataData : "");
        }
    }

    //运营商在网状态
    public function MobileStatus($postData)
    {
        $param = [
            "idCard" => $postData['idCard'],
            "name" => $postData['name'],
            "mobile" => $postData['mobile'],
        ];
        $this->Requ($param);
        $feifan = new FeiFanLost();
        $dataData = $feifan->mobileStatus($param);
        if (isset($dataData['RESULT']) && $dataData['RESULT'] > 0) {
            return $this->ReturnInfo(1000, $dataData);
        } else {
            return $this->ReturnInfo(1003, $dataData ? $dataData : "");
        }
    }


    // 内部操作 //

    /**
     * Info: 校验服务
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/17 1:16
     * Function:  isAction
     */
    protected function isAction()
    {
        //系统服务ID
        $serve = db("serve")->where("serve_code", $this->RequActon)->cache(60)->find();
        if (!isset($serve['serve_statu']) || $serve['serve_statu'] != 1) {
            $this->result('', -1, "请先联系管理员配置该服务", 'json');
        }
        $this->serveid = $serve['id'];

        //用户服务ID
        $capacity = db('capacity')->where(['admin_id' => $this->uid, "serve_id" => $this->serveid])->cache(60)->find();
        $this->userserveid = $capacity['id'];
        // 按使用收费
        if ($capacity['capacity_pay'] == 1) {

            if ($this->AdminInfo['balance'] > $capacity['capacity_price']) {
                $this->servePay = [
                    "type" => "1",
                    "price" => $capacity['capacity_price'],
                ];
            } else {
                $this->result('', -1, "请先充值", 'json');
            }

        } else if ($capacity['capacity_pay'] == 2) {
            if ($capacity['capacity_num'] > 0) {
                $this->servePay = [
                    "type" => "2",
                    "price" => "",
                ];
            } else {
                $this->result('', -1, "请先购买次数", 'json');
            }
        } else {
            $this->result('', -1, "请联系管理员配置", 'json');
        }

    }

    /**
     * Info: 服务次数减少
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/17 2:00
     * Function:  servenum
     */
    protected function servenum()
    {
        db('capacity')->where(['admin_id' => $this->uid, "serve_id" => $this->serveid])->setDec("capacity_num", 1);
    }

    /**
     * Info: 服务现金支付
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/17 2:00
     * Function:  servepay
     */
    protected function servepay()
    {
        db('admin')->where('id', $this->uid)->setDec("balance", $this->servePay['price']);
    }

    /**
     * Info: 请求记录
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/16 17:23
     * Url: /home/CLASS_NAME/Requ
     */
    protected function Requ($Parma)
    {
        $data['log_seed'] = $this->randomSeed;
        $data['log_admin_id'] = $this->uid;
        $data['log_serve_id'] = $this->serveid;
        $data['log_user_serve_id'] = $this->userserveid;
        $data['log_adddata'] = date("Y-m-d");
        $data['log_addtime'] = date("Y-m-d H:i:s");
        $data['log_service_name'] = $this->RequActon;
        $data['log_qeru'] = json_encode($Parma, 256);
        $data['log_type'] = $this->servePay['type'];
        $data['log_price'] = $this->servePay['price'];
        $data['log_ip'] = $this->RequIP;
        db("logs")->insert($data);
    }

    /**
     * Info: 返回记录
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/16 17:23
     * Url: /home/CLASS_NAME/Resp
     */
    protected function Resp($code, $data)
    {
        $where['log_seed'] = $this->randomSeed;

        $updata['log_code'] = $code;
        $updata['log_retime'] = date("Y-m-d H:i:s");
        $updata['log_consuming'] = get_msectime() - $this->RequTime;
        $updata['log_resp'] = is_array($data) ? json_encode($data, 256) : $data;

        $updata['log_guid'] = isset($data['guid'])?$data['guid']:'';
        db("logs")->where($where)->update($updata);
        if ($code == 1000 && $this->servePay['type'] == 1) {
            $this->servepay();
        }
        if ($code == 1000 && $this->servePay['type'] == 2) {
            $this->servenum();
        }

    }

    /**
     * Info: 返回信息
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/15 22:15
     * Function:  info
     */
    public function ReturnInfo($code, $data = [])
    {
        if ($this->RequActon != "getToken" &&  $this->RequActon != "SpecialBigDataGetStatus") {
            $this->Resp($code, $data);
        }

        return ['code'=>$code,'data'=>$data];
    }
}