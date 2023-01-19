<?php

use FSA\SMF\Board;
use PHPUnit\Framework\TestCase;

final class BoardTest extends TestCase
{
    private $index;
    private $board;

    protected function setUp(): void
    {
        $this->index = new Board;
        $this->index->loadFromFile(__DIR__ . '/archive/index.html');

        $this->board = new Board;
        $this->board->loadFromFile(__DIR__ . '/archive/board,2.0.html');
    }

    public function testGetBoardsCategories()
    {
        $result = $this->index->getBoardsCategories();
        //var_dump($result);
        $this->assertIsArray($result);
    }

    public function testGetBoards()
    {
        $result = $this->index->getBoards();
        //var_dump($result);
        $this->assertIsArray($result);
    }

    public function testGetBoardTopics()
    {
        $result = $this->board->getBoardTopics();
        //var_dump($result);
        $this->assertIsArray($result);
    }
}
