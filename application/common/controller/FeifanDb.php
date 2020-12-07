<?php
/**
 * User: 伍先生
 * DateTime: 2020/11/1012:12
 * Class:  FeifanDb
 * Info: API测试管理
 */

namespace app\common\controller;


use think\Controller;

class FeifanDb extends Controller
{
    public function getGuid($guid){
        return  db('logs')->whereTime('log_adddata',"-3 month")->where("log_guid",$guid)->cache(8630000)->find();
    }
}