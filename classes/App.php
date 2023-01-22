<?php

namespace FSA\SMF;

class App
{
    private static $config;

    public static function getConvertor($database_url, $timezone = null): Convertor
    {
        $pdo = new PostgreSQL($database_url);
        if ($timezone) {
            echo "Используется часовой пояс $timezone" . PHP_EOL;
            $pdo->query("SET TIMEZONE=\"$timezone\"");
            date_default_timezone_set($timezone);
        }
        return new Convertor($pdo);
    }

    public static function getConfig($name, $default=null) {
        if (is_null(self::$config)) {
            self::$config = require __DIR__ . '/../settings.php';
        }
        if (isset(self::$config[$name])) {
            return self::$config[$name];
        }
        return $default;
    }
}
