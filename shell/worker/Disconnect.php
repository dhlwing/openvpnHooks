<?php
namespace shell\worker;

class Disconnect extends \shell\WorkerBase
{

    public function run()
    {

        $user = \ModelCli_user::getInstance()->field('uid, quote_cycle, quote_bytes')->where('username="'.$_SERVER['common_name'].'"')->find();

        echo \ModelCli_user::getInstance()->getLastSql();

        if ($user) {
            $data = array(
                    //'uid'                => $user['uid'],
                    'end_time'           => time(),
                    'bytes_received'     => $_SERVER['bytes_received'],
                    'bytes_sent'         => $_SERVER['bytes_sent'],
                    'status'             => 0
                );
            $where = array(
                    'where' => array(
                        'uid'            => $user['uid'],
                        'trusted_ip'     => $_SERVER['trusted_ip'],
                        'trusted_port'   => $_SERVER['trusted_port'],
                        'remote_ip'      => $_SERVER['ifconfig_pool_remote_ip'],
                        'status'         => 1
                    )
                );
            \ModelCli_log::getInstance()->data($data)->save('',$where);

            $quote_bytes = $user['quote_bytes'] + $_SERVER['bytes_sent'] + $_SERVER['bytes_received'];
            $quote_cycle = $user['quote_cycle'] - ($_SERVER['bytes_sent'] + $_SERVER['bytes_received']);

            $dataUser = array(
                    'quote_bytes' => $quote_bytes,
                    'quote_cycle' => $quote_cycle
                );
            // 判断是否超过流量 超过则下线禁止vpn继续链接
            
            if ($quote_cycle <= 0) {
                $dataUser['active'] = 0;
            }

            $where = array(
                    'where' => array('uid'=>$user['uid'])
                );
            \ModelCli_user::getInstance()->save($dataUser, $where);
            $sql = \ModelCli_user::getInstance()->getLastSql();
            \shell\ServerBase::output($sql);
        } else {
            \shell\ServerBase::output('not user found');
        }


    }
}
