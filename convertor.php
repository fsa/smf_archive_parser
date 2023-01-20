<?php

use FSA\SMF\App;

require_once "vendor/autoload.php";
$path = 'archive/'. App::getConfig('SITE_URL').'/';
$convertor = App::getConvertor();
$convertor->queryExec(file_get_contents("src/sql/drop.sql"), 'Удаление таблиц');
$convertor->queryExec(file_get_contents("src/sql/create.sql"), 'Создание таблиц');

$convertor->mainPage($path . 'index.html');

$convertor->boardScan($path . 'index.php/', '/^board,(\d+)\.(\d+).html$/');
$convertor->boardScan($path, '/^index\.php\?board=(\d+)\.(\d+)/');

$convertor->topicScan($path . 'index.php/', '/^topic,(\d+)\.(msg)?(\d+).html$/');
$convertor->topicScan($path, '/^index\.php\?topic=(\d+)\.(msg)?(\d+)/');

#$path = 'archive/club.2-u.ru/forum/';

#$convertor->topicScan($path, '/^index\.php\?topic=(\d+)\.(msg)?(\d+)/');

## обновление last_modified для таблицы topics
# UPDATE topics t SET last_modified=p.max FROM (SELECT topic_id, max(posted) AS max FROM messages GROUP BY topic_id) p WHERE t.id=p.topic_id
