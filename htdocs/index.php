<?php

require_once __DIR__ . '/../vendor/autoload.php';
//$app = \Rnt\App::getInstance();

$b = \Rnt\Heartbeat::getLast();

var_dump(\Rnt\Heartbeat::getPeaks($b->hrs));
