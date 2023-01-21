<?php

namespace FSA\SMF;

use Symfony\Component\DomCrawler\Crawler;

class Board
{
    private $dom;

    public function loadFromFile($file)
    {
        $this->dom = new Crawler(file_get_contents($file));
    }

    public function getBoardTopics()
    {
        $tbody = $this->dom->filter('#messageindex')->filter('.boardsframe')->filter('tbody');
        if (count($tbody) == 0) {
            return null;
        }
        $data = [];
        $tbody->filter('tr')->each(function ($tr) use (&$data) {
            $td = $tr->filter('td');
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
        });
        return $data;
    }

    private function getBoardData($td)
    {
        # 0
        $result['post_type_img'] = basename($td->eq(0)->filter('img')->attr('src'));
        # 1
        $result['img'] = basename($td->eq(1)->filter('img')->attr('src'));
        # 2
        $a_title = $td->eq(2)->filter('a');
        $result['title'] = $a_title->html();
        $result['id'] = Tools::getTopicIdFromUrl($a_title->attr('href'));
        $result['sticky'] = count($td->eq(2)->filter('img.floatright'))>0;
        # 3
        $a_user = $td->eq(3)->filter('a');
        if (count($a_user) > 0) {
            $result['user_id'] = Tools::getUserIdFromUrl($a_user->attr('href'));
            $result['username'] = $a_user->html();
        } else {
            $result['user_id'] = null;
            $result['username'] = trim($td->eq(3)->html());
        }
        # 4
        $result['num_replies'] = intval($td->eq(4)->html());
        # 5
        $result['num_views'] = intval($td->eq(5)->html());
        # 6
        $date_span = $td->eq(6)->filter('.smalltext')->html();
        $a_user = $td->eq(6)->filter('.smalltext')->filter('a');
        if (count($a_user) > 0) {
            $result['updated_member_id'] = Tools::getUserIdFromUrl($a_user->attr('href'));
            $result['updated_member_name'] = $a_user->html();
        } else {
            $result['updated_member_id'] = null;
            $result['updated_member_name'] = trim(trim(explode('<br>', $date_span, 2)[1], 'от'));
        }
        $date = explode('<br>', $date_span, 2)[0];
        $result['last_modified'] = Tools::getDatetimeFromText($date);
        return (object)$result;
    }

    public function getBoardsCategories()
    {
        $result = [];
        $this->dom->filter('.categoryframe')->each(function ($category) use (&$result){
            $h3_el = $category->filter('h3');
            $id = intval(trim($h3_el->filter('a')->attr('id'), 'c'));
            $name = trim(strip_tags($h3_el->html()));
            $result[$id] = $name;
        });
        return $result;
    }

    public function getBoards()
    {
        $result = [];
        $this->dom->filter('.categoryframe')->each(function ($category) use (&$result){
            $h3_el = $category->filter('h3');
            $cat_id = intval(trim($h3_el->filter('a')->attr('id'), 'c'));
            $root_id = null;
            $category->filter('table')->eq(0)->filter('tr')->each(function ($board) use (&$result, &$root_id, $cat_id) {
                $td_parts = $board->filter('td');
                switch (count($td_parts)) {
                    case 1:
                        $result = array_replace($result, $this->getSubBoards($td_parts->eq(0), $root_id, $cat_id));
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
            });
        });
        return $result;
    }

    private function getSubBoards($el, $root_id, $cat_id): array
    {
        $result = [];
        $el->filter('a')->each(function ($href) use (&$result, $root_id, $cat_id) {
            $id = Tools::getBoardIdFromUrl($href->attr('href'));
            $result[$id] = (object)[
                'parent_id' => $root_id,
                'category_id' => $cat_id,
                'name' => $href->html(),
            ];
        });
        return $result;
    }

    private function getBoardFromTd($el, $cat_id): array
    {
        $td_icon = $el->eq(0)->filter('a');
        $id = Tools::getBoardIdFromUrl($td_icon->attr('href'));
        preg_match('/(\d+) Сообщений (\d+) Тем/', $el->eq(2)->text(), $counts);
        return [
            $id =>
            (object)
            [
                'parent_id' => null,
                'category_id' => $cat_id,
                'name' => $el->eq(1)->filter('a')->html(),
                'description' => $el->eq(1)->filter('p')->html(),
                'num_topics' => $counts[2],
                'num_posts' => $counts[1],
            ]
        ];
    }
}
