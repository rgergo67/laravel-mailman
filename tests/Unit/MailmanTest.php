<?php

namespace Rgergo67\LaravelMailman\Tests\Unit;

use Rgergo67\LaravelMailman\Mailman;
use rgergo67\LaravelMailman\Tests\BaseTestCase;

class MailmanTest extends BaseTestCase
{
    /** @test */
    public function testLists()
    {
        $body = file_get_contents(__DIR__.'/../Mocks/Lists/test-list-body.txt');
        $mailman = $this->getMailman([[200, $body]]);
        $lists = $mailman->lists();
        $this->assertCount(2, $lists);
    }

    /** @test */
    public function testMembers()
    {
        $body = file_get_contents(__DIR__.'/../Mocks/Members/membership-body.txt');
        $mailman = $this->getMailman([[200, $body]]);
        $membership = $mailman->membership('test@localhost.com');
        $this->assertCount(1, $membership);
    }
}
