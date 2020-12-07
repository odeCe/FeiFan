<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\facade\Log;

// 应用公共文件

/**
 * Info: 判断是否蜘蛛访问
 * Argument : 返回访问头
 * User: 伍先生
 * Date: 2019/8/29
 * Time: 9:00
 */
function isCrawler()
{
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if (!empty($agent)) {
        $spiderSite = array(
            "TencentTraveler",
            "Baiduspider+",
            "BaiduGame",
            "Googlebot",
            "msnbot",
            "Sosospider+",
            "Sogou web spider",
            "ia_archiver",
            "Yahoo! Slurp",
            "YoudaoBot",
            "Yahoo Slurp",
            "MSNBot",
            "Java (Often spam bot)",
            "BaiDuSpider",
            "Voila",
            "Yandex bot",
            "BSpider",
            "twiceler",
            "Sogou Spider",
            "Speedy Spider",
            "Google AdSense",
            "Heritrix",
            "Python-urllib",
            "Alexa (IA Archiver)",
            "Ask",
            "Exabot",
            "Custo",
            "OutfoxBot/YodaoBot",
            "yacy",
            "SurveyBot",
            "legs",
            "lwp-trivial",
            "Nutch",
            "StackRambler",
            "The web archive (IA Archiver)",
            "Perl tool",
            "MJ12bot",
            "Netcraft",
            "MSIECrawler",
            "WGet tools",
            "larbin",
            "Fish search",
        );

        foreach ($spiderSite as $val) {
            $str = strtolower($val);
            if (strpos($agent, $str) !== false) {
                return $agent;
            }
        }
    } else {
        return false;
    }
}

/**
 * Info: 返回带协议的域名
 * Argument :
 * User: 伍先生
 * Date: 2019/8/29
 * Time: 9:03
 */
function get_domain()
{
    return request()->domain();
}


/**
 * Info: 密码加密方法
 * Argument :  要加密的字符   盐
 * User: 伍先生
 * Date: 2019/8/29
 * Time: 9:05
 */
function set_password($pw, $authCode = '')
{
    if (empty($authCode)) {
        $authCode = config('database.salt');
    }
    $result = "jw" . md5(md5($authCode . $pw));
    return $result;
}


/**
 * Info: 随机字符串生成
 * Argument :
 * User: 伍先生
 * Date: 2019/8/29
 * Time: 9:09
 */
function random_string($len = 6)
{
    $chars = [
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
        "3", "4", "5", "6", "7", "8", "9"
    ];
    $charsLen = count($chars) - 1;
    shuffle($chars);    // 将数组打乱
    $output = "";
    for ($i = 0; $i < $len; $i++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    return $output;
}


/**
 * Info: 保存数组变量到php文件  用于修改配置文件
 * Argument :boolean 保存成功返回true,否则false
 * User: 伍先生
 * Date: 2019/8/29
 * Time: 9:14
 */
function set_save_var($path, $key, $val)
{
    $result = file_put_contents($path, "<?php\treturn Route::alias('" . $key . "','" . $val . "'); ;?>");
    return $result;
}

/**
 * Info: 判断是否为手机访问
 * Argument :
 * User: 伍先生
 * Date: 2019/8/29
 * Time: 22:16
 */
function is_mobile()
{

    $mobile = request()->isMobile();

    return $mobile;
}

/**
 * Info: 判断是否为微信访问
 * Argument :
 * User: 伍先生
 * Date: 2019/8/29
 * Time: 22:20
 */
function is_wechat()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }
    return false;
}

/**
 * Info: 获取惟一订单号
 * Argument :
 * User: 伍先生
 * Date: 2019/8/29
 * Time: 22:22
 */
function get_order_sn()
{
    $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
    $orderSn = $yCode[intval(date('Y')) - 2019] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    return $orderSn;
}

function get_random_seed()
{
    $aa = base_convert(microtime() . rand(0, 9999999), 10, 16);
    $bb = substr(strtoupper(md5(substr($aa, 0, 16))), 16);
    $cc = date("Ymd") . $bb;
    return $cc;
}

function get_msectime()
{
    return floor(microtime(true) * 1000);
}

/**
 * Info: 获取客户端IP地址
 * Argument :
 * User: 伍先生
 * Date: 2019/8/29
 * Time: 22:29
 */
function get_client_ip($type = 0, $adv = true)
{
    return request()->ip($type, $adv);
}

/**
 * Info: 检查手机格式，中国手机不带国家代码，国际手机号格式为：国家代码-手机号
 * Argument :
 * User: 伍先生
 * Date: 2019/8/29
 * Time: 22:32
 */
function cmf_check_mobile($mobile)
{
    if (preg_match('/(^(13\d|14\d|15\d|16\d|17\d|18\d|19\d)\d{8})$/', $mobile)) {
        return true;
    } else {
        if (preg_match('/^\d{1,4}-\d{5,11}$/', $mobile)) {
            if (preg_match('/^\d{1,4}-0+/', $mobile)) {
                //不能以0开头
                return false;
            }

            return true;
        }

        return false;
    }
}

/**
 * 方法库-数组去除空值
 * @param string $num 数值
 * @return string
 */
function array_remove_empty($arr)
{
    if (!is_array($arr)) return false;
    foreach ($arr as $k => $v) {
        if (!$v)
            unset($arr[$k]);
    }
    return $arr;
}

/**
 * Info: 判断账号是否存在
 * Argument :
 * User: 伍先生
 * Date: 2019/9/1
 * Time: 16:48
 */
function account_heavy($account)
{
    $bool = db('admin')->where('account', $account)->find();
    if ($bool) {
        return true;
    } else {
        return false;
    }
}

/**
 * Info: 权限展示
 * Argument :
 * User: 伍先生
 * Date: 2019/9/5
 * Time: 23:31
 */
function authority()
{
    $data = db('authority')->where('grade', 0)->field('id,name')->select();

    foreach ($data as $k => $v) {
        $data[$k]['list'] = db('authority')->where('grade', $v['id'])->field('id,name')->select();
    }

    return $data;

}


/**
 * Info: 字符转数组 在判断 某个字符在不在当前数组
 * Argument :
 * User: 伍先生
 * Date: 2019/9/5
 * Time: 20:30
 */
function inArray($msg, $v)
{
    $arr = explode(",", $msg);
    if (in_array($v, $arr)) {
        return 1;
    }
    return 0;
}

function catalogueall()
{
    $authority = db('authority')->distinct(true)->field('grade')->order('sort', 'desc')->cache(30)->select();
    foreach ($authority as $k => $v) {

        $grade = db('authority')->where(['id' => $v['grade'], "display" => 1])->order('sort', 'desc')->cache(30)->find();

        if ($grade) {
            if ($grade['id'] == 6) {
                unset($authority[$k]);
                continue;
            }
            $authority[$k]['name'] = $grade['name'];
            $authority[$k]['sort'] = $grade['sort'];
            $authority[$k]['icon'] = $grade['icon'];
            $authority[$k]['list'] = db('authority')->where(['grade' => $v['grade'], "display" => 1])->order('sort', 'desc')->cache(30)->select();
        } else {
            unset($authority[$k]);
        }
    }
    //排序
    array_multisort(array_column($authority, 'sort'), SORT_DESC, $authority);

    return $authority;
}


/**
 * Info: 根据角色展示目录
 * Argument :
 * User: 伍先生
 * Date: 2019/9/5
 * Time: 23:31
 */
function catalogue($id)
{
    $role = db('role')->where('id', $id)->find();
    $arr = explode(",", $role['role']);
    $where['id'] = $arr;
    $authority = db('authority')->where($where)->distinct(true)->field('grade')->order('sort', 'desc')->select();
    //echo "<pre>";
    //var_dump($authority);die;

    foreach ($authority as $k => $v) {
        $grade = db('authority')->where(['id' => $v['grade'], "display" => 1])->order('sort', 'desc')->find();
        if ($grade) {
            $authority[$k]['name'] = $grade['name'];
            $authority[$k]['sort'] = $grade['sort'];
            $authority[$k]['icon'] = $grade['icon'];
            $authority[$k]['list'] = db('authority')->where($where)->where(['grade' => $v['grade'], "display" => 1])->order('sort', 'desc')->select();
        } else {
            unset($authority[$k]);
        }
    }
    //排序
    array_multisort(array_column($authority, 'sort'), SORT_DESC, $authority);

    return $authority;
}


/**
 * Info: 设置session
 * Argument :
 * User: 伍先生
 * Date: 2019/9/7
 * Time: 0:59
 */
function set_session()
{
    //cookie
    $cookie = cookie('admin');

    $session = session('admin');
    //IP
    //$ip = get_client_ip();

    $account = json_encode(session('account'));

    $md5 = set_password(md5($cookie . $session . $account));

    return $md5;

}

/**
 * Info: 判断当前用户是否在线
 * Argument :
 * User: 伍先生
 * Date: 2019/9/7
 * Time: 1:02
 */
function is_login()
{


    $session = session('admin');
    $cookie = cookie('admin');
    if (!$session || !$cookie) {
        return false;
    }

    $time = db('session')->where('val', set_session())->value('deltime');


    if ($time > time()) {
        return true;
    } else {
        return false;
    }
}

/**
 * Info: 登录验证邮件
 * Argument :
 * User: 伍先生
 * Date: 2019/9/8
 * Time: 21:37
 */
function email()
{

}

/**
 * info: 日志保存
 * Created by 伍先生<qq:3383600886>
 * Date: 2019/12/7
 * Time: 14:23
 */
function seveLog($file, $mag)
{
    if (is_array($mag)) {
        $mag = json_encode($mag);
    }
    $data = date("Ym");
    $d = date("d");
    file_put_contents("../runtime/log/$data/$d.$file.log", date("Y-m-d H:i:s") . " | " . $mag . PHP_EOL . PHP_EOL, FILE_APPEND);
}


//文件导出
function csvsend($dataArr, $head, $title)
{
    ini_set('memory_limit', '1024M'); //设置程序运行的内存
    ini_set('max_execution_time', 0); //设置程序的执行时间,0为无上限
    @ob_end_clean();
    ob_start();
    //设置表名
    @header('Content-Type: application/vnd.ms-excel');   //header设置
    @header("Content-Disposition: attachment;filename=" . $title . ".csv");
    @header('Cache-Control: max-age=0');

    //打开php文件句柄，php://output表示直接输出到PHP缓存,a表示将输出的内容追加到文件末尾
    $fp = fopen('php://output', 'w');

    //设置表头
    foreach ($head as $k => $v) {
        $ssv = $v . "\t";
        $head[$k] = iconv("UTF-8", "GBK//IGNORE", $ssv);    //将utf-8编码转为gbk。理由是： Excel 以 ANSI 格式打开，不会做编码识别。如果直接用 Excel 打开 UTF-8 编码的 CSV 文件会导致汉字部分出现乱码。
    }

    fputcsv($fp, $head);  //fputcsv() 函数将行格式$head化为 CSV 并写入一个打开的文件$fp。

    foreach ($dataArr as $key => $data) {
        foreach ($data as $i => $item) {  //$item为一维数组哦
            $ss = $item . "\t";
            $data[$i] = iconv("UTF-8", "GBK//IGNORE", $ss);  //转为gbk的时候可能会遇到特殊字符‘-’之类的会报错，加 ignore表示这个特殊字符直接忽略不做转换。
        }
        fputcsv($fp, $data);
    }
    fclose($fp);
    @ob_flush();
    flush();
    ob_end_clean();
    return;
}

//读取cvs
function inputCsv($handle)
{
    $out = array();
    $n = 0;
    while ($data = fgetcsv($handle, 10000)) {
        $num = count($data);
        for ($i = 0; $i < $num; $i++) {
            if (empty(trim($data[$i]))){
                continue;
            }
            $out[$n][$i] = $data[$i];
        }
        $n++;
    }
    return $out;
}

//钉钉通知
function didi($message = '', $phone = array())
{
    //用法 $phone 必须为数组
    //dingding("测试@15989211480\r请忽略", array('15989211480', '123456789'));//可以多个电话
    if (!$message) return '消息不能为空';

    $message = "【通知】\n 域名: " . request()->domain() . "\n 内容: " . $message;

    $ch = curl_init();
    $data = array('msgtype' => 'text', 'text' => array('content' => $message));
    if ($phone && is_array($phone)) {
        $data['at'] = array('atMobiles' => $phone, 'isAtAll' => false);
    }
    $post_string = json_encode($data);
    $remote_server = 'https://oapi.dingtalk.com/robot/send?access_token=3872954748942421f07532911a32a1b58ddc665111d92abc875199ff9c44f0cb';
    curl_setopt($ch, CURLOPT_URL, $remote_server);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
    // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}




