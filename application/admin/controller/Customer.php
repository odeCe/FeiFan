<?php
/**
 * User: 伍先生
 * DateTime: 2020/9/2 21:36
 * Class:  Customer
 * Info: 客户管理
 */

namespace app\admin\controller;


use app\jwcode\controller\AdminBasic;

class Customer extends AdminBasic
{
    /**
     * Info: 客户列表
     * Argument :
     * User: 伍先生
     * Date: 2019/8/29
     * Time: 23:25
     */
    public function lists()
    {
        if ($this->request->post()) {
            $postData = input('post.');
            $page = 0;
            $limit = 10;
            if (input('?page') && input('?limit')) {
                $page = ($postData['page'] - 1) * 10;
                $limit = $postData['limit'];
            }

            $order = ['a.id' => 'desc', 'a.addtime' => 'desc'];
            if (input('?field') && input('?order')) {
                $order = [$postData['field'] => $postData['order']];
            }
            $where['is_del'] = 1;
            $table = db('admin')
                ->where('super', 2)
                ->alias('a')
                ->leftJoin('company c', 'a.company_id = c.id')
                ->field('a.*,c.tissue');
            if (isset($postData['username'])) {
                $table->where('a.account|a.username|c.tissue', 'like', '%' . $postData['username'] . '%');
            }
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
     * Info: 添加客户
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/8 19:24
     * Function:  custadd
     */
    public function custadd()
    {
        if ($this->request->post()) {
            $postData = array_remove_empty(input('post.'));

            $rule = [
                'account|账号' => 'require|length:6,16',
                'username|用户名' => 'require|length:2,10',
                'guest_type|客户类型' => 'require',
                'company_id|组织信息' => 'require',
                'pay_type|付费类型' => 'require',
                'is_del|客户状态' => 'require',
                'pass' => 'require|length:6,16',
                'repass' => 'require|length:6,16',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError());
            }

            if (account_heavy($postData['account'])) {
                $this->result('', -1, '该账号已存在');
            }

            if ($postData['pass'] != $postData['repass']) {
                $this->result('', -1, '两次密码不一样');
            }

            $inertData['super'] = 2;
            $inertData['account'] = $postData['account'];
            $inertData['username'] = $postData['username'];
            $inertData['guest_type'] = $postData['guest_type'];
            $inertData['company_id'] = $postData['company_id'];
            $inertData['pay_type'] = $postData['pay_type'];
            $inertData['is_del'] = $postData['is_del'];
            $inertData['password'] = set_password($postData['pass']);


            $inertData['addtime'] = $inertData['lasttime'] = time();
            $inertData['first_ip'] = $inertData['last_ip'] = get_client_ip();

            $bool = db('admin')->insert($inertData);

            if ($bool) {
                $this->result('', 1, '成功');
            } else {
                $this->result('', -1, '失败');
            }
        }
        $company = db('company')->field('id,tissue')->select();
        $this->assign('company', $company);

        return $this->fetch();
    }

    /**
     * Info: 修改客户
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/9 22:13
     * Function:  custedit
     */
    public function custedit()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $updata = array_remove_empty($postData);

            if ($updata['id']) {
                if (isset($updata['pass'])) {
                    $updata['password'] = set_password($updata['pass']);
                    unset($updata['pass']);
                }

                $where['id'] = $updata['id'];

                unset($updata['account']);
                $bool = db('admin')->where($where)->update($updata);
                if ($bool) {
                    $this->result('', 1, '成功');
                } else {
                    $this->result('', -1, '失败');
                }
            } else {
                $this->result('', -1, '参数错误');
            }

        } else {

            $where['id'] = input('id');
            $where['super'] = 2;
            $user = db('admin')->where($where)->find();
            $this->assign($user);


            $company = db('company')->field('id,tissue')->select();
            $this->assign('company', $company);
            return $this->fetch();
        }
    }

    /**
     * Info: 客户状态
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/9 22:13
     * Function:  custstatu
     */
    public function custstatu()
    {
        $updata['is_del'] = input('del');
        $where['id'] = input('id');
        $bool = db('admin')->where($where)->update($updata);

        if ($bool) {
            $this->result('', 1, '更新成功');
        } else {
            $this->result('', -1, '更新失败');
        }
    }

    /**
     * Info: 客户删除
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/9 22:13
     * Function:  custdel
     */
    public function custdel()
    {
        $where['id'] = input('id');
        $bool = db('admin')->where($where)->delete();

        if ($bool) {
            $this->result('', 1, '成功');
        } else {
            $this->result('', -1, '失败');
        }
    }


    /**
     * Info: 管理员给客户充值
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/9 22:13
     * Function:  directpay
     */
    public function directpay()
    {
        if ($this->request->post()) {
            $postData = input('post.');
            if ($postData['id']) {
                $where['id'] = $postData['id'];
                $bool = db('admin')->where($where)->setInc('balance', $postData['money']);


                $dataPay['admin_id'] = $postData['id'];
                $dataPay['pay_type'] = 1;
                $dataPay['pay_orderid'] = get_order_sn();
                $dataPay['pay_money'] = $postData['money']; //金额
                $dataPay['pay_statu'] = 1; // 状态 1成功 2失败 3提交
                $dataPay['pay_msg'] = "管理员充值"; // 操作说明
                $dataPay['addtime'] = date("Y-m-d H:i:s");
                $dataPay['uptime'] = date("Y-m-d H:i:s");
                db('pay')->insert($dataPay);


                if ($bool) {
                    $this->result('', 1, '成功');
                } else {
                    $this->result('', -1, '失败');
                }
            } else {
                $this->result('', -1, '参数错误');
            }

        } else {

            $where['id'] = input('id');
            $where['super'] = 2;
            $user = db('admin')->where($where)->find();
            $this->assign($user);
            return $this->fetch();
        }
    }



    /****** 服务 *****/

    /**
     * Info:
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/9 22:18
     * Function:  serve
     */
    public function serve()
    {
        //缓存上次操作服务
        $cacheid = cache('user_serve_param_id') ? cache('user_serve_param_id') : 0;
        $id = input('id', $cacheid);
        $this->assign('user_id', $id);
        cache('user_serve_param_id', $id, 99999999);

        if ($this->request->post()) {
            $id = input('id', 0);
            $list = db('capacity')
                ->alias('c')
                ->join('jw_serve s', "s.id = c.serve_id")
                ->where('admin_id', $id)
                ->order('id', 'desc')
                ->field('c.*,serve_name,serve_code')
                ->select();
            return $this->tableList(0, '成功', $list, 0);
        }
        $admin = db('admin')
            ->where(['super' => 2, "is_del" => 1])
            ->field('id,username')
            ->select();
        $this->assign('admin', $admin);
        return $this->fetch();
    }

    /**
     * Info: 客户服务添加
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/10 15:55
     * Function:  serveadd
     */
    public function serveadd()
    {

        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'admin_id|用户ID' => 'require',
                'serve_id|服务ID' => 'require',
                'capacity_pay|付费方式' => 'require',
                'capacity_mark|计费标识' => 'require',
                'capacity_price|价格' => 'require',
                'capacity_num|次数' => 'require',
                'capacity_ip|IP白名单' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $inertData['admin_id'] = $postData['admin_id'];
            $inertData['serve_id'] = $postData['serve_id'];
            $inertData['capacity_pay'] = $postData['capacity_pay'];
            $inertData['capacity_mark'] = $postData['capacity_mark'];
            $inertData['capacity_price'] = $postData['capacity_price'];
            $inertData['capacity_num'] = $postData['capacity_num'];
            $inertData['capacity_ip'] = $postData['capacity_ip'];

            $inertData['addtime'] = date("Y-m-d H:i:s");
            $bool = db('capacity')
                ->cache("capacity_list")
                ->insert($inertData);
            if ($bool) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }

        }


        // 用户uidID
        $id = input('id', 0);
        $this->assign('USER_ID', $id);

        $username = db('admin')->where('id', $id)->cache(60)->value("username");
        $this->assign('username', $username);

        // 服务列表
        $serve = db('serve')->order('id', 'desc')->cache(60)->select();
        $this->assign('serve', $serve);


        return $this->fetch();
    }

    /**
     * Info: 客户服务修改
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/10 15:55
     * Function:  serveedit
     */
    public function serveedit()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'id' => 'require',
                'serve_id|服务ID' => 'require',
                'capacity_pay|付费方式' => 'require',
                'capacity_mark|计费标识' => 'require',
                'capacity_price|价格' => 'require',
                'capacity_num|次数' => 'require',
                'capacity_ip|IP白名单' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $UpData['serve_id'] = $postData['serve_id'];
            $UpData['capacity_pay'] = $postData['capacity_pay'];
            $UpData['capacity_mark'] = $postData['capacity_mark'];
            $UpData['capacity_price'] = $postData['capacity_price'];
            $UpData['capacity_num'] = $postData['capacity_num'];
            $UpData['capacity_ip'] = $postData['capacity_ip'];

            $bool = db('capacity')->where('id', $postData['id'])->update($UpData);
            if ($bool) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }

        }

        // 用户uidID
        $id = input('id', 0);

        $capacity = db("capacity")->where("id", $id)->find();
        $this->assign($capacity);

        $username = db('admin')->where('id', $capacity['admin_id'])->value("username");
        $this->assign('username', $username);

        // 服务列表
        $serve = db('serve')->order('id', 'desc')->select();
        $this->assign('serve', $serve);

        return $this->fetch();
    }

    /**
     * Info: 客户服务删除
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/10 15:55
     * Function:  servedel
     */
    public function servedel()
    {
        $where['id'] = input('id');
        $bool = db('capacity')->where($where)->delete();

        if ($bool) {
            $this->result('', 1, '成功');
        } else {
            $this->result('', -1, '失败');
        }
    }

    /****** 服务 *****/

    /**
     * Info: 充值记录
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/12 22:15
     * Function:  paylist
     */
    public function paylist()
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
                ->join('admin a', 'a.id = p.admin_id')
                ->limit($page, $limit)
                ->order('p.id', 'desc')
                ->field('p.*,account')
                ->select();
            $count = $table->where($where)->count();
            return $this->tableList(0, '成功', $paylist, $count);
        }
        return $this->fetch();
    }

    /**
     * Info: 充值状态
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/12 22:15
     * Function:  paystatu
     */
    public function paystatu()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'id|ID' => 'require',
                'pay_statu|订单状态' => 'require',
                'pay_msg|操作说明' => 'require'
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $UpData['pay_statu'] = $postData['pay_statu'];
            $UpData['pay_msg'] = $postData['pay_msg'];
            $UpData['uptime'] = date("Y-m-d H:i:s");

            if ($postData['pay_statu'] == 1) {

                $pay = db('pay')->where('id', $postData['id'])->find();
                if ($pay['pay_statu'] == 3) {
                    db("admin")->where('id', $pay['admin_id'])->setInc('balance', $pay['pay_money']);
                }
            }
            $bool = db('pay')->where('id', $postData['id'])->update($UpData);
            if ($bool) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }

        }

        // 单号
        $id = input('id', 0);
        $orderid = db('pay')->where('id', $id)->find();
        $this->assign("orderid", $orderid);
        return $this->fetch();
    }

    /****** 对账清单 *****/

    /**
     * Info: 消费清单
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/10 16:04
     * Function:  expenditure
     */
    public function expenditure()
    {
        if ($this->request->post()) {
            $postData = input('post.');
            $rule = [
                'dateTime|时间' => 'require',
                'user_id|客户' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }


            $countData = db('logs')
                ->where(['log_admin_id' => $postData['user_id']])
                ->whereTime('log_adddata', $postData['dateTime'])
                ->group("log_serve_id")
                ->field('log_serve_id')
                ->cache(30)
                ->order('log_admin_id', 'ase')
                ->select();


            $arr = [];
            foreach ($countData as $key => $val) {
                $arr[$key]['serve_name'] = db('serve')->where('id',
                    $val['log_serve_id'])->cache(30)->value('serve_name');
                $arr[$key]['serve_num'] = db('logs')->whereTime('log_adddata',
                    $postData['dateTime'])->where(['log_serve_id' => $val['log_serve_id'], 'log_admin_id' => $postData['user_id']])->count();
                $arr[$key]['serve_num_pirce'] = db('logs')->whereTime('log_adddata',
                    $postData['dateTime'])->where(['log_serve_id' => $val['log_serve_id'], 'log_admin_id' => $postData['user_id'], 'log_type' => 1])->count();
                $arr[$key]['serve_pirce'] = db('logs')->whereTime('log_adddata',
                    $postData['dateTime'])->where(['log_serve_id' => $val['log_serve_id'], 'log_admin_id' => $postData['user_id'], 'log_type' => 1])->sum('log_price');
            }
            $count = count($arr);
            $arr[$count]['serve_name'] = "合计";
            $arr[$count]['serve_num'] = db('logs')
                ->where(['log_admin_id' => $postData['user_id']])
                ->whereTime('log_adddata', $postData['dateTime'])
                ->count();
            $arr[$count]['serve_num_pirce'] = db('logs')
                ->where(['log_admin_id' => $postData['user_id']])
                ->whereTime('log_adddata', $postData['dateTime'])
                ->where(['log_type' => 1])
                ->count();
            $arr[$count]['serve_pirce'] = db('logs')
                ->where(['log_admin_id' => $postData['user_id']])
                ->whereTime('log_adddata', $postData['dateTime'])
                ->where(['log_type' => 1])
                ->sum('log_price');


            return $this->tableList(0, '成功', $arr, 0);
        }
        //客户
        $admin = db('admin')->where('super', 2)->field('id,username')->select();
        $this->assign('admin', $admin);
        return $this->fetch();
    }

    /****** 对账清单 *****/

}