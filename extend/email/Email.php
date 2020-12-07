<?php
/**
 * Created by PhpStorm.
 * User: 伍先生
 * QQ  : 3383600886
 * Date: 2019/9/8
 * Time: 18:30
 * Info: 说明
 */

namespace email;

use email\SMTP;
use email\PHPMailer;

class Email extends PHPMailer
{
    public function __construct($exceptions = null)
    {
        parent::__construct($exceptions);
        
        $this->isSMTP();
    }

    /**
     * Info: XX
     * Argument : $toemail 收件人邮箱 $name 收件人名称 $title 邮件标题 $content 邮件内容
     *            $host 服务器地址  account 发件账号  pass 发件密码 launch 发件人
     * User: 伍先生
     * Date: 2019/9/8
     * Time: 18:44
     */
    public function emailSend($account,$pass,$toemail,$host,$launch='渐悟代码',$name='尊敬的用户',$title='渐悟代码标题',$content='渐悟代码内容'){
        $this->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
        $this->Host = $host;// 发送方的SMTP服务器地址
        $this->SMTPAuth = true;// 是否使用身份验证
        $this->Username = $account;/// 发送方的163邮箱用户名，就是你申请163的SMTP服务使用的163邮箱
        $this->Password = $pass;// 发送方的邮箱密码，注意用163邮箱这里填写的是“客户端授权密码”而不是邮箱的登录密码！
        $this->SMTPSecure = "ssl";// 使用ssl协议方式
        $this->Port = 465;// 163邮箱的ssl协议方式端口号是465/994

        $this->setFrom($account,$launch);
        $this->addAddress($toemail,$name);

        $this->Subject = $title;  //邮件标题
        $this->Body = $content;// 邮件正文

        if($this->send()){// 发送邮件
            return true;
            // echo "Message could not be sent.";
        }else{
            return false;
            // echo "Mailer Error: ".$mail->ErrorInfo;// 输出错误信息
        }
     }
}