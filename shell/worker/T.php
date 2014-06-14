<?php
namespace shell\worker;


class Connect extends \shell\WorkerBase
{
    protected static $autoBorrowIds = array();
    public static $storageKey = 'autoBorrow';

    public static function register($id, $time)
    {
        if (is_numeric($id) && $id && is_numeric($time)) {
            if (!isset(self::$autoBorrowIds[$id])) {
                self::$autoBorrowIds[$id] = $time;
                return true;
            }
        }
        return false;
    }

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

    public function getQueueData($set, $start, $limit, $score = true)
    {
        $start = intval($start);
        $stop  = $start + $limit;
        if ($score) {
            return $this->redis->zRange($set, $start, $stop, true);
        } else {
            return $this->redis->zRange($set, $start, $stop);
        }
    }

    public function run()
    {
        // $this->redis->zAdd(self::$storageKey ,time(), $id);
        $start = 0;
        while (true) {
            $queueData = $this->getQueueData(self::$storageKey, $start, 10);
            if (!$queueData) {
                $start = 0;
            } else {
                $start = $start + 10;
            }
            if ($queueData) {
                foreach ($queueData as $id => $time) {
                    //$this->redis->zDelete(self::$storageKey, $id);
                    $this->register($id, $time);
                }
            }

            if (self::$autoBorrowIds) {
                foreach (self::$autoBorrowIds as $borrowId => $time) {
                    if ($time == 0 || $time <= time()) {
                        self::doBorrow($borrowId);
                        unset(self::$autoBorrowIds[$borrowId]);
                        // delete borrowId from storage
                        $this->redis->zDelete(self::$storageKey, $borrowId);
                    } else {
                        // wait some time;
                    }
                }
            }
            sleep(1);
        }
    }
}
