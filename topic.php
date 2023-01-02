<?php

require_once "vendor/autoload.php";

$file = 'archive/club2u.ru/index.php/topic,467.0.html';

$board = new \FSA\SMF\Topic;
$board->loadFromFile($file);
var_dump($board->getTopicMessages());
