<?php

use FSA\SMF\Convertor;
use FSA\SMF\PostgreSQL;

require_once "vendor/autoload.php";
$settings = require 'settings.php';
$tz = $settings['TIMEZONE'] ?? 'Asia/Novosibirsk';
date_default_timezone_set($tz);
echo "Используется часовой пояс $tz" . PHP_EOL;
$path = "archive/{$settings['SITE_URL']}/";

$pdo = new PostgreSQL($settings['DATABASE_URL']);
$pdo->query("SET TIMEZONE=\"$tz\"");

$convertor = new Convertor($pdo);
$convertor->queryExec(file_get_contents("src/sql/drop.sql"), 'Удаление таблиц');
$convertor->queryExec(file_get_contents("src/sql/create.sql"), 'Создание таблиц');
$convertor->mainPage($path . 'index.html');
$convertor->boardScan($path . 'index.php/', '/^board,(\d+)\.(\d+).html$/');
$convertor->topicScan($path . 'index.php/', '/^topic,(\d+)\.(\d+).html$/');
#$convertor->topicScan($path, '/^index\.php\?topic=(\d+)\.(\d+)/');

## обновление last_modified для таблицы topics
# UPDATE topics t SET last_modified=p.max FROM (SELECT topic_id, max(posted) AS max FROM messages GROUP BY topic_id) p WHERE t.id=p.topic_id
