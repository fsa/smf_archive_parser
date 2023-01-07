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
        $a_name = $this->dom->find('#linktree_upper')->find('.last')->find('a');
        return [
            'id' => intval($this->dom->find('input[name="topic"]')->getAttribute('value')),
            'title' => $a_name->find('span')->innerHtml
        ];
    }

    public function getTopicMessages()
    {
        $posts = $this->dom->find('#forumposts')->find('form');
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

    private function getTopicData($post_dom)
    {
        $post_inner = $post_dom->find('.post')->find('.inner');
        $post = preg_replace('/\<div class\="quoteheader"\>\<div class\="topslice_quote"\>\<a href\="http\:\/\/www\.club2u\.ru\/index\.php\/topic,(\d+)\.msg(\d+)\.html\#msg(\d+)"\>Цитата: (.*)\<\/a\>\<\/div\>\<\/div\>\<blockquote class\="bbc_standard_quote"\>(.*)\<\/blockquote\>\<div class\="quotefooter"\>\<div class\="botslice_quote"\>\<\/div\>\<\/div>/', '<blockquote msg_id="$2" topic_id="$1" quote="$4">$5</blockquote>', $post_inner->innerHtml);
        $post = preg_replace('/\<div class\="quoteheader"\>\<div class\="topslice_quote"\>Цитата: (.*)\<\/div\>\<\/div\>\<blockquote class\="bbc_standard_quote"\>(.*)\<\/blockquote\>\<div class\="quotefooter"\>\<div class\="botslice_quote"\>\<\/div\>\<\/div>/', '<blockquote quote="$1">$2</blockquote>', $post);

        $keyinfo_dom = $post_dom->find('.postarea')->find('.keyinfo');
        $icon = basename($keyinfo_dom->find('img')->getAttribute('src'));
        preg_match('/&\#171; \<strong\>(.*) \:\<\/strong\> (.*) &\#187;/', $keyinfo_dom->find('.smalltext')->innerHtml, $post_info);
        $posted = Tools::getDatetimeFromText($post_info[2]);
        $subject = $keyinfo_dom->find('h5')->find('a')->innerHtml;

        $poster_a = $post_dom->find('div')->find('.poster')->find('h4')->find('a');
        $result = [
            'id' => intval(trim($post_inner->id, 'msg_')),
            'post' => $post,
            'poster_name' => $poster_a->innerHtml,
            'member_id' => Tools::getUserIdFromUrl($poster_a->getAttribute('href')),
            'icon' => trim($icon, '.gif'),
            'posted' => $posted,
            'subject' => $subject
        ];
        return (object)$result;
    }
}
