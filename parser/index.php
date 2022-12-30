<?php

require_once "../vendor/autoload.php";

$file='../archive/club2u.ru/index.php/board,2.0.html';

$board = new \FSA\SMF\Board;
$board->loadFromFile($file);
var_dump($board->getBoardTopics());