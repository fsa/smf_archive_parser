<?php

namespace FSA\SMF;

use Symfony\Component\DomCrawler\Crawler;

class Topic
{
    private $dom;

    public function loadFromFile($file)
    {
        $this->dom = new Crawler(file_get_contents($file));
    }

    public function getTopicInfo()
    {
        $nav = $this->dom->filter('#linktree_upper');
        if (count($nav) == 0) {
            // Другая навигация
            $nav = $this->dom->filter('div > div.nav')->filter('a');
            preg_match_all('/board[,=](\d+)\.0/', $nav->eq(count($nav) - 2)->attr('href'), $board_match);
            $board_id = intval($board_match[1][0]);
            $title = $nav->eq(count($nav) - 1)->html();
        } else {
            $a = $nav->filter('a');
            preg_match_all('/board[,=](\d+)\.0/', $a->eq(count($a) - 2)->attr('href'), $board_match);
            $board_id = intval($board_match[1][count($board_match[1]) - 1]);
            $title = $a->last()->text();
        }
        return [
            'id' => intval($this->dom->filter('input[name="topic"]')->attr('value')),
            'board_id' => $board_id,
            'title' => $title
        ];
    }

    public function getTopicMessages()
    {
        $posts_forum = $this->dom->filter('#forumposts');
        if (count($posts_forum) == 0) {
            return $this->getTopicMessagesV2();
        }
        $data = [];
        $posts_forum->filter('form')->children()->each(function($msg, $i) use (&$data) {
            if (count($msg->filter('div.poster')) == 0) {
                return;
            }
            $post = $this->getTopicData($msg);
            if ($post) {
                array_push($data, $post);
            }
        });
        return $data;
    }

    private function getTopicData($post_dom)
    {
        $post_post = $post_dom->filter('.post');
        $post_inner = $post_post->filter('.inner');
        $post = preg_replace('/\<div class\="quoteheader"\>\<div class\="topslice_quote"\>\<a href\="http\:\/\/www\.club2u\.ru\/index\.php\/topic,(\d+)\.msg(\d+)\.html\#msg(\d+)"\>Цитата: (.*)\<\/a\>\<\/div\>\<\/div\>\<blockquote class\="bbc_standard_quote"\>(.*)\<\/blockquote\>\<div class\="quotefooter"\>\<div class\="botslice_quote"\>\<\/div\>\<\/div>/', '<blockquote msg_id="$2" topic_id="$1" quote="$4">$5</blockquote>', $post_inner->html());
        $post = preg_replace('/\<div class\="quoteheader"\>\<div class\="topslice_quote"\>Цитата: (.*)\<\/div\>\<\/div\>\<blockquote class\="bbc_standard_quote"\>(.*)\<\/blockquote\>\<div class\="quotefooter"\>\<div class\="botslice_quote"\>\<\/div\>\<\/div>/', '<blockquote quote="$1">$2</blockquote>', $post);
        // Отличается от первого URL
        $post = preg_replace('/\<div class\="quoteheader"\>\<div class\="topslice_quote"\>\<a href\="http\:\/\/www\.club2u\.ru\/index\.php\?topic\=(\d+)\.msg(\d+)#msg(\d+)"\>Цитата: (.*)\<\/a\>\<\/div\>\<\/div\>\<blockquote class\="bbc_standard_quote"\>(.*)\<\/blockquote\>\<div class\="quotefooter"\>\<div class\="botslice_quote"\>\<\/div\>\<\/div>/', '<blockquote msg_id="$2" topic_id="$1" quote="$4">$5</blockquote>', $post);
        $keyinfo_dom = $post_dom->filter('.postarea')->filter('.keyinfo');
        $icon = basename($post_dom->filter('.postarea')->filter('.keyinfo')->filter('img')->attr('src'));
        preg_match('/« \<strong\>(.*) \:\<\/strong\> (.*) »/', $keyinfo_dom->filter('.smalltext')->html(), $post_info);
        $posted = Tools::getDatetimeFromText($post_info[2]);
        $subject = $keyinfo_dom->filter('h5')->filter('a')->html();

        $poster_h4 = $post_dom->filter('div')->filter('.poster')->filter('h4');
        $poster_a = $poster_h4->filter('a');
        if (count($poster_a) > 0) {
            $poster = trim($poster_a->html());
            $member_id = Tools::getUserIdFromUrl($poster_a->attr('href'));
        } else {
            $poster = trim($poster_h4->html());
            $member_id = null;
        }
        if ($poster == '') {
            $poster = null;
        }
        $result = [
            'id' => intval(trim($post_inner->attr('id'), 'msg_')),
            'post' => $post,
            'poster_name' => $poster,
            'member_id' => $member_id,
            'icon' => trim($icon, '.gif'),
            'posted' => $posted,
            'subject' => $subject
        ];
        return (object)$result;
    }

    private function getTopicMessagesV2()
    {
        $data = [];
        $this->dom->filter('#quickModForm')->children('table > tr > td')->each(function ($posts) use (&$data) {
            $posts->children('table > tr > td')->each(function ($post) use (&$data) {
                if (count($post->children('table')) == 0) {
                    return;
                }
                $data[] = $this->getTopicDataV2($post);
            });
        });
        return $data;
    }

    private function getTopicDataV2($post_dom){
        $table = $post_dom->children('table');
        $user_el = $table->filter('tr')->filter('td')->filter('a');
        $post = $table->filter('.post')->html();
        //TODO: цитаты
        $icon = basename($table->filter('tr')->filter('td')->eq(1)->filter('table')->filter('tr')->filter('td')->filter('img')->attr('src'));
        $subject = $table->filter('tr')->filter('td')->eq(1)->filter('table')->filter('tr')->filter('td')->eq(1)->filter('div > a')->html();
        $id = trim($table->filter('tr')->filter('td')->eq(1)->filter('table')->filter('tr')->filter('td')->eq(1)->filter('div')->attr('id'), 'subject_');
        preg_match('/: (.*) »/', $table->filter('tr')->filter('td')->eq(1)->filter('table')->filter('tr')->filter('td')->eq(1)->filter('div')->eq(1)->text(), $post_info);
        $posted = Tools::getDatetimeFromText($post_info[1]);
        return (object) [
            'id' => intval($id),
            'post' => $post,
            'poster_name' => $user_el->text(),
            'member_id' => Tools::getUserIdFromUrl($user_el->attr('href')),
            'icon' => trim($icon, '.gif'),
            'posted' => $posted,
            'subject' => $subject
        ];
    }

}
