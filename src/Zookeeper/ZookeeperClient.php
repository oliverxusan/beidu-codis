<?php


namespace Ybren\Codis\Zookeeper;


use Ybren\Codis\Exception\CodisException;

class ZookeeperClient
{
    /**
     * @var Zookeeper
     */
    private $zookeeper ;


    public function __construct($address) {
        if (!class_exists("Zookeeper")){
            throw new CodisException("Zookeeper extend is uninstall.");
        }
        $this->zookeeper = new \Zookeeper($address);
    }

    public function set($path, $value) {
        if (!$this->zookeeper->exists($path)) {
            $this->makePath($path);
            $this->makeNode($path, $value);
        } else {
            $this->zookeeper->set($path, $value);
        }
    }


    public function makePath($path, $value = '') {
        $parts = explode('/', $path);
        $parts = array_filter($parts);
        $subPath = '';
        while (count($parts) > 1) {
            $subPath .= '/' . array_shift($parts);
            if (!$this->zookeeper->exists($subPath)) {
                $this->makeNode($subPath, $value);
            }
        }
    }

    public function makeNode($path, $value, array $params = array()) {
        if (empty($params)) {
            $params = array(
                array(
                    'perms'  => Zookeeper::PERM_ALL,
                    'scheme' => 'world',
                    'id'     => 'anyone',
                )
            );
        }
        return $this->zookeeper->create($path, $value, $params);
    }


    public function get($path) {
        if (!$this->zookeeper->exists($path)) {
            return null;
        }
        return $this->zookeeper->get($path);
    }


    public function getChildren($path) {
        if (strlen($path) > 1 && preg_match('@/$@', $path)) {
            $path = substr($path, 0, -1);
        }
        return $this->zookeeper->getChildren($path);
    }

    public function removeNode($nodePath){
        return $this->zookeeper->delete($nodePath);
    }

    /**
     * Specify application credentials.
     *
     * @param string   $scheme  digest
     * @param string   $cert forexample oliver:123456as
     * @param callable $completion_cb
     *
     * @return bool
     */
    public function addAuth($scheme, $cert, $completion_cb = null ){
        return $this->zookeeper->addAuth($scheme, $cert, $completion_cb);
    }

}