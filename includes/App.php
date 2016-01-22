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
 * @property-read array $c
 */
class App
{

    private static $instance;

    private $storage;

    public static function getInstance()
    {
        if (self::$instance instanceof static) {
            return self::$instance;
        }
        return self::$instance = new static;
    }

    private function __construct()
    {
        try {
            $this->storage['config'] = require_once __DIR__ . "/../config/config.php";
            $this->storage['db'] = new \PDO('mysql:dbname=' . $this->config['db']['db'] . ';host=' . $this->config['db']['host'], $this->config['db']['user'], $this->config['db']['pass']);

            require_once __DIR__ . '/../bin/bootstrap.php';

            $this->storage['c'] = $c;

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
        $r->match('GET', '/session/start', '\Rnt\Controller::sessionStart');
        $r->match('GET', '/session/finish', '\Rnt\Controller::sessionClose');
        $r->match('GET', '/session/([\d\w]+)', '\Rnt\Controller::session');
        $r->match('GET', '/session', '\Rnt\Controller::session');
        $r->match('GET', '/.+', '\Rnt\Controller::void');


        ob_start();
        $r->run();
        $content = ob_get_contents();
        ob_clean();

        include __DIR__ . "/../views/layout.php";
    }


    public function getSession($id = false)
    {
        if ($id) {
            $q = $this->db->prepare('select * from session WHERE id = :id');
            $q->bindParam(':id', $id, \PDO::PARAM_INT);
            $q->execute();
        } else {
            $q = $this->db->query('select * from session WHERE `end` is null');
        }

        return $q ? $q->fetchObject() : false;
    }


    public function closeSession($id = false)
    {
        if ($id) {
            $q = $this->db->prepare('UPDATE session SET end = NOW() WHERE id = :id and `end` is null ');
            $q->bindParam(':id', $id, \PDO::PARAM_INT);

            if ($q->execute() && $q->rowCount()) {
                return true;
            }
        }
        return false;
    }

}