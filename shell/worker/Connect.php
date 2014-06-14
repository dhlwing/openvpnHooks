<?php
namespace shell\worker;

class Connect extends \shell\WorkerBase
{

    public function run()
    {
        
        $user = \ModelCli_user::getInstance()->field('uid')->where('username="'.$_SERVER['common_name'].'"')->find();
        if ($user) {
            $data = array(
                    'uid'            => $user['uid'],
                    'start_time'     => time(),
                    'trusted_ip'     => $_SERVER['trusted_ip'],
                    'trusted_port'   => $_SERVER['trusted_port'],
                    'protocol'       => $_SERVER['proto_1'],
                    'remote_ip'      => $_SERVER['ifconfig_pool_remote_ip'],
                    'remote_netmask' => $_SERVER['route_netmask_1'],
                    'status'         => 1
                );
            \ModelCli_log::getInstance()->data($data)->add();
        } else {
            // notify admin to kill this user
        }

    }
}
