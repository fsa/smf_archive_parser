<?php

use FSA\SMF\Topic;
use PHPUnit\Framework\TestCase;

final class TopicTest extends TestCase
{

    private $topic266;
    private $topic311;

    protected function setUp(): void
    {
        $this->topic266 = new Topic;
        $this->topic266->loadFromFile(__DIR__ . '/archive/index.php?topic=266.0');

        $this->topic311 = new Topic;
        $this->topic311->loadFromFile(__DIR__ . '/archive/index.php?topic=311.msg19476');

        // archive/club2u.ru/index.php/topic,467.0.html
    }

    public function testGetTopicInfo()
    {
        $result266 = $this->topic266->getTopicInfo();
        $this->assertEquals(
            [
                'id' => 266,
                'board_id' => 8,
                'title' => "Бета-версия нового Ю-кабинета."
            ],
            $result266
        );

        $result311 = $this->topic311->getTopicInfo();
        $this->assertEquals(
            [
                'id' => 311,
                'board_id' => 14,
                'title' => "Настройка торрентов"
            ],
            $result311
        );
    }

    public function testGetTopicMessages()
    {
        $result266 = $this->topic266->getTopicMessages();
        $this->assertIsArray($result266);
        $this->assertGreaterThan(0, count($result266));

        $result311 = $this->topic311->getTopicMessages();
        $this->assertIsArray($result311);
        $this->assertGreaterThan(0, count($result311));
    }
}
