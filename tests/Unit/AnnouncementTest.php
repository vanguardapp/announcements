<?php

use Tests\TestCase;
use Vanguard\Announcements\Announcement;

uses(TestCase::class);

test('test parsed body', function () {
    $announcement = new Announcement([
        'title' => 'foo',
        'body' => '# test',
    ]);

    $this->assertEquals("<h1>test</h1>\n", (string) $announcement->parsed_body);
});
