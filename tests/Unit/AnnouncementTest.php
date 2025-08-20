<?php

namespace Vanguard\Announcements\Tests\Unit;

use Tests\TestCase;
use Vanguard\Announcements\Announcement;

class AnnouncementTest extends TestCase
{
    
    public function test_testParsedBody()
    {
        $announcement = new Announcement([
            'title' => 'foo',
            'body' => '# test',
        ]);

        $this->assertEquals("<h1>test</h1>\n", (string) $announcement->parsed_body);
    }
}
