<?php
namespace shell;

class WorkerBase
{
    public function __get($name)
    {
        switch ($name) {
            case 'redis':
                if (!isset($this->redis) || !$this->redis) {
                    \redis\RedisStorage::config(C('REDIS'));
                    $this->redis = \redis\RedisStorage::getInstance();
                }
                return $this->redis;
            default:
                return false;
        }
    }
}
