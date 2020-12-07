<?php
/**
 * User: 伍先生
 * DateTime: 2020/12/315:51
 * Class:  Desensitization
 * Info: API测试管理
 */

namespace app\common\tool;


use think\App;
use think\Controller;

class Desensitization extends Controller
{

    protected function initialize()
    {
        $this->des();
    }

    protected $field = [];
    protected $rule = [];

    public function selectName($arr)
    {

        if (!is_array($arr)) {
            return $arr;
        }
        foreach ($arr as $key => $val) {
            if(is_array($val)){
                $arr[$key] = self::selectName($val);
            }
            if (in_array($key, $this->field) && $key && $val) {
                $rule = $this->rule[$key];
                $arr[$key] = $this->desensitize($val, $rule[1]-1, $rule[2], $rule[3]);
            }
        }

        return $arr;
    }

    public function desensitize($string, $start = 0, $length = 0, $re = '*')
    {
        if (empty($string) || empty($length) || empty($re)) return $string;
        $end = $start + $length;
        $strlen = mb_strlen($string);
        $str_arr = array();
        for ($i = 0; $i < $strlen; $i++) {
            if ($i >= $start && $i < $end)
                $str_arr[] = $re;
            else
                $str_arr[] = mb_substr($string, $i, 1);
        }
        return implode('', $str_arr);
    }

    //查数据库看看那些字段需要脱敏
    public function des()
    {
        $data = db("desensitize")->field('dese_text')->cache(60)->select();
        foreach ($data as $value) {
            $arr = json_decode($value['dese_text']);
            if (is_array($arr) && count($arr)) {
                array_push($this->field, $arr[0]);
                $this->rule[$arr[0]] = $arr;
            }
        }
    }
}