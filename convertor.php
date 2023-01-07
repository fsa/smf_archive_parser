<?php

use FSA\SMF\PostgreSQL;
use FSA\SMF\Tools;

require_once "vendor/autoload.php";
$settings = require 'settings.php';
$tz = $settings['TIMEZONE']??'Asia/Novosibirsk';
$path = "archive/{$settings['SITE_URL']}/";

date_default_timezone_set($tz);
$pdo = new PostgreSQL($settings['DATABASE_URL']);
$pdo->query("SET TIMEZONE=\"$tz\"");

$query = file_get_contents("src/sql/drop.sql");
echo 'Удаление таблиц: ' . $pdo->exec($query) . PHP_EOL;
$query = file_get_contents("src/sql/create.sql");
echo 'Создание таблиц: ' . $pdo->exec($query) . PHP_EOL;

// Categories
$board = new \FSA\SMF\Board;
$board->loadFromFile($path . 'index.html');
$categories = $board->getBoardsCategories();
$order = 0;
foreach ($categories as $id => $category) {
    $pdo->insert('categories', ['id' => $id, 'name' => $category, 'category_order' => $order++]);
    echo "Создана категория $id: $category" . PHP_EOL;
}

// Boards
$boards = $board->getBoards();
$board_order = 0;
foreach ($boards as $id => $entity) {
    $entity->id = $id;
    $entity->board_order = $board_order++;
    $pdo->insert('boards', get_object_vars($entity));
    echo "Создана доска $id: {$entity->name}" . PHP_EOL;
}

// All: Topics, Messages...
$dir = $path . 'index.php/';
$all_files = scandir($dir);

// Topics
$match = '/^board,(.*)\.(.*).html$/';
$files = preg_grep($match, $all_files);
$stmt = $pdo->prepare('INSERT INTO topics (id, board_id, title) VALUES (?,?,?) ON CONFLICT (id) DO NOTHING');
foreach ($files as $item) {
    $file = $path . 'index.php/' . trim($item);

    # Получение id из имени файла
    preg_match($match, $item, $search);
    $board_id = intval($search[1]);

    echo 'Обработка файла ' . $file . PHP_EOL;
    $board = new \FSA\SMF\Board;
    $board->loadFromFile($file);
    $topics = $board->getBoardTopics();
    if (!$topics) {
        continue;
    }
    //var_dump($topics);
    foreach ($topics as $topic) {
        $stmt->execute([$topic->id, $board_id, $topic->title]);
        echo "  Добавлен топик №{$topic->id}: {$topic->title}" . PHP_EOL;
    }
}