<?php
/**
 * Created by IntelliJ IDEA.
 * User: Swarm
 * Date: 22/01/16
 * Time: 14:28
 */

namespace Rnt;


class Heartbeat
{

    private $sessionkey;
    const OFFSET = 12;

    private static function isExtr(&$graph, &$i, $maxval = 0)
    {
        $max = $i;
        $maxval = $graph[$i]['beat'];
        $leftOffset = $i;
        $rightOffset = (($i + self::OFFSET) > count($graph)) ? count($graph) : ($i + self::OFFSET);

        for($j = $leftOffset; $j < $rightOffset; $j++) {

            if($graph[$j]['beat'] > $maxval  ) {
                $max = $j;
                $maxval = $graph[$j]['beat'];
            }
        }

        if($i != $max) {
            $i = $max;
            return self::isExtr($graph, $i, $maxval);
        }
        return true;
    }

    public static function getPeaks($graph)
    {
        $peaks = [];
        $i = 1;
        while($i < count($graph)) {
            if (self::isExtr($graph, $i)) {

                $peaks[$graph[$i]['dt']] = (object)[
                    'start' => isset($graph[$i - 5]['dt']) ? $graph[$i - 5]['dt'] : $graph[0]['dt'],
                    'end' => isset($graph[$i + 5]['dt']) ? $graph[$i + 5]['dt'] : $graph[max(array_keys($graph))]['dt']
                ];
            }
            $i += self::OFFSET ;

        }
        return $peaks;
    }


    public function checkWorkoutExists($workoutId)
    {
        $q = App::getInstance()->db->prepare('SELECT * FROM heartbeat where workoutId = :workoutid');
        $q->bindParam(':workoutid', $workoutId);
        $q->execute();
        return $q->rowCount();
    }


    public function getBeat($session = false)
    {
        $workout = false;

        $q = App::getInstance()->db->prepare('SELECT id, dt, beat FROM heartbeat WHERE session = :session order by dt');
        $q->bindParam(':session', $session, \PDO::PARAM_STR);
        $q->execute();

        if ($q->rowCount() > 0) {
            $workout = $q->fetchAll(\PDO::FETCH_ASSOC);
        }

        if (!$workout) {
            $workout_id = $this->getLastWorkoutId();

            if ($this->checkWorkoutExists($workout_id)) {
                return false;
            }
            $workout = $this->getWorkout($workout_id);
            $workoutInsert = '';

            foreach ($workout->hrs as $hr) {
                $workoutInsert .= ($workoutInsert ? ', ' : '') . "('$workout->workout_id', '$session', '" . date('Y-m-d H:i:s', $hr['dt']) . "', '{$hr['beat']}')";
            }

            App::getInstance()->db->query('INSERT INTO heartbeat (workoutId, session, dt, beat) VALUES ' . $workoutInsert);
            $workout = $workout->hrs;
        }
        return $workout;
    }

    private function auth()
    {
        $login = 'chernov.emin@gmail.com';
        $password = 'notasecret';

        // Авторизация
        $ch = curl_init("http://www.sports-tracker.com/apiserver/v1/login?source=javascript");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'l' => $login,
            'p' => $password,
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($info['http_code'] != 200) throw new \Exception("failed_to_login_" . $info['http_code'], 1453453931);
        $body = json_decode($body);
        if (!$body->sessionkey) throw new \Exception("invalid_sessionkey", 1453453976);
        $this->sessionkey = $body->sessionkey;
    }

    public function getLastWorkoutId()
    {
        if (!$this->sessionkey) {
            $this->auth();
        }

        // Получение ID последнего воркаута
        $ch = curl_init("http://www.sports-tracker.com/apiserver/v1/workouts?sortonst=true&limit=1&offset=0");
        curl_setopt($ch, CURLOPT_COOKIE, "sessionkey=$this->sessionkey; social=1; _gat=1; dashboardFeed=my-workouts; _ga=GA1.2.290589800.1");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "STTAuthorization: $this->sessionkey",
        ));
        $body = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($info['http_code'] != 200) throw new \Exception("failed_to_get_dashboard_" . $info['http_code'], 1453455322);
        $body = json_decode($body);
        $workout_id = $body->payload[0]->workoutKey;
        if (!$workout_id) throw new \Exception("invalid_workout_id", 1453455611);
        return $workout_id;
    }

    public function getWorkout($workout_id = false)
    {
        if (!$workout_id) {
            throw new \Exception('No Workout id', 235426);
        }

        if (!$this->sessionkey) {
            $this->auth();
        }

        // Получение данных воркаута в виде XML
        $ch = curl_init("http://www.sports-tracker.com/apiserver/v1/workout/exportGpx/$workout_id?token=$this->sessionkey");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($info['http_code'] != 200) throw new \Exception("failed_to_get_xml" . $info['http_code'], 1453455916);

        $body = str_replace("gpxtpx:TrackPointExtension", "gpxtpx_TrackPointExtension", $body);
        $body = str_replace("gpxtpx:hr", "gpxtpx_hr", $body);

        // Парсинг XML через simple XML
        $dom = new \DOMDocument();
        $dom->encoding = 'UTF-8';
        $dom->strictErrorChecking = false;
        $dom->substituteEntities = true;
        $dom->validateOnParse = false;
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->recover = true;
        if (!$dom->loadXML($body, LIBXML_NOCDATA | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            throw new \Exception('failed_to_parse_xml', 1453456104);
        }

        $xml = new \SimpleXMLElement($dom->saveXML());

        $hrs = array();
        foreach ($xml->trk->children() as $seq) {
            foreach ($seq->children() as $trkpt) {
                $ts = strtotime(strval($trkpt->time));
                $hr = intval($trkpt->extensions->gpxtpx_TrackPointExtension->gpxtpx_hr);
                if ($hr > 0 && $ts > 0) {
                    $hrs[] = ['dt' => $ts, 'beat' => $hr];
                }
            }
        }

        return (object)array(
            'workout_id' => $workout_id,
            'sessionkey' => $this->sessionkey,
            'hrs' => $hrs,
        );
    }


}