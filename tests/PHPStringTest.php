<?php

namespace JansenFelipe\PHPString;

use Carbon\Carbon;
use JansenFelipe\PHPString\Test\Event;
use PHPUnit_Framework_TestCase;

class PHPStringTest extends PHPUnit_Framework_TestCase
{
    public function testToObject()
    {
        $parser = new PHPString('JansenFelipe\PHPString\Test\Event');
        $event = $parser->toObject("BH Bike Show        20160621002000Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce consequat augue at hendrerit posuere.");

        $this->assertEquals('BH Bike Show', $event->name);
        $this->assertEquals('20160621', $event->date->format('Ymd'));
        $this->assertEquals(20, $event->price);
        $this->assertEquals('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce consequat augue at hendrerit posuere.', $event->description);
    }

    public function testToString()
    {
        $event = new Event();
        $event->name = 'Motocross Adventure';
        $event->description = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce consequat augue at hendrerit posuere.';
        $event->date = Carbon::createFromFormat('Y-m-d', '2016-06-21');
        $event->price = 1200.98;

        $parser = new PHPString('JansenFelipe\PHPString\Test\Event');
        $this->assertEquals('Motocross Adventure 20160621120098Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce consequat augue at hendrerit posuere.', $parser->toString($event));
    }
}