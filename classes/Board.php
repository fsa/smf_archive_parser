<?php

namespace FSA\SMF;

use PHPHtmlParser\Dom;

class Board
{
    private $dom;

    public function loadFromFile($file)
    {
        $this->dom = new Dom();
        $this->dom->loadFromFile($file);
    }

    public function getBoardTopics()
    {
        $contents = $this->dom->find('.boardsframe')->find('tbody')->find('tr');
        $data = [];
        foreach ($contents as $tr) {
            $td = $tr->find('td');
            switch (count($td)) {
                case 1:
                    break;
                case 7:
                    $topic = $this->getBoardData($td);
                    if ($topic) {
                        $data[] = $topic;
                    }
                    break;
                default:
                    throw new Exception('Неверное число элементов td');
            }
        }
        return $data;
    }

    private function getBoardData($td)
    {
        # 0
        $result['post_type_img'] = basename($td[0]->find('img')->getAttribute('src'));
        # 1
        $result['img'] = basename($td[1]->find('img')->getAttribute('src'));
        # 2
        $a_title = $td[2]->find('a');
        $result['title'] = $a_title->innerHtml;
        parse_str(parse_url($a_title->getAttribute('href'), PHP_URL_QUERY), $topic);
        if (!isset($topic['topic'])) {
            throw new Exception('Не найден id топика для URL: ' . $a_title->getAttribute('href'));
        }
        $id_dot = explode('.', $topic['topic'], 2);
        $id = intval($id_dot[0]);
        $result['id'] = $id;
        # 3
        $a_user = $td[3]->find('a');
        parse_str(parse_url($a_user->getAttribute('href'), PHP_URL_QUERY), $user_src);
        if (!isset($user_src['action'])) {
            throw new Exception('Не найден id пользователя: ' . $a_user);
        }
        $result['user_id'] = intval(trim($user_src['action'], 'profile;u='));
        $result['username'] = $a_user->innerHtml;
        # 4
        $result['answers'] = intval($td[4]->innerHtml);
        # 5
        $result['views'] = intval($td[5]->innerHtml);
        # 6
        $result['field_6'] = $td[6]->innerHtml;

        return (object)$result;
    }
}
