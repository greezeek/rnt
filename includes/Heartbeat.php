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


    private static function isExtr($hr1, $hr2, $hr3)
    {
        return ($hr1 <= $hr2 && $hr3 < $hr2);
    }

    public static function getPeaks($graph)
    {

        $peaks = [];

        for ($i = 0; $i < count($graph); $i++) {
            if ($i > 0) {
                if (self::isExtr($graph[$i - 1]['beat'], $graph[$i]['beat'], $graph[$i + 1]['beat'])) {
                    $peaks[] = [$graph[$i]['dt'] => $graph[$i]['beat']];
                }
            }
        }
        return $peaks;
    }


    public function getBeat($workout_id = false)
    {
        $workout = false;

        if (!$workout_id) {
            $workout_id = $this->getLastWorkoutId();
        }

        $q = App::getInstance()->db->prepare('SELECT id, dt, beat FROM heartbeat WHERE workoutId = :workoutid order by dt');
        $q->bindParam(':workoutid', $workout_id, \PDO::PARAM_STR);
        $q->execute();

        if ($q->rowCount() > 0) {
            $workout = $q->fetchAll(\PDO::FETCH_ASSOC);
        }

        if (!$workout) {
            $workout = $this->getWorkout($workout_id);
            $workoutInsert = '';

            foreach ($workout->hrs as $hr) {
                $workoutInsert .= ($workoutInsert ? ', ' : '') . "('$workout->workout_id', '" . date('Y-m-d H:i:s', $hr['dt']) . "', '{$hr['beat']}')";
            }

            App::getInstance()->db->query('INSERT INTO heartbeat (workoutId, dt, beat) VALUES ' . $workoutInsert);
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