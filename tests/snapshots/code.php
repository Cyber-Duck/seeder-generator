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
        // insert into `users` (`email`, `first_name`, `last_name`, `updated_at`, `created_at`) values ('duilio.palacios@cyber-duck.co.uk', 'Duilio', 'Palacios', '2021-05-19 06:21:50', '2021-05-19 06:21:50')
        // created: User:1
        $user1 = \User::create([
            'email' => 'duilio.palacios@cyber-duck.co.uk',
            'first_name' => 'Duilio',
            'last_name' => 'Palacios',
        ]);
        
        // delete from `posts` where `author_id` = 2
        \Post::query()
            ->where('author_id', '=', 2)
            ->delete();
        
        // update `events` set `starts_at` = '2021-05-21 09:12:00', `ends_at` = '2021-05-19 09:12:00' where `id` = 3
        \Event::query()
            ->where('id', '=', $event3->getKey())
            ->update([
                'starts_at' => '2021-05-21 09:12:00',
                'ends_at' => '2021-05-19 09:12:00',
            ]);
    }
}
