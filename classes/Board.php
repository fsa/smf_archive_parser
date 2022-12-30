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
        //var_dump($td[0]->innerHtml);
        //var_dump($td[1]->innerHtml);
        //var_dump($td[2]->innerHtml);
        //var_dump($td[3]->innerHtml);
        //var_dump($td[4]->innerHtml);
        //var_dump($td[5]->innerHtml);
        //var_dump($td[6]->innerHtml);
        $a = $td[2]->find('a');
        return [
            'title'=>$a->innerHtml,
            'url'=>$a->getAttribute('href'),
        ];
    }
}
