<?php

require_once __DIR__ . '/../vendor/autoload.php';









$b = new \Rnt\Heartbeat;

$beat = $b->getBeat();

var_dump(count($beat));

$peaks = \Rnt\Heartbeat::getPeaks($beat);

var_dump($peaks);