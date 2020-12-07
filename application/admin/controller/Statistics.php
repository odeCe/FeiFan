<?php
/**
 * 统计管理
 */

namespace app\admin\controller;


use app\jwcode\controller\AdminBasic;

class Statistics extends AdminBasic
{
    /**
     * info: 用量统计
     * User: 伍先生
     * Date: 2020/8/27
     * Time: 17:59
     */
    public function consumption()
    {
        if ($this->request->post()) {
            $postData = input('post.');
            $rule = [
                'dateTime|时间区间' => 'require',
                'serve_id|服务' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }
            //判断管理员还是客户
            if($this->super == 1 && empty($postData['user_id']) ){
                $this->result('', -1, "客户为必选", "json");
            }else{
                $postData['user_id'] = $this->uid;
            }


            $timeArr = explode('至', $postData['dateTime']);


            $countData = db('logs')
                ->where(['log_admin_id' => $postData['user_id'], 'log_serve_id' => $postData['serve_id']])
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


            $dataArr[0]["name"] = "共计使用量";
            $dataArr[0]["type"] = 'line';
            $dataArr[0]["areaStyle"] = '{}';
            $dataArr[0]["symbolSize"] = 10;
            $dataArr[0]["data"] = [];
            foreach ($countData as $key => $val) {
                $dataArr[0]["data"][$key] = db('logs')
                    ->where(['log_admin_id' => $postData['user_id'], 'log_serve_id' => $postData['serve_id']])
                    ->where('log_adddata', $val['log_adddata'])
                    ->cache(30)
                    ->count();
            }

            $dataArr[1]["name"] = "计费使用量(条)";
            $dataArr[1]["type"] = 'line';
            $dataArr[1]["areaStyle"] = '{}';
            $dataArr[1]["symbolSize"] = 10;
            $dataArr[1]["data"] = [];
            foreach ($countData as $key => $val) {
                $dataArr[1]["data"][$key] = db('logs')
                    ->where("log_code", 1000)
                    ->where(['log_admin_id' => $postData['user_id'], 'log_serve_id' => $postData['serve_id']])
                    ->where('log_type', 1)
                    ->cache(30)
                    ->where('log_adddata', $val['log_adddata'])
                    ->count();
            }

            $dataArr[2]["name"] = "消费金额(元)";
            $dataArr[2]["type"] = 'line';
            $dataArr[2]["areaStyle"] = '{}';
            $dataArr[2]["symbolSize"] = 10;
            $dataArr[2]["data"] = [];
            foreach ($countData as $key => $val) {
                $dataArr[2]["data"][$key] = db('logs')
                    ->where("log_code", 1000)
                    ->where(['log_admin_id' => $postData['user_id'], 'log_serve_id' => $postData['serve_id']])
                    ->where('log_type', 1)
                    ->where('log_adddata', $val['log_adddata'])
                    ->cache(30)
                    ->sum('log_price');
            }
            $this->result(['datetime' => $titleArr, 'dataArr' => $dataArr], 1, $validate->getError(), "json");
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
     * info: 总量统计
     * User: 伍先生
     * Date: 2020/8/27
     * Time: 17:59
     */
    public function total()
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

            $timeArr = explode('至', $postData['dateTime']);

            //判断管理员还是客户
            $whereSuper['log_code'] = 1000;
            if($this->super != 1){
                $whereSuper['log_admin_id'] = $this->uid;
            }


            $countData = db('logs')
                ->where($whereSuper)
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


            $dataArr[0]["name"] = "共计使用量";
            $dataArr[0]["type"] = 'line';
            $dataArr[0]["areaStyle"] = '{}';
            $dataArr[0]["symbolSize"] = 10;
            $dataArr[0]["data"] = [];
            foreach ($countData as $key => $val) {
                $dataArr[0]["data"][$key] = db('logs')
                    ->where($whereSuper)
                    ->where('log_adddata', $val['log_adddata'])
                    ->cache(300)
                    ->count();
            }

            $dataArr[1]["name"] = "计费使用量(条)";
            $dataArr[1]["type"] = 'line';
            $dataArr[1]["areaStyle"] = '{}';
            $dataArr[1]["symbolSize"] = 10;
            $dataArr[1]["data"] = [];
            foreach ($countData as $key => $val) {
                $dataArr[1]["data"][$key] = db('logs')
                    ->where($whereSuper)
                    ->where('log_type', 1)
                    ->cache(30)
                    ->where('log_adddata', $val['log_adddata'])
                    ->count();
            }

            $dataArr[2]["name"] = "消费金额(元)";
            $dataArr[2]["type"] = 'line';
            $dataArr[2]["areaStyle"] = '{}';
            $dataArr[2]["symbolSize"] = 10;
            $dataArr[2]["data"] = [];
            foreach ($countData as $key => $val) {
                $dataArr[2]["data"][$key] = db('logs')
                    ->where($whereSuper)
                    ->where('log_type', 1)
                    ->where('log_adddata', $val['log_adddata'])
                    ->cache(30)
                    ->sum('log_price');
            }
            $this->result(['datetime' => $titleArr, 'dataArr' => $dataArr], 1, $validate->getError(), "json");
        }
        return $this->fetch();
    }

    //客户用量
    public function total2()
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

            $timeArr = explode('至', $postData['dateTime']);

            //判断管理员还是客户
            $whereSuper['log_code'] = 1000;
            if($this->super != 1){
                $whereSuper['log_admin_id'] = $this->uid;
            }

            $countData = db('logs')
                ->where($whereSuper)
                ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                ->group("log_admin_id")
                ->field('log_admin_id')
                ->cache(30)
                ->order('log_admin_id', 'ase')
                ->select();
            $titleArr = [];
            foreach ($countData as $key => $val) {
                $titleArr[$key] = db('admin')->where('id', $val['log_admin_id'])->cache(600)->value('username');
            }
            $dataArr = [];


            $dataArr[0]["name"] = "共计使用量";
            $dataArr[0]["type"] = 'bar';
            $dataArr[0]["areaStyle"] = '{}';
            $dataArr[0]["symbolSize"] = 10;
            $dataArr[0]["data"] = [];
            foreach ($countData as $key => $val) {
                $dataArr[0]["data"][$key] = db('logs')
                    ->where($whereSuper)
                    ->where('log_admin_id', $val['log_admin_id'])
                    ->cache(300)
                    ->count();
            }

            $dataArr[1]["name"] = "计费使用量(条)";
            $dataArr[1]["type"] = 'bar';
            $dataArr[1]["areaStyle"] = '{}';
            $dataArr[1]["symbolSize"] = 10;
            $dataArr[1]["data"] = [];
            foreach ($countData as $key => $val) {
                $dataArr[1]["data"][$key] = db('logs')
                    ->where($whereSuper)
                    ->where('log_type', 1)
                    ->cache(30)
                    ->where('log_admin_id', $val['log_admin_id'])
                    ->count();
            }

            $dataArr[2]["name"] = "消费金额(元)";
            $dataArr[2]["type"] = 'bar';
            $dataArr[2]["areaStyle"] = '{}';
            $dataArr[2]["symbolSize"] = 10;
            $dataArr[2]["data"] = [];
            foreach ($countData as $key => $val) {
                $dataArr[2]["data"][$key] = db('logs')
                    ->where($whereSuper)
                    ->where('log_type', 1)
                    ->where('log_admin_id', $val['log_admin_id'])
                    ->cache(30)
                    ->sum('log_price');
            }
            $this->result(['datetime' => $titleArr, 'dataArr' => $dataArr], 1, $validate->getError(), "json");
        }
        return $this->fetch();
    }


    /**
     * info: 客户统计
     * User: 伍先生
     * Date: 2020/8/27
     * Time: 18:00
     */
    public function client()
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

            //判断管理员还是客户
            if($this->super == 1 && empty($postData['user_id']) ){
                $this->result('', -1, "客户为必选", "json");
            }else{
                $postData['user_id'] = $this->uid;
            }

            $timeArr = explode('至', $postData['dateTime']);


            $countData = db('logs')
                ->where("log_code", 1000)
                ->where(['log_admin_id' => $postData['user_id']])
                ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                ->group("log_serve_id")
                ->field('log_serve_id')
                ->cache(30)
                ->order('log_admin_id', 'ase')
                ->select();
            $legendData = [];
            $dataArr = [];

            foreach ($countData as $key => $val) {
                $serve_name = db('serve')->where('id', $val['log_serve_id'])->cache(600)->value('serve_name');
                $legendData[$key] = $serve_name;


                $dataArr[$key]['name'] = $serve_name;
                $dataArr[$key]['value'] = db('logs')
                    ->where("log_code", 1000)
                    ->where(['log_type' => '1', 'log_admin_id' => $postData['user_id'], 'log_serve_id' => $val['log_serve_id']])
                    ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                    ->count();
            }

            $this->result(['legendData' => $legendData, 'dataArr' => $dataArr], 1, $validate->getError(), "json");
        }

        //客户
        $admin = db('admin')->where('super', 2)->field('id,username')->select();
        $this->assign('admin', $admin);
        return $this->fetch();
    }

    public function client2()
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

            //判断管理员还是客户
            if($this->super == 1 && empty($postData['user_id']) ){
                $this->result('', -1, "客户为必选", "json");
            }else{
                $postData['user_id'] = $this->uid;
            }

            $timeArr = explode('至', $postData['dateTime']);


            $countData = db('logs')
                ->where("log_code", 1000)
                ->where(['log_admin_id' => $postData['user_id']])
                ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                ->group("log_serve_id")
                ->field('log_serve_id')
                ->cache(30)
                ->order('log_admin_id', 'ase')
                ->select();
            $legendData = [];
            $dataArr = [];

            foreach ($countData as $key => $val) {
                $serve_name = db('serve')->where('id', $val['log_serve_id'])->cache(600)->value('serve_name');
                $legendData[$key] = $serve_name;


                $dataArr[$key]['name'] = $serve_name;
                $dataArr[$key]['value'] = db('logs')
                    ->where("log_code", 1000)
                    ->where(['log_type' => '1', 'log_admin_id' => $postData['user_id'], 'log_serve_id' => $val['log_serve_id']])
                    ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                    ->sum('log_price');
            }

            $this->result(['legendData' => $legendData, 'dataArr' => $dataArr], 1, $validate->getError(), "json");
        }

        //客户
        $admin = db('admin')->where('super', 2)->field('id,username')->select();
        $this->assign('admin', $admin);
        return $this->fetch();
    }

    /**
     * info: 服务统计
     * User: 伍先生
     * Date: 2020/8/27
     * Time: 18:00
     */
    public function service()
    {
        if ($this->request->post()) {
            $postData = input('post.');
            $rule = [
                'dateTime|时间区间' => 'require',
                'serve_id|服务' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $timeArr = explode('至', $postData['dateTime']);

            //判断管理员还是客户
            $whereSuper['log_code'] = 1000;
            if($this->super != 1){
                $whereSuper['log_admin_id'] = $this->uid;
            }

            $countData = db('logs')
                ->where($whereSuper)
                ->where(['log_serve_id' => $postData['serve_id']])
                ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                ->group("log_admin_id")
                ->field('log_admin_id')
                ->cache(30)
                ->order('log_admin_id', 'ase')
                ->select();
            $legendData = [];
            $dataArr = [];

            foreach ($countData as $key => $val) {
                $serve_name = db('admin')->where('id', $val['log_admin_id'])->cache(600)->value('username');
                $legendData[$key] = $serve_name;


                $dataArr[$key]['name'] = $serve_name;
                $dataArr[$key]['value'] = db('logs')
                    ->cache(60)
                    ->where($whereSuper)
                    ->where(['log_type' => '1', 'log_admin_id' => $val['log_admin_id'], 'log_serve_id' => $postData['serve_id']])
                    ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                    ->count();
            }

            $this->result(['legendData' => $legendData, 'dataArr' => $dataArr], 1, $validate->getError(), "json");
        }

        //服务
        $serve = db('serve')
            ->cache(600)
            ->field('id,serve_name')
            ->select();
        $this->assign('serve', $serve);
        return $this->fetch();
    }

    public function service2()
    {
        if ($this->request->post()) {
            $postData = input('post.');
            $rule = [
                'dateTime|时间区间' => 'require',
                'serve_id|服务' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $timeArr = explode('至', $postData['dateTime']);

            //判断管理员还是客户
            $whereSuper['log_code'] = 1000;
            if($this->super != 1){
                $whereSuper['log_admin_id'] = $this->uid;
            }

            $countData = db('logs')
                ->where($whereSuper)
                ->where(['log_serve_id' => $postData['serve_id']])
                ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                ->group("log_admin_id")
                ->field('log_admin_id')
                ->cache(30)
                ->order('log_admin_id', 'ase')
                ->select();
            $legendData = [];
            $dataArr = [];

            foreach ($countData as $key => $val) {
                $serve_name = db('admin')->where('id', $val['log_admin_id'])->cache(600)->value('username');
                $legendData[$key] = $serve_name;


                $dataArr[$key]['name'] = $serve_name;
                $dataArr[$key]['value'] = db('logs')
                    ->cache(60)
                    ->where($whereSuper)
                    ->where(['log_type' => '1', 'log_admin_id' => $val['log_admin_id'], 'log_serve_id' => $postData['serve_id']])
                    ->whereTime('log_adddata', [$timeArr[0], $timeArr[1]])
                    ->sum('log_price');

            }

            $this->result(['legendData' => $legendData, 'dataArr' => $dataArr], 1, $validate->getError(), "json");
        }

        //服务
        $serve = db('serve')
            ->cache(600)
            ->field('id,serve_name')
            ->select();
        $this->assign('serve', $serve);
        return $this->fetch();
    }

}