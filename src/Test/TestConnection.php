<?php


namespace Ybren\Codis\Test;


use Ybren\Codis\Config\Conf;
use Ybren\Codis\Connection\Conn;
use Ybren\Codis\Zookeeper\RedisFromZk;

class TestConnection
{
    public function testConn(){
        $conn = new Conn();
        $conn->getSock(function ($connType,Conf $c){
            return RedisFromZk::connection($conf);
        });
    }
}