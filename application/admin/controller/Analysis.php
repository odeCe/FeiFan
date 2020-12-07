<?php
/**
 * 分析管理
 */

namespace app\admin\controller;


use app\common\controller\FeiFanLost;
use app\common\tool\Desensitization;
use app\jwcode\controller\AdminBasic;

class Analysis extends AdminBasic
{
    /**
     * info: 服务分析
     * User: 伍先生
     * Date: 2020/8/27
     * Time: 18:27
     */
    public function service()
    {
        if ($this->request->post()) {
            $postData = input('post.');
            $rule = [
                'dateTime|时间区间' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }
            $this->assign("date",$postData['dateTime']);
            $timeArr = explode('至', $postData['dateTime']);

            //判断管理员还是客户
            $whereSuper=[];
            if($this->super != 1){
                $whereSuper['log_admin_id'] = $this->uid;
            }

            $countData = db('logs')
                ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                ->where($whereSuper)
                ->group("log_serve_id")
                ->field('log_serve_id')
                ->cache(30)
                ->select();


            //服务金额分布
            $legendData1 = [];
            $dataPriceArr = [];
            foreach ($countData as $key => $val) {
                $serve_name = db('serve')->where('id', $val['log_serve_id'])->cache(600)->value('serve_name');
                $legendData1[$key] = $serve_name;
                $dataPriceArr[$key]['name'] = $serve_name;
                $dataPriceArr[$key]['value'] = db('logs')
                    ->where('log_code',1000)
                    ->where(['log_type' => '1', 'log_serve_id' => $val['log_serve_id']])
                    ->where($whereSuper)
                    ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                    ->sum('log_price');
            }
            $fwje = ['legendData' => $legendData1, "dataPriceArr" => $dataPriceArr];

            //服务次数分布
            $dataNumArr = [];
            $dataNumArr["name"] = '访问量';
            $dataNumArr["type"] = 'bar';
            $dataNumArr["barWidth"] = '60%';
            $dataNumArr["areaStyle"] = '{}';
            $dataNumArr["symbolSize"] = 10;
            foreach ($countData as $key => $val) {
                $dataNumArr['data'][$key] = db('logs')
                    ->where($whereSuper)
                    ->where(['log_serve_id' => $val['log_serve_id']])
                    ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                    ->count();
            }
            $fwcs = ['legendData' => $legendData1, "dataNumArr" => $dataNumArr];


            //服务别表分析
            $tableDataArr = [];
            foreach ($countData as $key => $val) {
                $tableDataArr[$key]['serve_name'] = db('serve')->where('id', $val['log_serve_id'])->value('serve_name');
                $tableDataArr[$key]['serve_price'] = db('logs')->where('log_code',1000)->where($whereSuper)->where(['log_type' => '1','log_serve_id' => $val['log_serve_id']])->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])->sum('log_price');
                $tableDataArr[$key]['serve_num'] = db('logs')->where(['log_serve_id' => $val['log_serve_id']])->where($whereSuper)->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])->count();;
                $tableDataArr[$key]['serve_hszd'] = db('logs')->where(['log_serve_id' => $val['log_serve_id']])->where($whereSuper)->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])->max('log_consuming');
                $tableDataArr[$key]['serve_hspj'] = db('logs')->where(['log_serve_id' => $val['log_serve_id']])->where($whereSuper)->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])->avg('log_consuming');
                $tableDataArr[$key]['serve_hszs'] = db('logs')->where(['log_serve_id' => $val['log_serve_id']])->where($whereSuper)->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])->min('log_consuming');
            }


            $this->result(['je' => $fwje, 'cs' => $fwcs, 'tb' => $tableDataArr], 1, '成功', "json");
        }

        $date = date('Y-m-d').' 至 '.date('Y-m-d',strtotime("+1 day"));
        $this->assign("date",$date);
        return $this->fetch();
    }


    /**
     * info: 用户分析
     * User: 伍先生
     * Date: 2020/8/27
     * Time: 18:29
     */
    public function user()
    {
        if ($this->request->post()) {
            $postData = input('post.');
            $rule = [
                'dateTime|时间区间' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }
            $this->assign("date",$postData['dateTime']);
            $timeArr = explode('至', $postData['dateTime']);


            //判断管理员还是客户
            $whereSuper=[];
            if($this->super != 1){
                $whereSuper['log_admin_id'] = $this->uid;
            }

            $countData = db('logs')
                ->where($whereSuper)
                ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                ->group("log_admin_id")
                ->field('log_admin_id')
                ->cache(30)
                ->select();

            //服务金额分布
            $legendData1 = [];
            $dataPriceArr = [];
            foreach ($countData as $key => $val) {
                $serve_name = db('admin')->where('id', $val['log_admin_id'])->cache(600)->value('username');
                $legendData1[$key] = $serve_name;
                $dataPriceArr[$key]['name'] = $serve_name;
                $dataPriceArr[$key]['value'] = db('logs')
                    ->where("log_code",1000)
                    ->where(['log_type' => '1', 'log_admin_id' => $val['log_admin_id']])
                    ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                    ->sum('log_price');
            }
            $fwje = ['legendData' => $legendData1, "dataPriceArr" => $dataPriceArr];

            //服务次数分布
            $dataNumArr = [];
            $dataNumArr["name"] = '访问量';
            $dataNumArr["type"] = 'bar';
            $dataNumArr["barWidth"] = '60%';
            $dataNumArr["areaStyle"] = '{}';
            $dataNumArr["symbolSize"] = 10;
            foreach ($countData as $key => $val) {
                $dataNumArr['data'][$key] = db('logs')
                    ->where(['log_admin_id' => $val['log_admin_id']])
                    ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                    ->count();
            }
            $fwcs = ['legendData' => $legendData1, "dataNumArr" => $dataNumArr];


            //服务别表分析
            $tableDataArr = [];
            foreach ($countData as $key => $val) {
                $tableDataArr[$key]['serve_name'] = db('admin')->where('id', $val['log_admin_id'])->value('username');
                $tableDataArr[$key]['serve_price'] = db('logs')->where($whereSuper)->where("log_code",1000)->where(['log_type' => '1','log_admin_id' => $val['log_admin_id']])->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])->sum('log_price');
                $tableDataArr[$key]['serve_num'] = db('logs')->where($whereSuper)->where(['log_admin_id' => $val['log_admin_id']])->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])->count();;
                $tableDataArr[$key]['serve_hszd'] = db('logs')->where($whereSuper)->where(['log_admin_id' => $val['log_admin_id']])->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])->max('log_consuming');
                $tableDataArr[$key]['serve_hspj'] = db('logs')->where($whereSuper)->where(['log_admin_id' => $val['log_admin_id']])->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])->avg('log_consuming');
                $tableDataArr[$key]['serve_hszs'] = db('logs')->where($whereSuper)->where(['log_admin_id' => $val['log_admin_id']])->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])->min('log_consuming');
            }


            $this->result(['je' => $fwje, 'cs' => $fwcs, 'tb' => $tableDataArr], 1, '成功', "json");
        }
        $date = date('Y-m-d').' 至 '.date('Y-m-d',strtotime("+1 day"));
        $this->assign("date",$date);
        return $this->fetch();
    }

    /**
     * info: 日志分析
     * User: 伍先生
     * Date: 2020/8/27
     * Time: 18:29
     */
    public function log()
    {
        if($this->request->post()){
            $postData = input('post.');


            $table= db('logs')
                ->alias('log')
                ->join('admin a','a.id=log.log_admin_id');

            if(isset($postData['dateTime']) && !empty($postData['dateTime'])){
                $timeArr = explode('至', $postData['dateTime']);
                $table = $table->whereTime('log_adddata', [$timeArr[0], $timeArr[1]]);
            }

            if(isset($postData['user_id']) && !empty($postData['user_id'])){
                $table = $table->where('log_admin_id',$postData['user_id']);
            }else if ($this->super != 1){
                $table = $table->where('log_admin_id',$this->uid);
            }

            if(isset($postData['serve_id']) && !empty($postData['serve_id'])){
                $table = $table->where('log_serve_id',$postData['serve_id']);
            }

            if(isset($postData['username']) && !empty($postData['username'])){
                $table = $table->where('log_qeru', 'like', '%'.$postData['username'].'%');
            }
            if(isset($postData['idcard']) && !empty($postData['idcard'])){
                $table = $table->where('log_qeru', 'like', '%'.$postData['idcard'].'%');
            }

            if(isset($postData['gt']) && !empty($postData['gt'])){
                $table = $table->where('log_consuming','>',$postData['gt']);
            }

            if(isset($postData['lt']) && !empty($postData['lt'])){
                $table = $table->where('log_consuming','<',$postData['lt']);
            }

            $limit = input('limit',10);
            $page = (input('page',1) - 1 ) * $limit;
            $order = ['log_id'=>'desc','log_addtime'=>'desc'];

            $count = $table->count();

            $userlist= $table
                ->limit($page,$limit)
                ->order($order)
                ->field('log.*,username')
                ->select();

            foreach ($userlist as $key => $val) {
                $userlist[$key]['log_qeru'] = (new Desensitization)->selectName(json_decode($val['log_qeru'], 1));
            }

            return $this->tableList(0,'成功',$userlist,$count);
        }

        //客户
        $admin = db('admin')->where('super', 2)->field('id,username')->select();
        $this->assign('admin', $admin);

        //服务
        $serve = db('serve')
            ->cache(600)
            ->field('id,serve_name')
            ->select();
        $this->assign('serve', $serve);

        return $this->fetch();
    }

    /**
     * info: guid分析
     * User: 伍先生
     * Date: 2020/8/27
     * Time: 18:29
     */
    public function guid()
    {
        if ($this->request->post()) {
            $postData = input('post.');
            $rule = [
                'guid' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }
            $data = db('logs')->where('log_guid', $postData['guid'])->find();

            if($data){
                $data['log_qeru'] = isset($data['log_qeru'])?json_decode($data['log_qeru'],1):"";

                if($data['log_service_name'] == "IdNamePhoneMapV1"){
                    $data['log_resp'] = cache($data['log_guid']);
                }else{
                    $data['log_resp'] = isset($data['log_resp'])?json_decode($data['log_resp'],1):"";
                }

                //打码处理
                $data = $this->dama($data);
            }else{
                $feifan = new FeiFanLost();
                $dataStatus = $feifan->getStatus(trim($postData['guid']));
                $data = $this->dama($dataStatus);
            }



            $this->result($data,1,'成功','json');
        }
        return $this->fetch();
    }

    /**
     * info: 消费水平
     * User: 伍先生
     * Date: 2020/8/27
     * Time: 18:30
     */
    public function consumption()
    {
        if ($this->request->post()) {
            $postData = input('post.');
            $rule = [
                'serve_id|服务' => 'require',
                'type|查询方式' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            //判断管理员还是客户
            if($this->super == 1 && empty($postData['username']) ){
                $this->result('', -1, "客户为必选", "json");
            }

            if($this->super == 2 ){
                $postData['username'] = $this->uid;
            }

            if($postData['type'] == 1){
                if(!isset($postData['dateTime']) || empty($postData['dateTime'])){
                    $this->result('', -1, '请选择时间', "json");
                }

                $timeArr = explode('至', $postData['dateTime']);

                $countData = db('logs')
                    ->where("log_code",1000)
                    ->where(['log_admin_id' => $postData['username'], 'log_serve_id' => $postData['serve_id']])
                    ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                    ->group("log_adddata")
                    ->field('log_adddata')
                    ->cache(30)
                    ->order('log_adddata', 'ase')
                    ->select();
                $titleArr = [];
                foreach ($countData as $key => $val) {
                    $titleArr[$key] = $val['log_adddata'];
                }
                $dataArr = [];


                $dataArr[0]["name"] = "日计使用量";
                $dataArr[0]["type"] = 'bar';
                $dataArr[0]["areaStyle"] = '{}';
                $dataArr[0]["symbolSize"] = 10;
                $dataArr[0]["data"] = [];
                foreach ($countData as $key => $val) {
                    $dataArr[0]["data"][$key] = db('logs')
                        ->where("log_code",1000)
                        ->where(['log_admin_id' => $postData['username'], 'log_serve_id' => $postData['serve_id']])
                        ->where('log_adddata', $val['log_adddata'])
                        ->cache(30)
                        ->count();
                }

                $dataArr[1]["name"] = "日计费使用量(条)";
                $dataArr[1]["type"] = 'bar';
                $dataArr[1]["areaStyle"] = '{}';
                $dataArr[1]["symbolSize"] = 10;
                $dataArr[1]["data"] = [];
                foreach ($countData as $key => $val) {
                    $dataArr[1]["data"][$key] = db('logs')
                        ->where("log_code",1000)
                        ->where(['log_admin_id' => $postData['username'], 'log_serve_id' => $postData['serve_id']])
                        ->where('log_type',1)
                        ->cache(30)
                        ->where('log_adddata', $val['log_adddata'])
                        ->count();
                }

                $dataArr[2]["name"] = "日消费金额(元)";
                $dataArr[2]["type"] = 'bar';
                $dataArr[2]["areaStyle"] = '{}';
                $dataArr[2]["symbolSize"] = 10;
                $dataArr[2]["data"] = [];
                foreach ($countData as $key => $val) {
                    $dataArr[2]["data"][$key] = db('logs')
                        ->where("log_code",1000)
                        ->where(['log_admin_id' => $postData['username'], 'log_serve_id' => $postData['serve_id']])
                        ->where('log_type',1)
                        ->where('log_adddata', $val['log_adddata'])
                        ->cache(30)
                        ->sum('log_price');
                }
                $this->result(['legendData'=>['日计使用量','日计费使用量(条)','日消费金额(元)'],'datetime'=>$titleArr,'dataArr'=>$dataArr], 1, $validate->getError(), "json");



            }else{
                if(!isset($postData['dateTime2']) || empty($postData['dateTime2'])){
                    $this->result('', -1, '请选择时间', "json");
                }

                $timeArr = explode('至', $postData['dateTime2']);




                $countData = $this->timeDate($timeArr[0],$timeArr[1]);
                $titleArr = $countData;
                $dataArr = [];


                $dataArr[0]["name"] = "月计使用量";
                $dataArr[0]["type"] = 'bar';
                $dataArr[0]["areaStyle"] = '{}';
                $dataArr[0]["symbolSize"] = 10;
                $dataArr[0]["data"] = [];
                foreach ($countData as $key => $val) {
                    $dataArr[0]["data"][$key] = db('logs')
                        ->where("log_code",1000)
                        ->where(['log_admin_id' => $postData['username'], 'log_serve_id' => $postData['serve_id']])
                        ->whereTime('log_adddata', $val)
                        ->cache(30)
                        ->count();
                }

                $dataArr[1]["name"] = "月计费使用量(条)";
                $dataArr[1]["type"] = 'bar';
                $dataArr[1]["areaStyle"] = '{}';
                $dataArr[1]["symbolSize"] = 10;
                $dataArr[1]["data"] = [];
                foreach ($countData as $key => $val) {
                    $dataArr[1]["data"][$key] = db('logs')
                        ->where("log_code",1000)
                        ->where(['log_admin_id' => $postData['username'], 'log_serve_id' => $postData['serve_id']])
                        ->where('log_type',1)
                        ->cache(30)
                        ->whereTime('log_adddata', $val)
                        ->count();
                }

                $dataArr[2]["name"] = "月消费金额(元)";
                $dataArr[2]["type"] = 'bar';
                $dataArr[2]["areaStyle"] = '{}';
                $dataArr[2]["symbolSize"] = 10;
                $dataArr[2]["data"] = [];
                foreach ($countData as $key => $val) {
                    $dataArr[2]["data"][$key] = db('logs')
                        ->where("log_code",1000)
                        ->where(['log_admin_id' => $postData['username'], 'log_serve_id' => $postData['serve_id']])
                        ->where('log_type',1)
                        ->whereTime('log_adddata', $val)
                        ->cache(30)
                        ->sum('log_price');
                }
                $this->result(['legendData'=>['月计使用量','月计费使用量(条)','月消费金额(元)'],'datetime'=>$titleArr,'dataArr'=>$dataArr], 1, $validate->getError(), "json");

            }
        }


        //客户
        $admin = db('admin')->where('super', 2)->field('id,username')->select();
        $this->assign('admin', $admin);

        //服务
        $serve = db('serve')
            ->cache(600)
            ->field('id,serve_name')
            ->select();
        $this->assign('serve', $serve);

        return $this->fetch();
    }

    /**
     * info: 服务质量
     * User: 伍先生
     * Date: 2020/8/27
     * Time: 18:31
     */
    public function quality()
    {
        if ($this->request->post()) {

            $postData = input('post.');
            $rule = [
                'serve_id|服务' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $where['log_serve_id'] = $postData['serve_id'];

            //判断管理员还是客户
            if($this->super == 2 ){
                $postData['user_id'] = $this->uid;
            }

            if(isset($postData['user_id']) && !empty($postData['user_id'])){
                $where['log_admin_id'] = $postData['user_id'];
            }


            $countData = db('logs')
                ->where("log_code",1000)
                ->where($where)
                ->whereTime('log_addtime','-2 hours')
                ->group("log_addtime")
                ->field('log_addtime')
                ->select();



            $titleArr = [];
            foreach ($countData as $key => $val) {
                $titleArr[$key] = $val['log_addtime'];
            }
            $dataArr = [];

            $dataArr[0]["name"] = "实时响应分析(ms)";
            $dataArr[0]["type"] = 'line';
            $dataArr[0]["areaStyle"] = '{}';
            $dataArr[0]["symbolSize"] = 10;
            $dataArr[0]["data"] = [];
            foreach ($countData as $key => $val) {
                $dataArr[0]["data"][$key] = db('logs')
                    ->where("log_code",1000)
                    ->where($where)
                    ->where('log_addtime', $val['log_addtime'])
                    ->avg('log_consuming');
            }

            $dataArr[1]["name"] = "实时调用量(条)";
            $dataArr[1]["type"] = 'line';
            $dataArr[1]["areaStyle"] = '{}';
            $dataArr[1]["symbolSize"] = 10;
            $dataArr[1]["data"] = [];
            foreach ($countData as $key => $val) {
                $dataArr[1]["data"][$key] = db('logs')
                    ->where("log_code",1000)
                    ->where($where)
                    ->where('log_addtime', $val['log_addtime'])
                    ->count();
            }

            $dataArr[2]["name"] = "实时计费调用量(条)";
            $dataArr[2]["type"] = 'line';
            $dataArr[2]["areaStyle"] = '{}';
            $dataArr[2]["symbolSize"] = 10;
            $dataArr[2]["data"] = [];
            foreach ($countData as $key => $val) {
                $dataArr[2]["data"][$key] = db('logs')
                    ->where("log_code",1000)
                    ->where($where)
                    ->where('log_addtime', $val['log_addtime'])
                    ->where('log_type',1)
                    ->count();
            }
            $this->result(['datetime'=>$titleArr,'dataArr'=>$dataArr], 1, $validate->getError(), "json");
        }
        //客户
        $admin = db('admin')->where('super', 2)->field('id,username')->select();
        $this->assign('admin', $admin);

        //服务
        $serve = db('serve')
            ->cache(600)
            ->field('id,serve_name')
            ->select();
        $this->assign('serve', $serve);
        return $this->fetch();
    }
    public function qualityhistory()
    {
        if ($this->request->post()) {

            $postData = input('post.');
            $rule = [
                'serve_id|服务' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $where['log_serve_id'] = $postData['serve_id'];

            //判断管理员还是客户
            if($this->super == 2 ){
                $postData['user_id'] = $this->uid;
            }

            if(isset($postData['user_id']) && !empty($postData['user_id'])){
                $where['log_admin_id'] = $postData['user_id'];
            }

            $countData = db('logs')
                ->where("log_code",1000)
                ->where($where)
                ->whereTime('log_addtime','-7 day')
                ->group("log_addtime")
                ->field('log_addtime')
                ->select();



            $titleArr = [];
            foreach ($countData as $key => $val) {
                $titleArr[$key] = $val['log_addtime'];
            }
            $dataArr = [];

            $dataArr[0]["name"] = "7天历史响应分析(ms)";
            $dataArr[0]["type"] = 'line';
            $dataArr[0]["areaStyle"] = '{}';
            $dataArr[0]["symbolSize"] = 10;
            $dataArr[0]["data"] = [];
            foreach ($countData as $key => $val) {
                $dataArr[0]["data"][$key] = db('logs')
                    ->where("log_code",1000)
                    ->where($where)
                    ->where('log_addtime', $val['log_addtime'])
                    ->avg('log_consuming');
            }

            $dataArr[1]["name"] = "7天历史调用量(条)";
            $dataArr[1]["type"] = 'line';
            $dataArr[1]["areaStyle"] = '{}';
            $dataArr[1]["symbolSize"] = 10;
            $dataArr[1]["data"] = [];
            foreach ($countData as $key => $val) {
                $dataArr[1]["data"][$key] = db('logs')
                    ->where("log_code",1000)
                    ->where($where)
                    ->where('log_addtime', $val['log_addtime'])
                    ->count();
            }

            $dataArr[2]["name"] = "7天历史计费调用量(条)";
            $dataArr[2]["type"] = 'line';
            $dataArr[2]["areaStyle"] = '{}';
            $dataArr[2]["symbolSize"] = 10;
            $dataArr[2]["data"] = [];
            foreach ($countData as $key => $val) {
                $dataArr[2]["data"][$key] = db('logs')
                    ->where("log_code",1000)
                    ->where($where)
                    ->where('log_addtime', $val['log_addtime'])
                    ->where('log_type',1)
                    ->count();
            }
            $this->result(['datetime'=>$titleArr,'dataArr'=>$dataArr], 1, $validate->getError(), "json");
        }
        //客户
        $admin = db('admin')->where('super', 2)->field('id,username')->select();
        $this->assign('admin', $admin);

        //服务
        $serve = db('serve')
            ->cache(600)
            ->field('id,serve_name')
            ->select();
        $this->assign('serve', $serve);
        return $this->fetch();
    }

    public function timeDate($data1,$data2){

        $timeArray[] = strtotime($data1); //开始时间
        $timeArray[] = strtotime($data2); //结束时间
        $TiOneDay = "86400"; //1天秒数
        $arr[] = date("Y-m-d", $timeArray[0]); //当前月
        while ($timeArray[0] < $timeArray[1]) {
            //计算当前天数;
            $timeArray[0] += date("t", $timeArray[0]) * $TiOneDay; //获取本月天数*小时 ++
            $arr[] = date("Y-m-d", $timeArray[0]); //获取当前月数
        }
        return $arr;

    }


    function testaa(){
        $json = '{"log_id":598,"log_seed":"20201030AA207968B59F92D3","log_admin_id":16,"log_serve_id":5,"log_user_serve_id":13,"log_adddata":"2020-10-30","log_addtime":"2020-10-30 17:28:34","log_retime":"2020-10-30 17:28:34","log_service_name":"IdNamePhoneMapV1","log_consuming":191,"log_qeru":{"serviceName":"IdNamePhoneMapV1","Token":"1FAFE0CF85D83B56E3E06FC12D67F641","param":[{"no":2,"idCard":"440105197011054516","name":"李升"},{"no":3,"idCard":"130821198711193787","name":"侯立辉"},{"no":4,"idCard":"440105197011054517","name":"李升2"}]},"log_resp":{"MESSAGE":"异常情况","data":{"paramError":{"no":4,"idCard":"440105197011054517","name":"李升2"}},"guid":"20201030172834590_519605684363329536","detail":{"code":"23501"},"RESULT":-1},"log_type":"2","log_price":"0.00","log_ip":"113.65.16.9","log_json1":null,"log_json2":null,"log_json3":null,"log_json4":null,"log_code":1000,"log_guid":"20201030172834590_519605684363329536"}';
        $arr = json_decode($json,1);
        $aa = $this->dama($arr);
        var_dump($aa);
    }
    //打码
    // 身份证打码  idCard
    // 名字打码  name nameCard
    function dama($arr){
        foreach ($arr as $key=>$val){
            if ($key === "idCard"){

                $arr[$key] = $this->IdCard($arr[$key]);
            }
            if ($key === "name"){
                $arr[$key] = $this->NameCard($arr[$key]);
            }
            if (is_array($val)){
                $arr[$key] = $this->dama($val);
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

}