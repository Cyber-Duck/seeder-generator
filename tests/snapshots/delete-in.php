<?php

namespace Database\Seeders;

use App\Helpers\CreatesRecords;
use Illuminate\Database\Seeder;

class BasicSeeder extends Seeder
{
    use CreatesRecords;

    public function run()
    {
        $this->create();
    }

    private function createRecord(array $attributes = [])
    {
        // delete from `posts` where `status` in ('draft', 'archived')
        \Post::query()
            ->whereIn('status', ['draft', 'archived'])
            ->delete();
    }
}
