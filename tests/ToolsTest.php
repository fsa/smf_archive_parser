<?php

use FSA\SMF\Tools;
use PHPUnit\Framework\TestCase;

final class ToolsTest extends TestCase
{
    public function testGetUserIdFromUrl()
    {
        $this->assertEquals(2, Tools::getUserIdFromUrl('http://www.club2u.ru/index.php?action=profile;u=2'));
        $this->assertEquals(2, Tools::getUserIdFromUrl('http://www.club2u.ru/index.php?PHPSESSID=d3044cc04cc926f9c9ee6fab90c992bb&amp;action=profile;u=2'));
        $this->assertEquals(2, Tools::getUserIdFromUrl('http://www.club2u.ru/index.php?PHPSESSID=d3044cc04cc926f9c9ee6fab90c992bb&amp;action=profile;u=2&amp;test=1'));
    }

    public function testGetBoardIdFromUrl()
    {
        $this->assertEquals(31, Tools::getBoardIdFromUrl('http://www.club2u.ru/index.php/board,31.0.html?PHPSESSID=d3044cc04cc926f9c9ee6fab90c992bb'));
        $this->assertEquals(31, Tools::getBoardIdFromUrl('http://www.club2u.ru/index.php/board,31.0.html'));
        $this->assertEquals(31, Tools::getBoardIdFromUrl('http://www.club2u.ru/index.php?board=31.0'));
    }

    public function testGetTopicIdFromUrl()
    {
        $this->assertEquals(900, Tools::getTopicIdFromUrl('http://www.club2u.ru/index.php/topic,900.msg25767.html?PHPSESSID=d3044cc04cc926f9c9ee6fab90c992bb#new'));
        $this->assertEquals(900, Tools::getTopicIdFromUrl('http://www.club2u.ru/index.php/topic,900.msg25767.html'));
        $this->assertEquals(900, Tools::getTopicIdFromUrl('http://www.club2u.ru/index.php/topic,900.0.html'));
        $this->assertEquals(900, Tools::getTopicIdFromUrl('http://www.club2u.ru/index.php?topic=900.0'));
    }

    public function testGetDatetimeFromText()
    {
        $this->assertEquals(date('c', strtotime('2010-03-01 15:54:11')), Tools::getDatetimeFromText('01 Март 2010, 15:54:11'));
        $this->assertEquals(date('c', strtotime('2016-08-10 08:41:52')), Tools::getDatetimeFromText('10 Август 2016, 08:41:52'));
    }
}
