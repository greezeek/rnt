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
    public static function index()
    {
        $stmt = App::getInstance()->db->query('
          SELECT s.*, m.name AS "thumb"
          FROM session s 
          INNER JOIN (SELECT session_id, name, MIN(date) FROM media GROUP BY session_id) m ON m.session_id = s.id  
          WHERE s.end IS NOT NULL
          ORDER BY s.id DESC');
        $data = $stmt->fetchAll(\PDO::FETCH_OBJ);
        
        require __DIR__ . '/../views/index.php';
    }

    public static function session($id) 
    {
        $session = App::getInstance()->getSession($id);
        if(!$session) {
          require __DIR__ . '/../views/nosession.php';
          return;
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