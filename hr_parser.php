<?php
error_reporting(E_ALL ^ E_NOTICE);

function getHrs()
{
  $login = 'chernov.emin@gmail.com';
  $password = 'notasecret';
  
  // Авторизация
  $ch = curl_init("http://www.sports-tracker.com/apiserver/v1/login?source=javascript");
  curl_setopt($ch,CURLOPT_POST,true);
  curl_setopt($ch,CURLOPT_POSTFIELDS,array(
    'l'=>$login,
    'p'=>$password,
  ));
  curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
  $body = curl_exec($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);
  if ($info['http_code'] != 200) throw new Exception("failed_to_login_".$info['http_code'],1453453931);
  $body = json_decode($body);
  if (!$body->sessionkey) throw new Exception("invalid_sessionkey",1453453976);
  $sessionkey = $body->sessionkey;

  // Получение ID последнего воркаута
  $ch = curl_init("http://www.sports-tracker.com/apiserver/v1/workouts?sortonst=true&limit=1&offset=0");
  curl_setopt($ch, CURLOPT_COOKIE, "sessionkey=$sessionkey; social=1; _gat=1; dashboardFeed=my-workouts; _ga=GA1.2.290589800.1");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "STTAuthorization: $sessionkey",
  ));
  $body = curl_exec($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);
  if ($info['http_code'] != 200) throw new Exception("failed_to_get_dashboard_".$info['http_code'],1453455322);
  $body = json_decode($body);
  $workout_id = $body->payload[0]->workoutKey;
  if (!$workout_id) throw new Exception("invalid_workout_id",1453455611);

  // Получение данных воркаута в виде XML
  $ch = curl_init("http://www.sports-tracker.com/apiserver/v1/workout/exportGpx/$workout_id?token=$sessionkey");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $body = curl_exec($ch);
  $info = curl_getinfo($ch);
  curl_close($ch);
  if ($info['http_code'] != 200) throw new Exception("failed_to_get_xml".$info['http_code'],1453455916);

  $body = str_replace("gpxtpx:TrackPointExtension","gpxtpx_TrackPointExtension",$body);
  $body = str_replace("gpxtpx:hr","gpxtpx_hr",$body);

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
    throw new Exception('failed_to_parse_xml',1453456104);
  }

  $xml = new \SimpleXMLElement($dom->saveXML());

  $hrs = array();
  foreach ($xml->trk->children() as $seq) {
    foreach ($seq->children() as $trkpt) {
      $ts = strtotime(strval($trkpt->time));
      $hr = intval($trkpt->extensions->gpxtpx_TrackPointExtension->gpxtpx_hr);
      if ($hr>0 && $ts>0) {
        $hrs[$ts] = $hr;
      }
    }
  }
  
  return (object)array(
    'workout_id' => $workout_id,
    'sessionkey' => $sessionkey,
    'hrs' => $hrs,
  );
}
