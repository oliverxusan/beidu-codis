<?php


namespace Ybren\Codis\Test;


use Ybren\Codis\Codis;
use Ybren\Codis\Config\Conf;
use Ybren\Codis\Connection\Conn;
use Ybren\Codis\Zookeeper\RedisFromZk;

class TestConnection
{
    public function testConn(){
        $conn = new Conn();
        $config= [];
        $conn->getSock($config,function (Conf $c){
            return RedisFromZk::connection($c);
        });

    }
}