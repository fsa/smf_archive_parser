<?php

require_once "vendor/autoload.php";

$file='archive/club2u.ru/index.html';

$board = new \FSA\SMF\Board;
$board->loadFromFile($file);
var_dump($board->getBoardsCategories());
var_dump($board->getBoards());
