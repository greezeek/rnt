<?php
/**
 * Created by IntelliJ IDEA.
 * User: Swarm
 * Date: 22/01/16
 * Time: 17:02
 */

namespace Rnt;


class Controller
{
    public static function index() {

        require __DIR__ . '/../views/index.php';

    }

    public static function session($id = false) {


        $session = App::getInstance()->getSession($id);

        if(!$session) {
            require __DIR__ . '/../views/nosession.php';
            return;
        }

        if (!$id) {
            // calc peaks
        }

        require __DIR__ . '/../views/session.php';
    }

    public static function sessionStart()
    {
      $q = App::getInstance()->db->prepare('SELECT * FROM session WHERE end is null');
      $q->execute();
      if ($q->rowCount()==0) {
        App::getInstance()->db->query("INSERT INTO session SET start=NOW()");
        $q = App::getInstance()->db->prepare('SELECT * FROM session WHERE end is null');
        $q->execute();
        $session = $q->fetchObject();
      } else {
        $session = $q->fetchObject();
      }
      header("Location: /session/$session->id/"); exit();
    }
  
    public static function debug($id) {

        $b = new \Rnt\Heartbeat;
        $beat = $b->getBeat();
        $peak = \Rnt\Heartbeat::getPeaks($beat);

        foreach ($beat as $tick) {

            $beats[] = $tick['beat'];
            $peaks[] = (isset($peak[$tick['dt']])) ? 50 : 0;

        }


        require __DIR__ . '/../views/debug.php';
    }


}