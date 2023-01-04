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
        $result['id'] = $this->getTopicIdFromUrl($a_title->getAttribute('href'));
        # 3
        $a_user = $td[3]->find('a');
        $result['user_id'] = $this->getUserIdFromUrl($a_user->getAttribute('href'));
        $result['username'] = $a_user->innerHtml;
        # 4
        $result['answers'] = intval($td[4]->innerHtml);
        # 5
        $result['views'] = intval($td[5]->innerHtml);
        # 6
        $result['field_6'] = $td[6]->innerHtml;

        return (object)$result;
    }

    public function getBoardsCategories()
    {
        $contents = $this->dom->find('.categoryframe');
        $result = [];
        foreach ($contents as $category) {
            $h3_el = $category->find('h3');
            $id = intval(trim($h3_el->find('a')->id, 'c'));
            $name = trim(strip_tags($h3_el->innerHtml));
            $result[$id] = $name;
        }
        return $result;
    }

    public function getBoards()
    {
        $contents = $this->dom->find('.categoryframe');
        $result = [];
        foreach ($contents as $category) {
            $h3_el = $category->find('h3');
            $cat_id = intval(trim($h3_el->find('a')->id, 'c'));
            $table = $category->find('table')[0]->find('tr');
            $root_id = null;
            foreach ($table as $board) {
                $td_parts = $board->find('td');
                switch (count($td_parts)) {
                    case 1:
                        $result = array_replace($result, $this->getSubBoards($td_parts[0], $root_id, $cat_id));
                        $root_id = null;
                        break;
                    case 4:
                        $board = $this->getBoardFromTd($td_parts, $cat_id);
                        $result = array_replace($result, $board);
                        $root_id = array_key_first($board);
                        break;
                    default:
                        throw new Exception('Неверное число столбцов в таблице');
                }
            }
        }
        return $result;
    }

    private function getSubBoards($el, $root_id, $cat_id): array
    {
        $result = [];
        foreach ($el->find('a') as $href) {
            $id = $this->getBoardIdFromUrl($href->getAttribute('href'));
            $result[$id] = (object)[
                'parent_id' => $root_id,
                'category_id' => $cat_id,
                'name' => $href->innerHtml,
            ];
        }
        return $result;
    }

    private function getBoardFromTd($el, $cat_id): array
    {
        $td_icon = $el[0]->find('a');
        $id = $this->getBoardIdFromUrl($td_icon->getAttribute('href'));
        return [
            $id =>
            (object)
            [
                'parent_id' => null,
                'category_id' => $cat_id,
                'name' => $el[1]->find('a')->innerHtml,
                'description' => $el[1]->find('p')->innerHtml,
            ]
        ];
    }

    private function getBoardIdFromUrl($url): int
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $url);
        if (!isset($url['board'])) {
            throw new Exception('Не найден id форума для URL: ' . $url);
        }
        $id_dot = explode('.', $url['board'], 2);
        return intval($id_dot[0]);
    }

    private function getTopicIdFromUrl($url): int
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $url);
        if (!isset($url['topic'])) {
            throw new Exception('Не найден id топика для URL: ' . $url);
        }
        $id_dot = explode('.', $url['topic'], 2);
        return intval($id_dot[0]);
    }

    private function getUserIdFromUrl($url): int
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $user_src);
        if (!isset($user_src['action'])) {
            throw new Exception('Не найден id пользователя в URL: ' . $url);
        }
        return intval(trim($user_src['action'], 'profile;u='));
    }
}
