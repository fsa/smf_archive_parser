<?php

use FSA\SMF\Topic;
use PHPUnit\Framework\TestCase;

final class TopicTest extends TestCase
{

    private $topic;

    protected function setUp(): void
    {
        $this->topic = new Topic;
        $this->topic->loadFromFile(__DIR__.'/../archive/club2u.ru/index.php?topic=266.0');
    }

    public function testGetTopicInfo()
    {
        $this->assertEquals(
            ['id' =>266, 'board_id' =>8, 'title' => "Бета-версия нового Ю-кабинета."],
            $this->topic->getTopicInfo()
        );
    }

    public function testGetTopicMessages()
    {
        $result = $this->topic->getTopicMessages();
        $this->assertIsArray($result);
    }
}