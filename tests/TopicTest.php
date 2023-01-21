<?php

use FSA\SMF\Topic;
use PHPUnit\Framework\TestCase;

final class TopicTest extends TestCase
{

    private $topic = [];

    protected function setUp(): void
    {
        $this->topic[266] = new Topic;
        $this->topic[266]->loadFromFile(__DIR__ . '/archive/index.php?topic=266.0');

        $this->topic[326] = new Topic;
        $this->topic[326]->loadFromFile(__DIR__ . '/archive/index.php?topic=326.0');

        $this->topic[311] = new Topic;
        $this->topic[311]->loadFromFile(__DIR__ . '/archive/index.php?topic=311.msg19476');

        $this->topic[722] = new Topic;
        $this->topic[722]->loadFromFile(__DIR__ . '/archive/topic,722.0.html');
        // archive/club2u.ru/index.php/topic,467.0.html
    }

    public function testGetTopicInfo()
    {
        $excepted = [
            266=>
            [
                'id' => 266,
                'board_id' => 8,
                'title' => "Бета-версия нового Ю-кабинета."
            ],
            311 =>
            [
                'id' => 311,
                'board_id' => 14,
                'title' => "Настройка торрентов"
            ],
            326 =>
            [
                'id' => 326,
                'board_id' => 24,
                'title' => "Встреча форумчан"
            ],
            722 =>
            [
                'id' => 722,
                'board_id' => 5,
                'title' => "Новый плейлист - 2010!"
            ],
        ];
        foreach ($this->topic as $id=>$topic) {
            $result = $topic->getTopicInfo();
            $this->assertEquals($excepted[$id], $result, 'Пост №'.$id);
        }
    }

    public function testGetTopicMessages()
    {
        $result266 = $this->topic[266]->getTopicMessages();
        $this->assertIsArray($result266);
        $this->assertGreaterThan(0, count($result266));
        foreach ($result266 as $item) {
            $this->assertObjectHasAttribute('id', $item);
        }

        $result311 = $this->topic[311]->getTopicMessages();
        $this->assertIsArray($result311);
        $this->assertGreaterThan(0, count($result311));
        foreach ($result311 as $item) {
            $this->assertObjectHasAttribute('id', $item);
        }

        $result326 = $this->topic[326]->getTopicMessages();
        $this->assertIsArray($result326);
        $this->assertGreaterThan(0, count($result326));
        foreach ($result326 as $item) {
            $this->assertObjectHasAttribute('id', $item);
        }
        //$this->assertEquals('<blockquote quote="Ваня">место</blockquote>пусть будет Сосьва))<br><blockquote quote="Ваня">время</blockquote>первая суббота после ближайшей пятницы, 13', $result326[2]->post);

        $result722 = $this->topic[722]->getTopicMessages();
        $this->assertIsArray($result722);
        $this->assertGreaterThan(0, count($result722));
        foreach ($result722 as $item) {
            $this->assertObjectHasAttribute('id', $item);
        }
    }
}
