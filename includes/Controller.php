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

    public static function void()
    {
        header("Location: /"); exit();

    }

    public static function session($id = false)
    {
        $id = intval($id);
        if($id <= 0) {
            $session = App::getInstance()->getSession($id);
            if($session) {
                header("Location: /session/$session->id/"); exit();
            } else {
                header("Location: /"); exit();
            }
        } else {
            $session = App::getInstance()->getSession($id);
        }
        if(!$session) {
          $message = 'Session not found';
          require __DIR__ . '/../views/nosession.php';
          return;
        }

        $q = App::getInstance()->db->prepare("SELECT * FROM finish WHERE session_id=$session->id ORDER BY ID");
        $q->execute();
        
        $images = $q->fetchAll(\PDO::FETCH_OBJ);

        $b = new \Rnt\Heartbeat;
        $beat = $b->getBeat($session->id);
        $peak = \Rnt\Heartbeat::getPeaks($beat);
        foreach ($beat as $tick) {
          $beats[] = $tick['beat'];
          $peaks[] = (isset($peak[$tick['dt']])) ? 150 : 0;
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
      }
      $session = $q->fetchObject();
      header("Location: /session/$session->id/"); exit();
    }

    public static function sessionClose()
    {
        if(!$session = App::getInstance()->getSession()) {
            header("Location: /"); exit();
        }

        if(App::getInstance()->closeSession($session->id)) {

            $b = new \Rnt\Heartbeat;
            $beat = $b->getBeat($session->id);
            
            if(!$beat) {
                $message = 'No heartbeat yet. Please restart...';
                require __DIR__ . '/../views/nosession.php';
                return;
            }

            // проверить что для этих пиков есть картинки
            $stmt = App::getInstance()->db->query("select min(dt) min,max(dt) max from heartbeat");
            $stmt->execute();
            $row = $stmt->fetchObject();
            
            $stmt = App::getInstance()->db->query("select count(*) from media where session_id=$session->id and $row->min <= date and date <= $row->max");
            $stmt->execute();
            $cols = intval($stmt->fetchColumn());
            
            if (!$cols) {
                $st = App::getInstance()->db->query("update session set end = null where id=$session->id");
                $st->execute();
                $st = App::getInstance()->db->query("delete from heartbeat where session=$session->id");
                $st->execute();
                $message = 'Please wait...'."<script>window.setTimeout('window.location.reload()',2000)</script>";
                require __DIR__ . '/../views/nosession.php';
                return;
            }
            
            $peak = \Rnt\Heartbeat::getPeaks($beat);

            $gif = [];
            $thumb = [];

            $q = '';
            foreach($peak as $p) {
                $file = App::getInstance()->c['generate.gif']($session->id, $p->start, $p->end);
                $preview = App::getInstance()->c['generate.thumb']($session->id, $p->start, $p->end);
                $q .= ($q ? ', ' : '') . "('$session->id', $p->start, $p->end, '$file', '$preview')";
            }
            $q = 'INSERT INTO finish (session_id, start, end, gif, thumb) VALUES  ' . $q ;
            App::getInstance()->db->query($q);
            header("Location: /session/$session->id/"); exit();
        }

        $message = 'session not closed';
        require __DIR__ . '/../views/nosession.php';
    }

    public static function debug($id) {
        $b = new \Rnt\Heartbeat;
        $beat = $b->getBeat(16);
        $peak = \Rnt\Heartbeat::getPeaks($beat);

        var_dump($peak);
        foreach ($beat as $tick) {
            $beats[] = $tick['beat'];
            $peaks[] = (isset($peak[$tick['dt']])) ? 50 : 0;
        }
        require __DIR__ . '/../views/debug.php';
    }


}