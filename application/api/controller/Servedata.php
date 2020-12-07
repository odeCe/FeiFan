<?php


namespace app\api\controller;


use app\common\controller\FeifanDb;
use app\common\controller\FeiFanLost;
use app\jwcode\controller\ApiServe;
use think\exception\ErrorException;

class Servedata extends ApiServe
{
    /**
     * Info: 非凡大数据修复_查询数据
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/16 18:43
     * Url: /api/ServeData/IdNamePhoneMapV1
     * {"no":23,"Token":"8208988196DCB7A6406BDA487BDDF24D","idCard":"440105197011054516","nameCard":"李升"}
     * {"code":1000,"msg":"成功","time":1600273345,"data":{"No":"23","Tm_myMobile":[],"Flag":{"myMobile":"0"}}}
     */
    public function IdNamePhoneMapV1()
    {
        try {
            if ($this->request->isPost()) {

                $postData = input('post.');
                $rule = [
                    'param|批量参数' => 'array'
                ];
                $validate = new \think\Validate;
                $validate->rule($rule);

                if (!$validate->check($postData)) {
                    $this->ReturnInfo(1001, $validate->getError());
                }

                if (count($postData['param']) > 1000) {
                    $this->ReturnInfo(1001);
                }

                $data = $postData['param'];
                $feifan = new FeiFanLost();
                $dataData = $feifan->getData($data);
                if (isset($dataData['RESULT']) && $dataData['RESULT'] == 1 && isset($dataData['guid'])) {
                    sleep(2);
                    $dataStatus = $feifan->getStatus($dataData['guid']);
                    $dataStatus['guid'] = $dataData['guid'];
                    $time = 30*24*3600;
                    cache($dataData['guid'],$dataStatus,$time);
                    if (isset($dataStatus['data'][0]['Tm_myMobile'][0]['phone'])) {
                        $this->ReturnInfo(1000, ["guid"=>$dataData['guid']]);
                    } else {
                        $this->ReturnInfo(1003, ["guid"=>$dataData['guid']]);
                    }
                } else {
                    $this->ReturnInfo(1003, $dataData ? $dataData : "");
                }

            } else {
                $this->ReturnInfo(1002);
            }
        } catch (ErrorException $e) {
            $this->ReturnInfo(1004);
        }

    }

    /**
     * info: 非凡大数据修复_查询结果
     * User: 伍先生
     * Date: 2020/9/29
     * Time: 16:17
     */
    public function SpecialBigDataGetStatus()
    {
        try {
            if ($this->request->isPost()) {
                $postData = input('post.');
                $rule = [
                    'guid' => 'require',
                ];
                $validate = new \think\Validate;
                $validate->rule($rule);

                if (!$validate->check($postData)) {
                    $this->ReturnInfo(1001);
                }

                //先查数据库
                $dataStatus = cache($postData['guid']);
                if (isset($dataStatus['data'][0]['Tm_myMobile'][0]['phone'])) {
                    $this->ReturnInfo(1000, $dataStatus);
                } else {
                    $this->ReturnInfo(1003);
                }

//                $feifan = new FeiFanLost();
//                $dataStatus = $feifan->getStatus($postData['guid']);
//                if (isset($dataStatus['data'][0]['Tm_myMobile'][0]['phone'])) {
//                    $dataStatus['guid'] = $postData['guid'];
//                    $this->ReturnInfo(1000, $dataStatus);
//                } else {
//                    $this->ReturnInfo(1003);
//                }
            } else {
                $this->ReturnInfo(1002);
            }
        } catch (ErrorException $e) {
            $this->ReturnInfo(1004);
        }

    }

    /**
     * Info: 运营商三要素实名认证简版
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/17 15:25
     * Url: /api/ServeData/IdNamePhoneCheck
     * {"serviceName":"IdNamePhoneCheck","Token":"CF0EF7508A9CE96972EA467CEDD9544C","idCard":"130821198711193787","nameCard":"侯立辉","mobile":"15851569929"}
     */
    public function IdNamePhoneCheck()
    {
        try {
            if ($this->request->isPost()) {

                $postData = input('post.');
                $rule = [
                    'mobile|手机号' => 'require|mobile',
                    'idCard|身份证' => 'require|idCard',
                    'name|名字' => 'require',
                ];
                $validate = new \think\Validate;
                $validate->rule($rule);

                if (!$validate->check($postData)) {
                    $this->ReturnInfo(1001);
                }

                $param = [
                    "idCard" => $postData['idCard'],
                    "name" => $postData['name'],
                    "mobile" => $postData['mobile'],
                ];
                $feifan = new FeiFanLost();
                $dataData = $feifan->idNamePhoneCheck($param);
                if (isset($dataData['RESULT']) && $dataData['RESULT'] > 0) {
                    $this->ReturnInfo(1000, $dataData);
                } else {
                    $this->ReturnInfo(1003, $dataData ? $dataData : "");
                }
            } else {
                $this->ReturnInfo(1002);
            }
        } catch (ErrorException $e) {
            $this->ReturnInfo(1004);
        }
    }

    /**
     * Info: 运营商三要素实名认证详版
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/17 15:29
     * Url:  /api/ServeData/IdNamePhoneDetailCheck
     * {"serviceName":"IdNamePhoneDetailCheck","Token":"6C8A322B7ED40324358EEF42698907D4","idCard":"130821198711193787","nameCard":"侯立辉","mobile":"15851569929"}
     */
    public function IdNamePhoneDetailCheck()
    {
        try {
            if ($this->request->isPost()) {

                $postData = input('post.');
                $rule = [
                    'mobile|手机号' => 'require|mobile',
                    'idCard|身份证' => 'require|idCard',
                    'name|名字' => 'require',
                ];
                $validate = new \think\Validate;
                $validate->rule($rule);

                if (!$validate->check($postData)) {
                    $this->ReturnInfo(1001);
                }

                $param = [
                    "idCard" => $postData['idCard'],
                    "name" => $postData['name'],
                    "mobile" => $postData['mobile'],
                ];
                $feifan = new FeiFanLost();
                $dataData = $feifan->IdNamePhoneDetailCheck($param);
                if (isset($dataData['RESULT']) && $dataData['RESULT'] > 0) {
                    $this->ReturnInfo(1000, $dataData);
                } else {
                    $this->ReturnInfo(1003, $dataData ? $dataData : "");
                }
            } else {
                $this->ReturnInfo(1002);
            }
        } catch (ErrorException $e) {
            $this->ReturnInfo(1004);
        }
    }

    /**
     * Info: 运营商在网状态
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/17 17:31
     * Url: /api/ServeData/MobileStatus
     */
    public function MobileStatus()
    {
        try {
            if ($this->request->isPost()) {

                $postData = input('post.');
                $rule = [
                    'mobile|手机号' => 'require|mobile',
                    'idCard|身份证' => 'require|idCard',
                    'name|名字' => 'require',
                ];
                $validate = new \think\Validate;
                $validate->rule($rule);

                if (!$validate->check($postData)) {
                    $this->ReturnInfo(1001, $validate->getError());
                }

                $param = [
                    "idCard" => $postData['idCard'],
                    "name" => $postData['name'],
                    "mobile" => $postData['mobile'],
                ];
                $feifan = new FeiFanLost();
                $dataData = $feifan->mobileStatus($param);
                if (isset($dataData['RESULT']) && $dataData['RESULT'] > 0) {
                    $this->ReturnInfo(1000, $dataData);
                } else {
                    $this->ReturnInfo(1003, $dataData ? $dataData : "");
                }
            } else {
                $this->ReturnInfo(1002);
            }

        } catch (ErrorException $e) {
            $this->ReturnInfo(1004);
        }
    }
}