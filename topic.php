<?php

require_once "vendor/autoload.php";
date_default_timezone_set('Asia/Yekaterinburg');

$file = 'archive/club2u.ru/index.php/topic,467.0.html';

$board = new \FSA\SMF\Topic;
$board->loadFromFile($file);
var_dump($board->getTopicInfo());
var_dump($board->getTopicMessages());
