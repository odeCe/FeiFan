<?php
/**
 * User: 伍先生
 * DateTime: 2020/9/12 23:40
 * Class:  ApiServe
 * Info:
 */

namespace app\jwcode\controller;


use think\Controller;

class ApiServe extends Controller
{
    protected $uid = "用户ID";
    protected $userserveid = "用户服务ID";
    protected $serveid = "服务ID";
    protected $AdminInfo = []; //账号信息
    protected $randomSeed = '随机种子'; //随机种子
    protected $RequTime = '获取当前请求毫秒时间'; //获取当前请求毫秒时间
    protected $RequActon = '获取当前请求api方法'; //获取当前请求api方法
    protected $RequIP = '获取当前请求IP'; //获取当前请求IP
    protected $White = "feifanipwhite";
    protected $servePay = [];

    private $state = [
        1000 => "成功",
        1001 => "参数错误",
        1002 => "请求错误",
        1003 => "请求失败",
        1004 => "服务异常",
        1005 => "账号异常",
        1006 => "token过期或不存在",
        1007 => "每天token获取次数为50",
        1008 => "访问频繁,稍后再试",
        2001 => "服务未启动或没有该服务",
        2002 => "请联系管理员添加白名单再访问",
        2003 => "请联系管理员配置该服务",
        2004 => "账户余额不足",
        2005 => "服务次数不足",
    ];

    protected function initialize()
    {
        // 初始化
        $this->RequTime = get_msectime(); // 请求时间
        $this->randomSeed = get_random_seed(); //随机种子
        $this->RequActon = $this->request->action(true); //请求api方法
        $this->RequIP = get_client_ip();

        //访问限制
//        $ip_Acton_key = $this->RequActon . $this->RequIP;
//        $keynum = cache($ip_Acton_key);
//        $keynum++;
//        if ($keynum > 10) {
//            $this->ReturnInfo(1008);
//        }
//        cache($ip_Acton_key, $keynum, 1);

        // 如果不是获取token 就需要鉴权
        if ($this->RequActon != "getToken" && $this->RequActon != "SpecialBigDataGetStatus") {
            $token = input("post.Token", false);
            if ($token == false) {
                $this->ReturnInfo(1001);
            }

            if (!cache($token)) {
                $this->ReturnInfo(1006);
            }
            $this->AdminInfo = cache($token);
            $this->uid = $this->AdminInfo['id'];

            //判断是否IP白名单
//            $this->WhiteIpList();

            //判断请求服务正确???
            $action = input("post.serviceName", false);

            if ($action != $this->RequActon) {
                $this->ReturnInfo(1001);
            }
            //判断有没有这个api 是否生效果
            //判断这个人是否有使用权限
            $this->isAction();

            $this->Requ();

        }

    }

    /**
     * Info: IP白名单校验 OK
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/17 1:13
     * Function:  WhiteIpList
     */
    protected function WhiteIpList()
    {
        $ip = $this->RequIP;


        $key = date("ymd") . $this->White;

        if (cache($key) && !in_array($ip, cache($key))) {
            $this->ReturnInfo(2002);
        }

        $ipArr = [];
        $capacity = db("capacity")->field('id,capacity_ip')->select();
        foreach ($capacity as $key => $val) {
            $ipArr = array_merge_recursive($ipArr, explode(",", $val['capacity_ip']));
        }
        cache($key, $ipArr, 3600);
        if (!in_array($ip, $ipArr)) {
            $this->ReturnInfo(2002);
        }
    }

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
            $this->ReturnInfo(2001);
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
                $this->ReturnInfo(2004);
            }

        } else {
            if ($capacity['capacity_pay'] == 2) {
                if ($capacity['capacity_num'] > 0) {
                    $this->servePay = [
                        "type" => "2",
                        "price" => "",
                    ];
                } else {
                    $this->ReturnInfo(2005);
                }
            } else {
                $this->ReturnInfo(2003);
            }
        }

    }

    /**
     * Info: 请求记录
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/16 17:23
     * Url: /home/CLASS_NAME/Requ
     */
    protected function Requ()
    {
        $data['log_seed'] = $this->randomSeed;
        $data['log_admin_id'] = $this->uid;
        $data['log_serve_id'] = $this->serveid;
        $data['log_user_serve_id'] = $this->userserveid;
        $data['log_adddata'] = date("Y-m-d");
        $data['log_addtime'] = date("Y-m-d H:i:s");
        $data['log_service_name'] = $this->RequActon;
        $data['log_qeru'] = json_encode(input(), 256);
        $data['log_type'] = $this->servePay['type'];
        $data['log_price'] = $this->servePay['price'];
        $data['log_ip'] = $this->RequIP;
        db("logs")->insert($data);
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

        $this->result($data, $code, $this->state[$code], 'json');
    }

}