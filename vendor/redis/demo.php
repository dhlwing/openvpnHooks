<?php

include_once __DIR__ . '/RedisStorage.class.php';

use redis\RedisStorage as RedisStorage;

$config = array(//缓存的配置，要求的配置格式。
    'nodes' => array(
        array('master' => "192.168.8.230:27000", 'slave' => "192.168.8.231:27000"),
        array('master' => "192.168.8.230:27001", 'slave' => "192.168.8.231:27001"),
        array('master' => "192.168.8.230:27002", 'slave' => "192.168.8.231:27002"),
        array('master' => "192.168.8.230:27003", 'slave' => "192.168.8.231:27003"),
        array('master' => "192.168.8.230:27004", 'slave' => "192.168.8.231:27004"),
        array('master' => "192.168.8.232:27005", 'slave' => "192.168.8.231:27005"),
        array('master' => "192.168.8.232:27006", 'slave' => "192.168.8.231:27006"),
        array('master' => "192.168.8.232:27007", 'slave' => "192.168.8.231:27007"),
        array('master' => "192.168.8.232:27008", 'slave' => "192.168.8.231:27008"),
        array('master' => "192.168.8.232:27009", 'slave' => "192.168.8.231:27009"),
    ),
    'db' => 2
);
RedisStorage::config($config);


//*******************************存储的使用方法*************************************
$stor = RedisStorage::getInstance();
if (!$stor) {
    exit('check your config');
}
$stor->set("key", 1111);
var_dump($stor->get("key"));
$stor->close();
exit;
//*******************************事務的使用方法（只支持單個key）*************************************
$stor = RedisStorage::getInstance();
$stor->MULTI();
$stor->incr("key");
$stor->incr("key");
$stor->incr("key");
$stor->EXEC();
echo $stor->get("key");

exit();
//***************************如果你在项目中需要访问2个集群的数据，需要下面的方法*****************
$config = array(//存储的配置，要求的配置格式。
    'WEB' => array('nodes' => array(
            array('master' => "192.168.8.230:27000", 'slave' => "192.168.8.231:27000"),
            array('master' => "192.168.8.230:27001", 'slave' => "192.168.8.231:27001"),
            array('master' => "192.168.8.230:27002", 'slave' => "192.168.8.231:27002"),
            array('master' => "192.168.8.230:27003", 'slave' => "192.168.8.231:27003"),
            array('master' => "192.168.8.230:27004", 'slave' => "192.168.8.231:27004"),
            array('master' => "192.168.8.232:27005", 'slave' => "192.168.8.231:27005"),
            array('master' => "192.168.8.232:27006", 'slave' => "192.168.8.231:27006"),
            array('master' => "192.168.8.232:27007", 'slave' => "192.168.8.231:27007"),
            array('master' => "192.168.8.232:27008", 'slave' => "192.168.8.231:27008"),
            array('master' => "192.168.8.232:27009", 'slave' => "192.168.8.231:27009"),
        ),
        'db' => 15
    ),
    'APP' => array('nodes' => array(
            array('master' => "192.168.8.230:27000", 'slave' => "192.168.8.231:27000"),
            array('master' => "192.168.8.230:27001", 'slave' => "192.168.8.231:27001"),
            array('master' => "192.168.8.230:27002", 'slave' => "192.168.8.231:27002"),
            array('master' => "192.168.8.230:27003", 'slave' => "192.168.8.231:27003"),
            array('master' => "192.168.8.230:27004", 'slave' => "192.168.8.231:27004"),
            array('master' => "192.168.8.232:27005", 'slave' => "192.168.8.231:27005"),
            array('master' => "192.168.8.232:27006", 'slave' => "192.168.8.231:27006"),
            array('master' => "192.168.8.232:27007", 'slave' => "192.168.8.231:27007"),
            array('master' => "192.168.8.232:27008", 'slave' => "192.168.8.231:27008"),
            array('master' => "192.168.8.232:27009", 'slave' => "192.168.8.231:27009"),
        ),
        'db' => 14
    )
);
RedisMultiStorage::config($config); //入口文件配置一次
$WEB = RedisMultiStorage::getInstance("WEB");//获取WEB前端redis集群实例（存储）
$APP = RedisMultiStorage::getInstance("APP");//获取APP的redis集群实例（存储）
$WEB->set("key5", "web");
var_dump($WEB->get("key5"));
$APP->set("key5", "app");
var_dump($APP->get("key5"));
