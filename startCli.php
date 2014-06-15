#!/usr/bin/php
<?php

define('DEBUG', true);
set_time_limit(0);

if (DEBUG === true) {
    error_reporting(E_ALL);
    ini_set("display_erros", "On");
} else {
    error_reporting(0);
    ini_set("display_erros", "Off");
}
//mb_internal_encoding("UTF-8");
date_default_timezone_set('Asia/ShangHai');
define('ROOT', dirname(__FILE__).'/');
define('DATA_DIR', ROOT.'data/');

require ROOT.'config.php';
require ROOT.'common/function.inc.php';
require ROOT.'shell/ClassLoader.php';

shell\ClassLoader::addDirectories(
    array(
        ROOT,
        ROOT . "vendor",
    )
);

shell\ClassLoader::register();

spl_autoload_register(
    function ($className) {
        if (strpos($className, 'ModelCli_') !== 0) {
            return false;
        }

        eval(sprintf('class %s extends shell\ModelCli {}', $className));
    }
);

$worker = '';
if (!isset($argv[1])) {
    $argv[1] = '-w=Disconnect';
}
if ($argc >= 1) {
    $options = explode('=', $argv[1]);
    if ($options[0] != '-w') {
        shell\ServerBase::showHelp();
    } else {
        
        if (isset($options[1])) {
            $worker = 'shell\\worker\\' . $options[1];

            if (class_exists($worker)) {
                $service = new shell\ServerBase($worker);
                $service->start();
            } else {
                shell\ServerBase::output("worker " . $options[1] . " not found");
            }
            
        } else {
            shell\ServerBase::output("worker " . $options[1] . " not found");
        }
    }
    
} else {
    shell\ServerBase::showHelp();
}
