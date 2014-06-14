<?php

define('MEMORY_LIMIT_ON', false);

foreach ($_config as $k => $v) {
    C($k, $v);
}

function trace($value = '[think]', $label = '', $level = 'DEBUG', $record = false)
{
    static $_trace =  array();
    if ('[think]' === $value) { // 获取trace信息
        return $_trace;
    } else {
        $info   =   ($label?$label.':':'').print_r($value, true);
        $level  =   strtoupper($level);
        
        if ((defined('IS_AJAX') && IS_AJAX) || !C('SHOW_PAGE_TRACE')  || $record) {
            //Log::record($info,$level,$record);
        } else {
            if (!isset($_trace[$level]) || count($_trace[$level])>C('TRACE_MAX_RECORD')) {
                $_trace[$level] =   array();
            }
            $_trace[$level][]   =   $info;
        }
    }
}
function G($start, $end = '', $dec = 4)
{
    static $_info       =   array();
    static $_mem        =   array();
    if (is_float($end)) { // 记录时间
        $_info[$start]  =   $end;
    } elseif (!empty($end)) { // 统计时间和内存使用
        if (!isset($_info[$end])) {
            $_info[$end]       =  microtime(true);
        }
        if (MEMORY_LIMIT_ON && $dec=='m') {
            if (!isset($_mem[$end])) {
                $_mem[$end]     =  memory_get_usage();
            }
            return number_format(($_mem[$end]-$_mem[$start])/1024);
        } else {
            return number_format(($_info[$end]-$_info[$start]), $dec);
        }

    } else { // 记录时间和内存使用
        $_info[$start]  =  microtime(true);
        if (MEMORY_LIMIT_ON) {
            $_mem[$start]           =  memory_get_usage();
        }
    }
}

function N($key, $step = 0, $save = false)
{
    static $_num    = array();
    if (!isset($_num[$key])) {
        //$_num[$key] = (false !== $save)? S('N_'.$key) :  0;

        $_num[$key] = (false !== $save)? 0 :  0;
    }
    if (empty($step)) {
        return $_num[$key];
    } else {
        $_num[$key] = $_num[$key] + (int) $step;
    }
    if (false !== $save) { // 保存结果
        //S('N_'.$key,$_num[$key],$save);
    }
}

function E($msg, $code = 0)
{
    throw new Exception($msg, $code);
}

function C($name = null, $value = null, $default = null)
{
    static $_config = array();
    // 无参数时获取所有
    if (empty($name)) {
        return $_config;
    }
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtoupper($name);
            if (is_null($value)) {
                return isset($_config[$name]) ? $_config[$name] : $default;
            }
            $_config[$name] = $value;
            return;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0]   =  strtoupper($name[0]);
        if (is_null($value)) {
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
        }
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    // 批量设置
    if (is_array($name)) {
        $_config = array_merge($_config, array_change_key_case($name, CASE_UPPER));
        return;
    }
    return null; // 避免非法参数
}

function parse_name($name, $type = 0)
{
    if ($type) {
        return ucfirst(preg_replace("/_([a-zA-Z])/e", "strtoupper('\\1')", $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

function L($str)
{
    return $str;
}

function to_guid_string($mix)
{
    if (is_object($mix)) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    } else {
        $mix = serialize($mix);
    }
    return md5($mix);
}

function mk_dir($path, $mode = 0777) //creates directory tree recursively
{
    //$GLOBALS["dirseparator"]
    //$path = dirname($path);
    $dirs = explode('/', $path);
    $pos = strrpos($path, ".");
    
    if ($pos === false) {
        // note: three equal signs
        // not found, means path ends in a dir not file
        $subamount = 0;
    } else {
        $subamount = 1;
    }

    $parent_path = '';
    for ($c = 0; $c < count($dirs) - $subamount; $c++) {
        $thispath = $parent_path.$dirs[$c].'/';
        if (!file_exists($thispath)) {
            mkdir($thispath, $mode);
        }
        
        $parent_path = $thispath;
    }
    return true;
}
