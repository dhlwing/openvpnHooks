<?php
namespace shell\worker;

class Disconnect extends \shell\WorkerBase
{

    public function run()
    {

        
        $user = \ModelCli_user::getInstance()->field('uid, quota_cycle, quota_bytes')->where('username="'.$_SERVER['common_name'].'"')->find();

        if ($user) {
            $data = array(
                    //'uid'                => $user['uid'],
                    'end_time'           => time(),
                    'bytes_received'     => $_SERVER['bytes_received'],
                    'bytes_sent'         => $_SERVER['bytes_sent'],
                    'status'             => 0
                );
            $where = array(
                    'uid'            => $user['uid'],
                    'trusted_ip'     => $_SERVER['trusted_ip'],
                    'trusted_port'   => $_SERVER['trusted_port'],
                    'remote_ip'      => $_SERVER['ifconfig_pool_remote_ip'],
                    'status'         => 1
                );
            \ModelCli_log::getInstance()->data($data)->save('',$where);

            $quota_bytes = $user['quota_bytes'] + $_SERVER['bytes_sent'] + $_SERVER['bytes_received'];
            $quota_cycle = $user['quota_cycle'] + $_SERVER['bytes_sent'] + $_SERVER['bytes_received'];

            $dataUser = array(
                    'quota_bytes' => $quota_bytes,
                    'quota_cycle' => $quota_bytes
                );
            // 判断是否超过流量 超过则下线禁止vpn继续链接
            
            if ($quota_cycle > 30 * 1024 * 1204 * 1024) {
                $dataUser['active'] = 0;
            }

            \ModelCli_user::getInstance()->save($dataUser, array('uid'=>$user['uid']));
        } else {
            \shell\ServerBase::output('not user found');
        }


    }
}
