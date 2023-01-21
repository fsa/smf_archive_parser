<?php

namespace FSA\SMF;

class Tools
{
    public static function getUserIdFromUrl($url): int
    {
        parse_str(parse_url(html_entity_decode($url), PHP_URL_QUERY), $user_src);
        if (isset($user_src['action'])) {
            return intval(trim($user_src['action'], 'profile;u='));            
        }
        throw new Exception('Не найден id пользователя в URL: ' . $url);
    }

    public static function getBoardIdFromUrl($url): int
    {
        if (!preg_match('/board[,=](\d+)\.(\d+)/', $url, $match)) {
            throw new Exception('Не найден id форума для URL: ' . $url);
        }
        $board = $match[1];
        return intval($board);
    }

    public static function getTopicIdFromUrl($url): int
    {
        if (!preg_match('/topic[,=](\d+)\.(msg)?(\d+)/', $url, $match)) {
            throw new Exception('Не найден id топика для URL: ' . $url);
        }
        $topic = $match[1];
        return intval($topic);
    }

    public static function getDatetimeFromText($text)
    {
        $en_date = str_replace(['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'], ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'], $text);
        return date('c', strtotime($en_date));
    }

    public static function quoteReplace($text)
    {
        $post = preg_replace('/\<div class\="quoteheader"\>\<div class\="topslice_quote"\>\<a href\="http\:\/\/www\.club2u\.ru\/index\.php\/topic,(\d+)\.msg(\d+)\.html\#msg(\d+)"\>Цитата: (\S* от \d+ \S* \d+\, \d+:\d+:\d+)\<\/a\>\<\/div\>\<\/div\>\<blockquote class\="bbc_standard_quote"\>/', '<blockquote msg_id="$2" topic_id="$1" quote="$4">', $text);
        // Отличается от первого URL
        $post = preg_replace('/\<div class\="quoteheader"\>\<div class\="topslice_quote"\>\<a href\="http\:\/\/www\.club2u\.ru\/index\.php\?topic\=(\d+)\.msg(\d+)#msg(\d+)"\>Цитата: (\S* от \d+ \S* \d+\, \d+:\d+:\d+)\<\/a\>\<\/div\>\<\/div\>\<blockquote class\="bbc_standard_quote"\>/', '<blockquote msg_id="$2" topic_id="$1" quote="$4">', $post);
        $post = preg_replace('/\<div class\="quoteheader"\>\<div class\="topslice_quote"\>Цитата: (\S*)\<\/div\>\<\/div\>\<blockquote class\="bbc_standard_quote"\>/', '<blockquote quote="$1">', $post);
        $post = preg_replace('/\<div class\="quoteheader"\>\<div class\="topslice_quote"\>Цитировать\<\/div\>\<\/div\>\<blockquote class\="bbc_standard_quote"\>/', '<blockquote>', $post);

        $post = preg_replace('/\<\/blockquote\>\<div class\="quotefooter"\>\<div class\="botslice_quote"\>\<\/div\>\<\/div\>/', '</blockquote>', $post);
        
        return $post;
    }
}