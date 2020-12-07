<?php
/**
 * User: 伍先生
 * DateTime: 2020/8/30 13:49
 * Class:  Serve
 * Info: 服务管理
 */

namespace app\admin\controller;


use app\jwcode\controller\AdminBasic;

class Serve extends AdminBasic
{

    /* 服务  */

    /**
     * Info: 服务信息
     * Argument :
     * User: 伍先生
     * DateTime: 2020/8/30 13:51
     * Function:  infos
     */
    public function infos()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $page = 0;
            $limit = 10;
            $where = [];
            if (input('?page') && input('?limit')) {
                $page = ($postData['page'] - 1) * 10;
                $limit = $postData['limit'];
            }

            $order = ['id' => 'desc', 'addtime' => 'desc'];
            if (input('?field') && input('?order')) {
                $order = [$postData['field'] => $postData['order']];
            }
            $userlist = db('serve');

            if (input('?username') && !empty($postData['username'])) {
                $userlist->where('serve_name|serve_code|serve_site|serve_mark', 'like',
                    '%' . $postData['username'] . '%');
            }

            $userlist = $userlist->where($where)->limit($page, $limit)->order($order)->select();

            $count = db('serve')->where($where)->count();
            return $this->tableList(0, '成功', $userlist, $count);
        }
        return $this->fetch();
    }

    /**
     * Info: 添加服务信息
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/3 11:43
     * Function:  addinfos
     */
    public function infosadd()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'serve_name|服务名称' => 'require',
                'serve_code|服务代号' => 'require',
                'serve_site|服务地址' => 'require',
                'serve_statu|服务状态' => 'require',
                'serve_mark|服务标示' => 'require',
                'serve_type|服务类型' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $inertData['serve_name'] = $postData['serve_name'];
            $inertData['serve_code'] = ($postData['serve_code']);
            $inertData['serve_site'] = $postData['serve_site'];
            $inertData['serve_statu'] = $postData['serve_statu'];
            $inertData['serve_mark'] = $postData['serve_mark'];
            $inertData['serve_type'] = $postData['serve_type'];


            $inertData['addtime'] = date("Y-m-d H:i:s");

            $bool = db('serve')->insert($inertData);

            if ($bool) {
                $this->result('', 1, '添加成功', 'json');
            } else {
                $this->result('', -1, '添加失败', 'json');
            }

        }
        return $this->fetch();
    }

    /**
     * Info: 修改服务信息
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/3 11:44
     * Function:  editinfos
     */
    public function infosedit()
    {
        if ($this->request->post()) {
            $postData = input('post.');
            $rule = [
                'id' => 'require',
                'serve_name|服务名称' => 'require',
                'serve_code|服务代号' => 'require',
                'serve_site|服务地址' => 'require',
                'serve_statu|服务状态' => 'require',
                'serve_mark|服务标示' => 'require',
                'serve_type|服务类型' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $updataData['serve_name'] = $postData['serve_name'];
            $updataData['serve_code'] = ($postData['serve_code']);
            $updataData['serve_site'] = $postData['serve_site'];
            $updataData['serve_statu'] = $postData['serve_statu'];
            $updataData['serve_mark'] = $postData['serve_mark'];
            $updataData['serve_type'] = $postData['serve_type'];

            $bool = db('serve')->where('id', $postData['id'])->update($updataData);

            if ($bool) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }
        }
        $id = input('id', 0);
        $serve = db('serve')->where('id', $id)->find();
        $this->assign($serve);
        return $this->fetch();
    }

    /**
     * Info: 状态修改
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/3 21:19
     * Function:  infosstatu
     */
    public function infosstatu()
    {
        $postData = input('post.');
        if (input('?id') && !empty($postData['id'])) {
            db('serve')->where(['id' => $postData['id']])->update(['serve_statu' => $postData['state']]);
            $this->result('', 1, '成功');
        }
        $this->result('', -1, '参数错误');
    }

    /**
     * Info: 服务删除
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/3 22:10
     * Function:  infossdelete
     */
    public function infossdelete()
    {
        $postData = input('post.');
        if (input('?id') && !empty($postData['id'])) {
            db('serve')->where(['id' => $postData['id']])->delete();
            $this->result('', 1, '成功');
        }
        $this->result('', -1, '参数错误');
    }

    /* 服务  */


    /* 参数 */

    /**
     * Info: 参数信息
     * Argument :
     * User: 伍先生
     * DateTime: 2020/8/30 13:53
     * Function:  param
     */
    public function param()
    {
        //缓存上次操作服务
        $cacheid = cache('param_id') ? cache('param_id') : 0;
        $id = input('id', $cacheid);
        $this->assign('serveid', $id);
        cache('param_id', $id, 99999999);

        if ($this->request->post()) {
            $id = input('id', 0);
            $list = db('param')->where('serve_id', $id)->order('id', 'desc')->select();
            return $this->tableList(0, '成功', $list, 0);
        }
        $serve = db('serve')->order('id', 'desc')->select();
        $this->assign('serve', $serve);
        return $this->fetch();
    }

    /**
     * Info: 参数添加
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/3 23:16
     * Function:  paramadd
     */
    public function paramadd()
    {

        $id = input('id', 0);
        $this->assign('serveid', $id);

        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'serveid|服务id' => 'require',
                'param_name|参数名称' => 'require',
                'param_code|参数码' => 'require',
                'param_statu|是否必填' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $inertData['serve_id'] = $postData['serveid'];
            $inertData['param_name'] = $postData['param_name'];
            $inertData['param_code'] = $postData['param_code'];
            $inertData['param_statu'] = $postData['param_statu'];

            $inertData['addtime'] = date("Y-m-d H:i:s");
            $bool = db('param')->insert($inertData);
            if ($bool) {
                $this->result('', 1, '添加成功', 'json');
            } else {
                $this->result('', -1, '添加失败', 'json');
            }

        }
        return $this->fetch();
    }

    /**
     * Info: 参数修改
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/3 23:17
     * Function:  paramedit
     */
    public function paramedit()
    {

        $id = input('id', 0);
        if ($this->request->post()) {
            $postData = input('post.');
            $rule = [
                'id' => 'require',
                'param_name|参数名称' => 'require',
                'param_code|参数码' => 'require',
                'param_statu|是否必填' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $updataData['param_name'] = $postData['param_name'];
            $updataData['param_code'] = $postData['param_code'];
            $updataData['param_statu'] = $postData['param_statu'];

            $bool = db('param')->where('id', $postData['id'])->update($updataData);

            if ($bool) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }
        }
        $id = input('id', 0);
        $data = db('param')->where('id', $id)->find();
        $this->assign($data);
        return $this->fetch();
    }

    /**
     * Info: 参数是否必填
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/3 23:17
     * Function:  paramstatu
     */
    public function paramstatu()
    {
        $postData = input('post.');
        if (input('?id') && !empty($postData['id'])) {
            db('param')->where(['id' => $postData['id']])->update(['param_statu' => $postData['state']]);
            $this->result('', 1, '成功');
        }
        $this->result('', -1, '参数错误');
    }

    /**
     * Info:参数删除
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/3 23:17
     * Function:  paramdelete
     */
    public function paramdelete()
    {
        $postData = input('post.');
        if (input('?id') && !empty($postData['id'])) {
            db('param')->where(['id' => $postData['id']])->delete();
            $this->result('', 1, '成功');
        }
        $this->result('', -1, '参数错误');
    }

    /* 参数 */
    /* 脱敏信息 */

    /**
     * Info: 脱敏信息
     * Argument :
     * User: 伍先生
     * DateTime: 2020/8/30 13:53
     * Function:  desensitize
     */
    public function desensitize()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $page = 0;
            $limit = 10;
            if (input('?page') && input('?limit')) {
                $page = ($postData['page'] - 1) * 10;
                $limit = $postData['limit'];
            }

            $order = ['id' => 'desc', 'addtime' => 'desc'];
            if (input('?field') && input('?order')) {
                $order = [$postData['field'] => $postData['order']];
            }
            $table = db('desensitize');
            $userlist = $table->limit($page, $limit)->order($order)->select();

            $count = $table->count();
            return $this->tableList(0, '成功', $userlist, $count);
        }
        return $this->fetch();
    }

    /**
     * Info: 脱敏信息添加
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/6 13:54
     * Function:  desensitizeadd
     */
    public function desensitizeadd()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'dese_name|规则名称' => 'require',
                'dese_text|规则内容' => 'require'
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $inertData['dese_name'] = $postData['dese_name'];
            $inertData['dese_text'] = $postData['dese_text'];

            $inertData['addtime'] = date("Y-m-d H:i:s");

            $bool = db('desensitize')->insert($inertData);

            if ($bool) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }

        }
        return $this->fetch();
    }

    /**
     * Info: 脱敏信息修改
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/6 13:55
     * Function:  desensitizeedit
     */
    public function desensitizeedit()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'id' => 'require',
                'dese_name|规则名称' => 'require',
                'dese_text|规则内容' => 'require'
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $updateData['dese_name'] = $postData['dese_name'];
            $updateData['dese_text'] = $postData['dese_text'];

            $bool = db('desensitize')->where('id', $postData['id'])->update($updateData);

            if ($bool) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }

        }
        $id = input('id', 0);
        $data = db('desensitize')->where('id', $id)->find();
        $this->assign($data);

        return $this->fetch();
    }

    /**
     * Info:脱敏信息删除
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/3 23:17
     * Function:  paramdelete
     */
    public function desensitizedel()
    {
        $postData = input('post.');
        if (input('?id') && !empty($postData['id'])) {
            db('desensitize')->where(['id' => $postData['id']])->delete();
            $this->result('', 1, '成功');
        }
        $this->result('', -1, '参数错误');
    }


    /* 脱敏信息 */
    /* 上游厂商 */




    /**
     * Info: 上游厂商
     * Argument :
     * User: 伍先生
     * DateTime: 2020/8/30 13:54
     * Function:  firm
     */
    public function firm()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $page = 0;
            $limit = 10;
            if (input('?page') && input('?limit')) {
                $page = ($postData['page'] - 1) * 10;
                $limit = $postData['limit'];
            }

            $order = ['id' => 'desc', 'addtime' => 'desc'];
            if (input('?field') && input('?order')) {
                $order = [$postData['field'] => $postData['order']];
            }
            $table = db('firm');
            $userlist = $table->limit($page, $limit)->order($order)->select();

            $count = $table->count();
            return $this->tableList(0, '成功', $userlist, $count);
        }
        return $this->fetch();
    }

    /**
     * Info: 厂商添加
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/6 14:41
     * Function:  firmadd
     */
    public function firmadd()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'firm_title|厂商名称' => 'require',
                'firm_account|账号' => 'require',
                'firm_pass|密码' => 'require',
                'firm_email|邮箱' => 'require',
                'firm_username|联系人' => 'require',
                'firm_mobile|手机' => 'require',
                'firm_mark|备注' => 'require',
                'firm_statu|启用' => 'require',
                'firm_code|代码' => 'require'
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $inertData['firm_title'] = $postData['firm_title'];
            $inertData['firm_account'] = $postData['firm_account'];
            $inertData['firm_pass'] = $postData['firm_pass'];
            $inertData['firm_email'] = $postData['firm_email'];
            $inertData['firm_username'] = $postData['firm_username'];
            $inertData['firm_mobile'] = $postData['firm_mobile'];
            $inertData['firm_mark'] = $postData['firm_mark'];
            $inertData['firm_statu'] = $postData['firm_statu'];
            $inertData['firm_code'] = $postData['firm_code'];

            $inertData['addtime'] = date("Y-m-d H:i:s");

            $bool = db('firm')->insert($inertData);

            if ($bool) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }

        }
        return $this->fetch();
    }

    /**
     * Info: 厂商修改
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/6 14:41
     * Function:  firmedit
     */
    public function firmedit()
    {
        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'id' => 'require',
                'firm_title|厂商名称' => 'require',
                'firm_account|账号' => 'require',
                'firm_pass|密码' => 'require',
                'firm_email|邮箱' => 'require',
                'firm_username|联系人' => 'require',
                'firm_mobile|手机' => 'require',
                'firm_mark|备注' => 'require',
                'firm_statu|启用' => 'require',
                'firm_code|代码' => 'require'
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $updateData['firm_title'] = $postData['firm_title'];
            $updateData['firm_account'] = $postData['firm_account'];
            $updateData['firm_pass'] = $postData['firm_pass'];
            $updateData['firm_email'] = $postData['firm_email'];
            $updateData['firm_username'] = $postData['firm_username'];
            $updateData['firm_mobile'] = $postData['firm_mobile'];
            $updateData['firm_mark'] = $postData['firm_mark'];
            $updateData['firm_statu'] = $postData['firm_statu'];
            $updateData['firm_code'] = $postData['firm_code'];

            $bool = db('firm')->where('id', $postData['id'])->update($updateData);

            if ($bool) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }

        }
        $id = input('id', 0);
        $data = db('firm')->where('id', $id)->find();
        $this->assign($data);

        return $this->fetch();
    }

    /**
     * Info: 厂商状态
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/6 14:41
     * Function:  firmstatu
     */
    public function firmstatu()
    {
        $postData = input('post.');
        if (input('?id') && !empty($postData['id'])) {
            db('firm')->where(['id' => $postData['id']])->update(['firm_statu' => $postData['state']]);
            $this->result('', 1, '成功');
        }
        $this->result('', -1, '参数错误');
    }

    /**
     * Info: 优先级
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/7 19:02
     * Function:  priority
     */
    public function priority(){
        $postData = input('post.');
        if (input('?id') && !empty($postData['id'])) {
            db('firm')->where(['id' => $postData['id']])->update(['firm_priority' => $postData['num']]);
            $this->result('', 1, '成功');
        }
        $this->result('', -1, '参数错误');
    }

    /**
     * Info: 厂商删除
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/6 14:41
     * Function:  firmdel
     */
    public function firmdel()
    {
        $postData = input('post.');
        if (input('?id') && !empty($postData['id'])) {
            db('firm')->where(['id' => $postData['id']])->delete();
            $this->result('', 1, '成功');
        }
        $this->result('', -1, '参数错误');
    }



    /* 上游厂商 */
    /* 上游服务 */


    /**
     * Info: 上游服务
     * Argument :
     * User: 伍先生
     * DateTime: 2020/8/30 13:55
     * Function:  service
     */
    public function service()
    {
        //缓存上次操作服务
        $cacheid = cache('firm_param_id') ? cache('firm_param_id') : 0;
        $id = input('id', $cacheid);
        $this->assign('firmid', $id);
        cache('firm_param_id', $id, 99999999);

        if ($this->request->post()) {
            $id = input('id', 0);
            $list = db('service')->where('firm_id', $id)->order('id', 'desc')->select();
            return $this->tableList(0, '成功', $list, 0);
        }
        $data = db('firm')->order('id', 'desc')->select();
        $this->assign('firm', $data);
        return $this->fetch();
    }

    public function serviceadd(){
        $id = input('id', 0);
        $this->assign('firmid', $id);

        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'firmid|厂商ID' => 'require',
                'service_name|服务名称' => 'require',
                'service_code|服务码' => 'require',
                'service_mark|计费标识' => 'require',
                'service_statu|服务状态' => 'require',
                'service_price|服务价格' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $inertData['firm_id'] = $postData['firmid'];
            $inertData['service_name'] = $postData['service_name'];
            $inertData['service_code'] = $postData['service_code'];
            $inertData['service_mark'] = $postData['service_mark'];
            $inertData['service_statu'] = $postData['service_statu'];
            $inertData['service_price'] = $postData['service_price'];

            $inertData['addtime'] = date("Y-m-d H:i:s");
            $bool = db('service')->insert($inertData);
            if ($bool) {
                $this->result('', 1, '添加成功', 'json');
            } else {
                $this->result('', -1, '添加失败', 'json');
            }

        }
        return $this->fetch();
    }

    public function serviceedit(){

        if ($this->request->post()) {
            $postData = input('post.');

            $rule = [
                'id' => 'require',
                'service_name|服务名称' => 'require',
                'service_code|服务码' => 'require',
                'service_mark|计费标识' => 'require',
                'service_statu|服务状态' => 'require',
                'service_price|服务价格' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->result('', -1, $validate->getError(), "json");
            }

            $updateData['service_name'] = $postData['service_name'];
            $updateData['service_code'] = $postData['service_code'];
            $updateData['service_mark'] = $postData['service_mark'];
            $updateData['service_statu'] = $postData['service_statu'];
            $updateData['service_price'] = $postData['service_price'];

            $bool = db('service')->where('id', $postData['id'])->update($updateData);

            if ($bool) {
                $this->result('', 1, '成功', 'json');
            } else {
                $this->result('', -1, '失败', 'json');
            }

        }
        $id = input('id', 0);
        $data = db('service')->where('id', $id)->find();
        $this->assign($data);

        return $this->fetch();
    }

    public function servicestatu(){
        $postData = input('post.');
        if (input('?id') && !empty($postData['id'])) {
            db('service')->where(['id' => $postData['id']])->update(['service_statu' => $postData['state']]);
            $this->result('', 1, '成功');
        }
        $this->result('', -1, '参数错误');
    }

    public function servicedel(){
        $postData = input('post.');
        if (input('?id') && !empty($postData['id'])) {
            db('service')->where(['id' => $postData['id']])->delete();
            $this->result('', 1, '成功');
        }
        $this->result('', -1, '参数错误');
    }


    /* 上游服务 */
    /* 优先级 */

    public function priorityadd()
    {
        if ($this->request->post()) {

        }
        echo "优先级";
        return $this->fetch();
    }



    /* 优先级 */
}