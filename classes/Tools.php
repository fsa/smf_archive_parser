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
}