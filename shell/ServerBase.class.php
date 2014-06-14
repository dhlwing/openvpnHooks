<?php


namespace shell;

class ServerBase
{
    public static $help = "
********************************************************************************
*
*    desc  : 命令行程序基类，用于执行异步任务、定时任务等
*    author: kong
*    email : 249717835@qq.com
*
******************************************************************************** 

usage
    参数：
        -h ：  显示帮助 (show help)
        -w ：  执行后台任务 如 -w=connect 则执行worker下面的 connect类的run
";
    
    public $worker = false;

    public static function showHelp()
    {
        echo self::$help;
    }

    public function __construct($worker)
    {
        if (class_exists($worker)) {
            $this->worker = $worker;
        } else {
            self::showHelp();
        }
    }

    public static function output($str)
    {
        echo $str . "\n";
    }

    public function start()
    {
        $lockFile = DATA_DIR . str_replace('\\', '_', $this->worker). ".lock";
        if (file_exists($lockFile)) {
            $this->output('worker '.$this->worker.' has running now');
        } else {
            //mk_dir($lockFile);
            //$fp = touch($lockFile);
            $worker = $this->worker;
            $worker = new $worker();
            $worker->run();
            unlink($lockFile);
        }

    }
}
