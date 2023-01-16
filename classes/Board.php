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
        $tbody = $this->dom->find('#messageindex')->find('.boardsframe')->find('tbody');
        if (count($tbody) == 0) {
            return null;
        }
        $contents = $tbody->find('tr');
        $data = [];
        foreach ($contents as $tr) {
            $td = $tr->find('td');
            switch (count($td)) {
                case 1:
                    break;
                case 4:
                    # Проверить
                    break;
                case 7:
                    $topic = $this->getBoardData($td);
                    if ($topic) {
                        $data[] = $topic;
                    }
                    break;
                default:
                    throw new Exception('Неверное число элементов td: ' . count($td));
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
        $result['id'] = Tools::getTopicIdFromUrl($a_title->getAttribute('href'));
        $result['sticky'] = count($td[2]->find('img.floatright'))>0;
        # 3
        $a_user = $td[3]->find('a');
        if (count($a_user) > 0) {
            $result['user_id'] = Tools::getUserIdFromUrl($a_user->getAttribute('href'));
            $result['username'] = $a_user->innerHtml;
        } else {
            $result['user_id'] = null;
            $result['username'] = trim($td[3]->innerHtml);
        }
        # 4
        $result['num_replies'] = intval($td[4]->innerHtml);
        # 5
        $result['num_views'] = intval($td[5]->innerHtml);
        # 6
        $date_span = $td[6]->find('.smalltext')->innerHtml;
        $a_user = $td[6]->find('.smalltext')->find('a');
        if (count($a_user) > 0) {
            $result['updated_member_id'] = Tools::getUserIdFromUrl($a_user->getAttribute('href'));
            $result['updated_member_name'] = $a_user->innerHtml;
        } else {
            $result['updated_member_id'] = null;
            $result['updated_member_name'] = trim(trim(explode('<br />', $date_span, 2)[1], 'от'));
        }
        $date = explode('<br />', $date_span, 2)[0];
        $result['last_modified'] = Tools::getDatetimeFromText($date);
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
            $id = Tools::getBoardIdFromUrl($href->getAttribute('href'));
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
        $id = Tools::getBoardIdFromUrl($td_icon->getAttribute('href'));
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
}
