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

    public static function session($id) {
        require __DIR__ . '/../views/session.php';
    }

    public static function debug($id) {

        $b = new \Rnt\Heartbeat;
        $beat = $b->getBeat();
        $peaks = \Rnt\Heartbeat::getPeaks($beat);


        require __DIR__ . '/../views/debug.php';
    }


}