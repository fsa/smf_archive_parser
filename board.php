<?php

require_once "vendor/autoload.php";

$path = 'archive/club2u.ru/';
$file = $path . 'index.php/board,2.0.html';
$dir = $path . 'index.php/';

#$board = new \FSA\SMF\Board;
#$board->loadFromFile($file);
#var_dump($board->getBoardTopics());

$all_files = scandir($dir);
$files = preg_grep('/^board,(.*).(.*).html$/', $all_files);
foreach ($files as $item) {
    $file = $path . 'index.php/' . trim($item);
    echo 'Обработка файла ' . $file . PHP_EOL;
    $board = new \FSA\SMF\Board;
    $board->loadFromFile($file);
    $topics = $board->getBoardTopics();
    if (!$topics) {
        continue;
    }
    var_dump($topics);
    foreach ($topics as $topic) {
        echo '  ' . $topic->title . PHP_EOL;
    }
}
