<?php

namespace FSA\SMF;

use PHPHtmlParser\Dom;

class Topic
{
    private $dom;

    public function loadFromFile($file)
    {
        $this->dom = new Dom();
        $this->dom->loadFromFile($file);
    }

    public function getTopicInfo()
    {
        $nav = $this->dom->find('#linktree_upper');
        if (count($nav) == 0) {
            // Другая навигация
            $nav = $this->dom->find('.nav')->find('a');
            preg_match_all('/board[,=](\d+)\.0/', $nav[count($nav) - 2]->getAttribute('href'), $board_match);
            $board_id = intval($board_match[1][count($board_match[1]) - 1]);
            $title = $nav[count($nav) - 1]->innerHtml;
        } else {
            $a_name = $nav->find('.last')->find('a');
            preg_match_all('/board[,=](\d+)\.0/', $nav->innerHtml, $board_match);
            $board_id = intval($board_match[1][count($board_match[1]) - 1]);
            $title = $a_name->find('span')->innerHtml;
        }
        return [
            'id' => intval($this->dom->find('input[name="topic"]')->getAttribute('value')),
            'board_id' => $board_id,
            'title' => $title
        ];
    }

    public function getTopicMessages()
    {
        $posts_forum = $this->dom->find('#forumposts');
        if (count($posts_forum) == 0) {
            return $this->getTopicMessagesV2();
        }
        $posts = $posts_forum->find('form');
        $data = [];
        foreach ($posts->getChildren() as $msg) {
            if (count($msg->find('div.poster')) == 0) {
                continue;
            }
            $post = $this->getTopicData($msg);
            if ($post) {
                array_push($data, $post);
            }
        }
        return $data;
    }

    private function getTopicMessagesV2()
    {
        $posts_table = $this->dom->find('#quickModForm')->find('table');
        $posts = $posts_table->getChildren();
        $data = [];
        foreach ($posts as $post) {
            //$a = $post->getChildren();
            echo $post->innerHtml . PHP_EOL . PHP_EOL;
        }
        return $data;
    }

    private function getTopicData($post_dom)
    {
        $post_post = $post_dom->find('.post');
        $post_inner = $post_post->find('.inner');
        $post = preg_replace('/\<div class\="quoteheader"\>\<div class\="topslice_quote"\>\<a href\="http\:\/\/www\.club2u\.ru\/index\.php\/topic,(\d+)\.msg(\d+)\.html\#msg(\d+)"\>Цитата: (.*)\<\/a\>\<\/div\>\<\/div\>\<blockquote class\="bbc_standard_quote"\>(.*)\<\/blockquote\>\<div class\="quotefooter"\>\<div class\="botslice_quote"\>\<\/div\>\<\/div>/', '<blockquote msg_id="$2" topic_id="$1" quote="$4">$5</blockquote>', $post_inner->innerHtml);
        $post = preg_replace('/\<div class\="quoteheader"\>\<div class\="topslice_quote"\>Цитата: (.*)\<\/div\>\<\/div\>\<blockquote class\="bbc_standard_quote"\>(.*)\<\/blockquote\>\<div class\="quotefooter"\>\<div class\="botslice_quote"\>\<\/div\>\<\/div>/', '<blockquote quote="$1">$2</blockquote>', $post);
        // Отличается от первого URL
        $post = preg_replace('/\<div class\="quoteheader"\>\<div class\="topslice_quote"\>\<a href\="http\:\/\/www\.club2u\.ru\/index\.php\?topic\=(\d+)\.msg(\d+)#msg(\d+)"\>Цитата: (.*)\<\/a\>\<\/div\>\<\/div\>\<blockquote class\="bbc_standard_quote"\>(.*)\<\/blockquote\>\<div class\="quotefooter"\>\<div class\="botslice_quote"\>\<\/div\>\<\/div>/', '<blockquote msg_id="$2" topic_id="$1" quote="$4">$5</blockquote>', $post);
        $keyinfo_dom = $post_dom->find('.postarea')->find('.keyinfo');
        $icon = basename($keyinfo_dom->find('img')->getAttribute('src'));
        preg_match('/&\#171; \<strong\>(.*) \:\<\/strong\> (.*) &\#187;/', $keyinfo_dom->find('.smalltext')->innerHtml, $post_info);
        $posted = Tools::getDatetimeFromText($post_info[2]);
        $subject = $keyinfo_dom->find('h5')->find('a')->innerHtml;

        $poster_h4 = $post_dom->find('div')->find('.poster')->find('h4');
        $poster_a = $poster_h4->find('a');
        if (count($poster_a) > 0) {
            $poster = trim($poster_a->innerHtml);
            $member_id = Tools::getUserIdFromUrl($poster_a->getAttribute('href'));
        } else {
            $poster = trim($poster_h4->innerHtml);
            $member_id = null;
        }
        if ($poster == '') {
            $poster = null;
        }
        $result = [
            'id' => intval(trim($post_inner->id, 'msg_')),
            'post' => $post,
            'poster_name' => $poster,
            'member_id' => $member_id,
            'icon' => trim($icon, '.gif'),
            'posted' => $posted,
            'subject' => $subject
        ];
        return (object)$result;
    }
}
