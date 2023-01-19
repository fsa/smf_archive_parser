<?php

namespace FSA\SMF;

class Convertor
{

    public function __construct(private $pdo)
    {
    }

    public function queryExec($query, $description)
    {
        echo $description . ': ' . $this->pdo->exec($query) . PHP_EOL;
    }

    public function mainPage($path)
    {
        // Categories
        $board = new Board;
        $board->loadFromFile($path);
        $categories = $board->getBoardsCategories();
        $order = 0;
        foreach ($categories as $id => $category) {
            $this->pdo->insert('categories', ['id' => $id, 'name' => $category, 'category_order' => $order++]);
            echo "Создана категория $id: $category" . PHP_EOL;
        }
        // Boards
        $boards = $board->getBoards();
        $board_order = 0;
        foreach ($boards as $id => $entity) {
            $entity->id = $id;
            $entity->board_order = $board_order++;
            $this->pdo->insert('boards', get_object_vars($entity));
            echo "Создана доска $id: {$entity->name}" . PHP_EOL;
        }
    }

    public function board($file, $board_id)
    {
        echo 'Обработка файла форума (board): ' . $file . PHP_EOL;
        $board = new \FSA\SMF\Board;
        $board->loadFromFile($file);
        $topics = $board->getBoardTopics();
        if (!$topics) {
            return;
        }

        // Users
        $users = [];
        foreach ($topics as $topic) {
            if ($topic->user_id) {
                $users[$topic->user_id] = ['name' => $topic->username];
            }
            if ($topic->updated_member_id) {
                $users[$topic->updated_member_id] = ['name' => $topic->updated_member_name];
            }
        }
        $this->upsertUsers($users);

        //var_dump($topics);
        $stmt = $this->pdo->prepare('INSERT INTO topics (id, is_sticky, board_id, title, started_member_id, started_member_name, updated_member_id, updated_member_name, num_replies, num_views, last_modified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT (id) DO NOTHING');
        echo "  Найдено топиков: " . count($topics) . PHP_EOL;
        foreach ($topics as $topic) {
            $stmt->execute([$topic->id, $topic->sticky ? 't' : 'f', $board_id, $topic->title, $topic->user_id, $topic->username, $topic->updated_member_id, $topic->updated_member_name, $topic->num_replies, $topic->num_views, $topic->last_modified]);
            if ($stmt->rowCount() > 0) {
                echo "  Добавлен топик №{$topic->id}: {$topic->title}" . PHP_EOL;
            }
        }
    }

    public function boardScan($path, $pattern)
    {
        $all_files = scandir($path);
        $files = preg_grep($pattern, $all_files);
        echo "Поиск файлов форумов по паттерну $pattern. Найдено файлов - " . count($files) . PHP_EOL;
        foreach ($files as $item) {
            preg_match($pattern, $item, $search);
            $this->board($path . trim($item), intval($search[1]));
        }
    }

    public function topic($file, $topic_id)
    {
        echo 'Обработка файла топика: ' . $file . PHP_EOL;
        $topic = new \FSA\SMF\Topic;
        $topic->loadFromFile($file);

        $stmt = $this->pdo->prepare('SELECT id FROM topics WHERE id=?');
        $stmt->execute([$topic_id]);
        if (!$stmt->fetchColumn()) {
            $topic_info = $topic->getTopicInfo();
            $stmt = $this->pdo->prepare('INSERT INTO topics (id, board_id, title) VALUES (:id, :board_id, :title)');
            $stmt->execute($topic_info);
            echo "  Добавлен топик №{$topic_info['id']}: {$topic_info['title']}" . PHP_EOL;
        }

        $messages = $topic->getTopicMessages();
        // Users
        $users = [];
        foreach ($messages as $message) {
            if ($message->member_id) {
                $users[$message->member_id] = ['name' => $message->poster_name];
            }
        }
        $this->upsertUsers($users);
        $stmt = $this->pdo->prepare('INSERT INTO messages (id, topic_id, posted, member_id, subject, poster_name, body, icon) VALUES (:id, :topic_id, :posted, :member_id, :subject, :poster_name, :post, :icon) ON CONFLICT (id) DO NOTHING');
        echo "  Найдено сообщений: " . count($messages) . PHP_EOL;
        foreach ($messages as $message) {
            $obj = get_object_vars($message);
            $obj['topic_id'] = $topic_id;
            $stmt->execute($obj);
            if ($stmt->rowCount() > 0) {
                echo "  Добавлено сообщение от {$message->posted} №{$message->id}, пользователь {$message->poster_name}" . PHP_EOL;
            }
        }
    }

    public function topicScan($path, $pattern)
    {
        $all_files = scandir($path);
        $files = preg_grep($pattern, $all_files);
        echo "Поиск файлов топиков по паттерну $pattern. Найдено файлов - " . count($files) . PHP_EOL;
        foreach ($files as $item) {
            preg_match($pattern, $item, $search);
            $this->topic($path . trim($item), intval($search[1]));
        }
    }

    public function upsertUsers(array $users)
    {
        $stmt = $this->pdo->prepare('INSERT INTO members (id, name) VALUES (?,?) ON CONFLICT (id) DO UPDATE SET name=EXCLUDED.name WHERE members.name IS NULL AND EXCLUDED.name IS NOT NULL');
        foreach ($users as $id => $user) {
            $stmt->execute([$id, $user['name']]);
            if ($stmt->rowCount() > 0) {
                echo "  Добавлен пользователь №{$id}: {$user['name']}" . PHP_EOL;
            }
        }
    }
}
