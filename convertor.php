<?php

use FSA\SMF\PostgreSQL;
use FSA\SMF\Tools;

require_once "vendor/autoload.php";
$settings = require 'settings.php';
$tz = $settings['TIMEZONE'] ?? 'Asia/Novosibirsk';
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
        if ($stmt->rowCount() > 0) {
            echo "  Добавлен топик №{$topic->id}: {$topic->title}" . PHP_EOL;
        } else {
            echo "  Дубликат топика №{$topic->id}: {$topic->title}" . PHP_EOL;
        }
    }
}

// Messages
$match = '/^topic,(.*)\.(.*).html$/';
$files = preg_grep($match, $all_files);
foreach ($files as $item) {
    $file = $path . 'index.php/' . trim($item);

    # Получение id из имени файла
    preg_match($match, $item, $search);
    $topic_id = intval($search[1]);

    echo 'Обработка файла ' . $file . PHP_EOL;
    $topic = new \FSA\SMF\Topic;
    $topic->loadFromFile($file);

    $stmt = $pdo->prepare('SELECT id FROM topics WHERE id=?');
    $stmt->execute([$topic_id]);
    if (!$stmt->fetch(PDO::FETCH_COLUMN)) {
        $topic_info = $topic->getTopicInfo();
        $stmt = $pdo->prepare('INSERT INTO topics (id, board_id, title) VALUES (:id, :board_id, :title)');
        $stmt->execute($topic_info);
        echo "  Добавлен топик №{$topic_info['id']}: {$topic_info['title']}" . PHP_EOL;
    }

    $messages = $topic->getTopicMessages();
    // Users
    $stmt = $pdo->prepare('INSERT INTO members (id, name) VALUES (?,?) ON CONFLICT (id) DO NOTHING');
    foreach ($messages as $message) {
        if ($message->member_id) {
            $stmt->execute([$message->member_id, $message->poster_name]);
            if ($stmt->rowCount() > 0) {
                echo "  Добавлен пользователь №{$message->member_id}: {$message->poster_name}" . PHP_EOL;
            }
        }
    }
    $stmt = $pdo->prepare('INSERT INTO messages (id, topic_id, posted, member_id, subject, poster_name, body, icon) VALUES (:id, :topic_id, :posted, :member_id, :subject, :poster_name, :post, :icon) ON CONFLICT (id) DO NOTHING');
    foreach ($messages as $message) {
        $obj = get_object_vars($message);
        $obj['topic_id'] = $topic_id;
        $stmt->execute($obj);
        if ($stmt->rowCount() > 0) {
            echo "  Добавлено сообщение от {$message->posted} №{$message->id}, пользователь {$message->poster_name}" . PHP_EOL;
        } else {
            echo "  Дубликат сообщения от {$message->posted} №{$message->id}, пользователь {$message->poster_name}" . PHP_EOL;

        }
    }
}
