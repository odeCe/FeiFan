<?php


namespace app\api\controller;


use app\jwcode\controller\ApiServe;

class Servetoken extends ApiServe
{
    /**
     * Info: 获取token
     * Argument :
     * User: 伍先生
     * DateTime: 2020/9/16 18:04
     * Url: api/ServeToken/getToken
     * Requ: {"Account": "testcode","Password": "testcode"}
     * Resp: {"code":1000,"msg":"成功","time":1600250629,"data":{"Token":"29738749A202B6D108CEECADE86083F1"}}
     */
    public function getToken()
    {
        if ($this->request->isPost()) {

            $postData = input('post.');
            $rule = [
                'Account|账号' => 'require',
                'Password|用户名' => 'require',
            ];
            $validate = new \think\Validate;
            $validate->rule($rule);

            if (!$validate->check($postData)) {
                $this->ReturnInfo(1001);
            }

            $datatimenum = date("Ymd") . $postData['Account'];
            if (cache($datatimenum)) {
                $num = cache($datatimenum) + 1;
                cache($datatimenum, $num, 86400);
                if ($num > 50) $this->ReturnInfo(1007);
            } else {
                cache($datatimenum, 1, 86400);
            }

            $where['account'] = $postData['Account'];
            $where['super'] = 2;
            $where['is_del'] = 1;
            $admin = db("admin")->where($where)->cache(60)->find();
            $pass = set_password($postData['Password']);
            if ($pass == $admin['password']) {
                $token = strtoupper(md5(time() . $pass . rand(9999, 1111)));
                cache($token, $admin, 3600);
                $this->ReturnInfo(1000, ['Token' => $token]);
            } else {
                $this->ReturnInfo(1005);
            }
        } else {
            $this->ReturnInfo(1002);
        }
    }
}