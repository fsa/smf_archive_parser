<?php

use FSA\SMF\PostgreSQL;

require_once "vendor/autoload.php";
$settings = require 'settings.php';
$pdo = new PostgreSQL($settings['DATABASE_URL']);

$query = file_get_contents("src/sql/drop.sql");
echo 'Удаление таблиц: ' . $pdo->exec($query) . PHP_EOL;
$query = file_get_contents("src/sql/create.sql");
echo 'Создание таблиц: ' . $pdo->exec($query) . PHP_EOL;

// Categories
$file = 'archive/club2u.ru/index.html';

$board = new \FSA\SMF\Board;
$board->loadFromFile($file);
$categories = $board->getBoardsCategories();
$order = 0;
foreach ($categories as $id => $category) {
    $pdo->insert('categories', ['id' => $id, 'name' => $category, 'category_order' => $order++]);
    echo "Создана категория $id: $category" . PHP_EOL;
}

// Boards
$boards = $board->getBoards();
var_dump($boards);
$board_order = 0;
foreach ($boards as $id => $entity) {
    $entity->id = $id;
    $entity->board_order = $board_order++;
    $pdo->insert('boards', get_object_vars($entity));
    echo "Создана доска $id: {$entity->name}" . PHP_EOL;
}
