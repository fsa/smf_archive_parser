<?php

namespace FSA\SMF;

class App
{
    private static $config;

    public static function getConvertor(): Convertor
    {
        $tz = self::getConfig('TIMEZONE', 'Asia/Novosibirsk');
        date_default_timezone_set($tz);
        echo "Используется часовой пояс $tz" . PHP_EOL;
        $pdo = new PostgreSQL(self::getConfig('DATABASE_URL'));
        $pdo->query("SET TIMEZONE=\"$tz\"");
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
