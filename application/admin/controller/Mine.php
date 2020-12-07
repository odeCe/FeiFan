<?php
/**
 * 客户信息
 */

namespace app\admin\controller;


use app\jwcode\controller\AdminBasic;

class Mine extends AdminBasic
{
    /**
     * Info: 客户信息
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/11 17:23
     * Function:  info
     */
    public function info()
    {
        if ($this->request->post()) {
            $postData = input('post.');
            if (empty($postData['pass'])) {
                $this->result('', -1, '没有数据可以更新', 'json');
            }
            $updata['password'] = set_password($postData['pass']);

            $bool = db('admin')->where('id', $updata['id'])->update($updata);
            if ($bool) {
                $this->result('', 1, '更新成功', 'json');
            } else {
                $this->result('', -1, '更新失败', 'json');
            }

        }


        $userinfo = db('admin')
            ->alias('a')
            ->leftJoin('company c', "c.id = a.company_id")
            ->where('a.id', $this->uid)
            ->field("a.*,tissue")
            ->find();
        $this->assign("user", $userinfo);
        return $this->fetch();
    }

    /**
     * Info: 客户服务
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/12 22:01
     * Function:  serve
     */
    public function serve()
    {
        if ($this->request->post()) {
            $list = db('capacity')
                ->alias('c')
                ->join('jw_serve s', "s.id = c.serve_id")
                ->where('admin_id', $this->uid)
                ->order('id', 'desc')
                ->field('c.*,serve_name,serve_code')
                ->select();
            return $this->tableList(0, '成功', $list, 0);
        }
        return $this->fetch();
    }

    /**
     * Info: 充值
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/12 22:01
     * Function:  charge
     */
    public function charge()
    {
        if ($this->request->post()) {
            $postData = input('post.');
            $rule = [
                'pay_money|充值金额' => 'require|number',
                'pay_msg|说明' => 'require',

            ];
            $validate = new \think\Validate;
            $validate->rule($rule);
            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }
            $dataPay['admin_id'] = $this->uid;
            $dataPay['pay_type'] = 2;
            $dataPay['pay_orderid'] = get_order_sn();
            $dataPay['pay_money'] = $postData['pay_money']; //金额
            $dataPay['pay_statu'] = 3; // 状态 1成功 2失败 3提交
            $dataPay['pay_msg'] = $postData['pay_msg']; // 操作说明
            $dataPay['addtime'] = date("Y-m-d H:i:s");
            $bool = db('pay')->insert($dataPay);

            if ($bool) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }
        } else {


            $data = db('System')->where('title','pay')->value('json');
            $data = json_decode($data,1);
            $this->assign($data);

            $where['id'] = $this->uid;
            $user = db('admin')->where($where)->find();
            $this->assign($user);
            return $this->fetch();
        }
    }

    /**
     * Info: 充值记录
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/12 22:06
     * Function:  chargelist
     */
    public function chargelist()
    {
        if ($this->request->post()) {

            $postData = input('post.');
            $page = 0;
            $limit = 10;
            if (input('?page') && input('?limit')) {
                $page = ($postData['page'] - 1) * 10;
                $limit = $postData['limit'];
            }
            $table = db("pay");

            $where = [];

            if (isset($postData['username'])) {
                $table->where('account|pay_orderid', 'like', '%' . $postData['username'] . '%');
            }


            $paylist = $table->where($where)
                ->alias('p')
                ->join('admin a','a.id = p.admin_id')
                ->limit($page, $limit)
                ->order('p.id', 'desc')
                ->where('admin_id',$this->uid)
                ->field('p.*,account')
                ->select();
            $count = $table->where($where)->count();
            return $this->tableList(0, '成功', $paylist, $count);
        }
        return $this->fetch();
    }

    /**
     * Info: 对账清单
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/20 22:25
     * Function:  expenditure
     */
    public function expenditure()
    {


        $postData = input();
        if(!isset($postData['dateTime']) || empty($postData['dateTime'])){
            $postData['dateTime'] = date("Y-m");
        }
        $this->assign("dateTime",$postData['dateTime']);
        if ($this->request->post()) {

            $startTime =  $postData['dateTime']."-01";
            $endTime = date('Y-m-01', strtotime ( "$startTime +1 month") );
            $dateTime = [$startTime,$endTime];


            $countData = db('logs')
                ->where(['log_admin_id' => $this->uid])
                ->whereTime('log_adddata', $dateTime)
                ->group("log_serve_id")
                ->field('log_serve_id,log_id')
                ->cache(30)
                ->select() ;



            $arr = [];
            foreach ($countData as $key => $val) {
                $arr[$key]['serve_name'] = db('serve')->where('id',
                    $val['log_serve_id'])->cache(30)->value('serve_name');
                $arr[$key]['serve_num'] = db('logs')->whereTime('log_adddata',
                    $dateTime)->where(['log_serve_id'=>$val['log_serve_id'],'log_admin_id' =>  $this->uid])->count();
                $arr[$key]['serve_num_pirce'] = db('logs')->whereTime('log_adddata',
                    $dateTime)->where(['log_serve_id'=>$val['log_serve_id'],'log_admin_id' =>  $this->uid,'log_type'=>1])->count();
                $arr[$key]['serve_pirce'] = db('logs')->whereTime('log_adddata',
                    $dateTime)->where(['log_serve_id'=>$val['log_serve_id'],'log_admin_id' =>  $this->uid,'log_type'=>1])->sum('log_price');
            }
            $count = count($arr);
            $arr[$count]['serve_name'] = "合计";
            $arr[$count]['serve_num'] = db('logs')
                ->where(['log_admin_id' => $this->uid])
                ->whereTime('log_adddata', $dateTime)
                ->count();
            $arr[$count]['serve_num_pirce'] = db('logs')
                ->where(['log_admin_id' => $this->uid])
                ->whereTime('log_adddata', $dateTime)
                ->where(['log_type' => 1])
                ->count();
            $arr[$count]['serve_pirce'] = db('logs')
                ->where(['log_admin_id' => $this->uid])
                ->whereTime('log_adddata', $dateTime)
                ->where(['log_type' => 1])
                ->sum('log_price');

            return $this->tableList(0, '成功', $arr, 0);
        }
        return $this->fetch();
    }
}