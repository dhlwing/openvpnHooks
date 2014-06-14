<?php

namespace redis;

include_once __DIR__ . '/RedisBase.class.php';
include_once __DIR__ . '/ConsistentHash.class.php';

use \Redis;
use redis\RedisBase      as RedisBase;
use redis\ConsistentHash as ConsistentHash;

class RedisStorage extends RedisBase
{
    /*
     * 目标物理结点
     */

    private $targets;

    /*
     * 单例
     */
    private static $instance;

    /*
     * redis实例
     */
    private $redis = array();
    /*
     * config
     */
    public $config = array();

    private function __construct()
    {

    }

    /*
     * 关闭socket
     */

    public function close()
    {
        foreach ((array) $this->redis as $target => $value) {
            try {
                if ($value) {
                    $value->close();
                }
                unset($this->redis[$target]);
            } catch (Exception $exc) {
                throw new Exception("close error!");
            }
        }
    }

    public static function getInstance($name = 'default')
    {
        if (!isset(self::$instance[$name])) {
            self::$instance[$name] = new self;
        }
        return self::$instance[$name];
    }

    public static function config($config, $name = null)
    {
        if (empty($config)) {
            throw new Exception("config error!");
        }
        if (isset($name)) {
            $instance = self::getInstance($name);
            $instance->config = $config[$name];
        } else {
            $instance = self::getInstance();
            $instance->config = $config;
        }
        $instance->init();
    }

    public function init()
    {
        $ShmList = ShmConfig::getStorageAvailableAddress($this->config); //从内存中获得可用列表
        if (empty($ShmList)) {//内存中没有，可能ping脚本没启,直接用配置
            foreach ($this->config['nodes'] as $value) {
                $list[] = $value['master'];
            }
        } else {
            $list = $ShmList;
        }
        $this->targets = $list; //和cache不一样，失效后是false不能剔除
    }

    /*
     * 根据key和实际结点建立链接
     */

    public function connectTarget($key)
    {
        $this->target = $target = $this->hash($key);
        if (!$target) {//主从都down了
            return false;
        }
        if (!isset($this->redis[$target])) {//每个物理机对应一个new redis
            $this->redis[$target] = new Redis();
            $ip_port = explode(":", $target);
            try {
                $conn = $this->redis[$target]->connect($ip_port[0], $ip_port[1], 3);
                if (!$conn) {
                    unset($this->redis[$target]);
                    $this->redis[$target] = false;
                    return false;
                }
                if (isset($this->config['db'])) {//如果设置了db
                    $this->redis[$target]->select($this->config['db']);
                }
            } catch (Exception $e) {
                unset($this->redis[$target]);
                throw new Exception("connect error!({$target})(db:".(isset($this->config['db']) ? $this->config['db'] : 0).") \n".$e);
            }
        }

        return $this->redis[$target];
    }

    /*
     * 取模打散
     */

    private function hash($key)
    {

        $hash = abs(crc32($key));
        $count = count($this->targets);
        $mod = $hash % $count;
        return $this->targets[$mod];
    }
}
