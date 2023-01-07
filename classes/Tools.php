<?php

namespace FSA\SMF;

class Tools
{
    public static function getUserIdFromUrl($url): int
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $user_src);
        if (!isset($user_src['action'])) {
            throw new Exception('Не найден id пользователя в URL: ' . $url);
        }
        return intval(trim($user_src['action'], 'profile;u='));
    }

    public static function getBoardIdFromUrl($url): int
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $url);
        if (!isset($url['board'])) {
            throw new Exception('Не найден id форума для URL: ' . $url);
        }
        $id_dot = explode('.', $url['board'], 2);
        return intval($id_dot[0]);
    }

    public static function getTopicIdFromUrl($url): int
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $url);
        if (!isset($url['topic'])) {
            throw new Exception('Не найден id топика для URL: ' . $url);
        }
        $id_dot = explode('.', $url['topic'], 2);
        return intval($id_dot[0]);
    }

}