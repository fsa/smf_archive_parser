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
        parse_str(parse_url($url, PHP_URL_QUERY), $part);
        if (isset($part['board'])) {
            $id_dot = explode('.', $part['board'], 2);
            $board = $id_dot[0];
        } else {
            if (!preg_match('/http\:\/\/www\.club2u\.ru\/index\.php\/board,(.*)\.0\.html/', $url, $match)) {
                throw new Exception('Не найден id форума для URL: ' . $url);
            }
            $board = $match[0];
        }
        return intval($board);
    }

    public static function getTopicIdFromUrl($url): int
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $part);
        if (isset($part['topic'])) {
            $id_dot = explode('.', $part['topic'], 2);
            $topic = $id_dot[0];
        } else {
            if (!preg_match('/http\:\/\/www\.club2u\.ru\/index\.php\/topic,(.*)\.0\.html/', $url, $match)) {
                throw new Exception('Не найден id топика для URL: ' . $url);
            }
            $topic = $match[1];
        }    
        return intval($topic);
    }

    public static function getDatetimeFromText($text)
    {
        $en_date = str_replace(['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'], ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'], $text);
        return date('c', strtotime($en_date));
    }
}