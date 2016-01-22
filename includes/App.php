<?php
/**
 * Created by IntelliJ IDEA.
 * User: Swarm
 * Date: 22/01/16
 * Time: 12:53
 */

namespace Rnt;
use Bramus\Router\Router;

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


    public function route()
    {
        $r = new Router();

        $r->match('GET', '/', '\Rnt\Controller::index');
        $r->match('GET', '/debug/([\d\w]+)', '\Rnt\Controller::debug');
        $r->match('GET', '/session/([\d\w]+)', '\Rnt\Controller::session');

        ob_start();
        $r->run();
        $content = ob_get_contents();
        ob_clean();

        include __DIR__ . "/../views/layout.php";
    }


    public function getSession($id = false)
    {
        if($id) {
            $q = $this->db->prepare('select * from session WHERE id = :id and `end` is not null ');
            $q->bindParam(':id', $id, \PDO::PARAM_INT);
            $q->execute();

        } else {
            $q = $this->db->query('select * from session WHERE `end` is null');
        }

        return $q->fetch(\PDO::FETCH_ASSOC);
    }
}