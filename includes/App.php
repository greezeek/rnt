<?php
/**
 * Created by IntelliJ IDEA.
 * User: Swarm
 * Date: 22/01/16
 * Time: 12:53
 */

namespace Rnt;

/**
 * Class App
 * @package Rnt
 *
 * @property-read \PDO $db
 */
class App
{

    private static $instance;

    private $storage;

    public static function getInstance(){
        if(self::$instance instanceof static) {
            return self::$instance;
        }
        return self::$instance = new static;
    }

    private function __construct()
    {
        try {
            $this->storage['config'] = require_once __DIR__ . "/../config/config.php";
            $this->storage['db'] = new \PDO('mysql:dbname=' . $this->config['db']['db'] . ';host=' . $this->config['db']['host'], $this->config['db']['user'], $this->config['db']['pass']);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function __get($var)
    {
        return isset($this->storage[$var]) ? $this->storage[$var] : null;
    }

}