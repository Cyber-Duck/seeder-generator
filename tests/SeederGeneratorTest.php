<?php

namespace Tests;

use CyberDuck\Seeder\EloquentGenerator;
use CyberDuck\Seeder\Entry;
use CyberDuck\Seeder\SeederGenerator;
use Laravel\Telescope\Storage\EntryModel;

class SeederGeneratorTest extends TestCase
{
    protected SeederGenerator $seederGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seederGenerator = $this->app->make(SeederGenerator::class);
    }

    /** @test */
    function it_generates_eloquent_code()
    {
        $code = $this->seederGenerator->generateCode('BasicSeeder', collect([
            new Entry(
                "insert into `users` (`email`, `first_name`, `last_name`, `updated_at`, `created_at`) values ('duilio.palacios@cyber-duck.co.uk', 'Duilio', 'Palacios', '2021-05-19 06:21:50', '2021-05-19 06:21:50')",
                (new EntryModel)->forceFill(['content' => ['model' => 'User:1', 'action' => 'created']])
            ),
            new Entry("delete from `posts` where `author_id` = 2"),
            new Entry("update `events` set `starts_at` = '2021-05-21 09:12:00', `ends_at` = '2021-05-19 09:12:00' where `id` = 3"),
        ]));

        $this->assertSnapshotMatches('code', $code);
    }

    /** @test */
    function parses_in_expressions()
    {
        $code = $this->seederGenerator->generateCode('BasicSeeder', collect([
            new Entry("delete from `posts` where `status` in ('draft', 'archived')"),
        ]));

        $this->assertSnapshotMatches('delete-in', $code);
    }
}
