<?php
/**
 * User: 伍先生
 * DateTime: 2020/9/19 14:51
 * Class:  Test
 * Info:
 */

namespace app\api\controller;



use app\common\controller\FeiFanLost;
use app\common\tool\Desensitization;
use Redis;
use think\Controller;

class Test
{
    public $redis;
    public $token = "FeiFanLost_token";

    public function __construct()
    {
        if (!$this->redis) {
            $this->redis();
        }
    }



    /**
     * info: 1.1 查询数据
     * User: 伍先生
     * Date: 2020/8/17
     * Time: 11:12
     */
    public function getData($param = [])
    {
        $url = "https://120.25.167.151:9053/api/getData";
        $token = $this->getToken();
        if ($token == false) {
            return "服务繁忙,请稍后";
        }

        $data['token'] = $token;
        $data['serviceName'] = "IdNamePhoneMapV1";
        $data['param'] = $param;

        $resp = $this->get($data, $url);
        return json_decode($resp, 1);
    }

    /**
     * info: 1.2 查询结果
     * User: 伍先生
     * Date: 2020/8/17
     * Time: 11:19
     */
    public function getStatus($guid)
    {
        $url = "https://120.25.167.151:9053/data/getStatus";

        $token = $this->getToken();
        if ($token == false) {
            return "服务繁忙,请稍后";
        }

        $data['token'] = $token;
        $data['guid'] = $guid;
        $resp = $this->get($data, $url);
        return json_decode($resp, 1);
    }

    /**
     * info: 2.0 运营商三要素实名认证 简单版本
     * User: 伍先生
     * Date: 2020/8/17
     * Time: 11:59
     */
    public function idNamePhoneCheck($param = [])
    {
        $url = "https://120.25.167.151:9032/api/IdNamePhoneCheck";
        $data['loginName'] = "feifan";
        $data['pwd'] = "Ff202083.";
        $data['serviceName'] = "IdNamePhoneCheck";
        $data['param'] = $param;

        // echo  json_encode($data,256);die();
//        echo "<br>";

        $resp = $this->get($data, $url);
        return json_decode($resp, 1);
    }

    /**
     * info: 3.0  运营商在网状态
     * User: 伍先生
     * Date: 2020/8/17
     * Time: 14:14
     */
    public function mobileStatus($param){
        $url = "https://120.25.167.151:9032/api/MobileStatus";
        $data['loginName'] = "feifan";
        $data['pwd'] = "Ff202083.";
        $data['serviceName'] = "MobileStatus";
        $data['param'] = $param;

        // echo  json_encode($data,256);die();
//        echo "<br>";

        $resp = $this->get($data, $url);
        return json_decode($resp, 1);
    }

    /**
     * info: 4.0  运营商三要素实名认证含详情
     * User: 伍先生
     * Date: 2020/8/17
     * Time: 14:19
     */
    public function IdNamePhoneDetailCheck($param){
        $url = "https://120.25.167.151:9032/api/IdNamePhoneDetailCheck";
        $data['loginName'] = "feifan";
        $data['pwd'] = "Ff202083.";
        $data['serviceName'] = "IdNamePhoneDetailCheck";
        $data['param'] = $param;

        // echo  json_encode($data,256);die();
//        echo "<br>";

        $resp = $this->get($data, $url);
        return json_decode($resp, 1);
    }

    /**
     * info: 1.0 登录获取投入看
     * User: 伍先生
     * Date: 2020/8/17
     * Time: 10:37
     */
    public function login()
    {
        $url = "https://120.25.167.151:9053/login";
        $data['loginName'] = "feifan";
        $data['pwd'] = "Ff202083.";

        $resp = $this->get($data, $url);

        $respArr = json_decode($resp, 1);

        if ($respArr['RESULT'] == 0) {
            $this->buffer($this->token, $respArr['data']['token'], 3500);
            return true;
        }
        return false;
    }

    /**
     * info: 缓存拿token
     * User: 伍先生
     * Date: 2020/8/17
     * Time: 10:37
     */
    public function getToken()
    {
        if ($this->buffer($this->token)) {
            return $this->buffer($this->token);
        } else {
            $bool = $this->login();
            if ($bool) {
                $this->buffer($this->token);
            } else {
                return false;
            }
        }
    }


    private function get($data, $url)
    {

        if (!is_array($data)) {
            $data = json_decode($data, 1);
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    private function buffer($key, $val = null, $time = 86300)
    {
        if ($val != null) {
            $this->redis->set($key, $val, $time);
        } else {
            return $this->redis->get($key);
        }
    }

    private function redis()
    {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    public function aa(){
        $param = [
            "idCard" => '130821198711193787',
            "name" => '侯立辉',
            "mobile" => "15851569929",
        ];

        $aaa = new FeiFanLost();
        $data = $aaa->idNamePhoneCheck($param);
        echo json_encode($data, 256);
    }

    public function bb(){
        $aa = new Desensitization();

        $data['aa'] = 123;
        $data['bb'] = 123;
        $data['cc'] = 123;
        $data['name'] = 123;

        $aa->selectName($data);
    }
}