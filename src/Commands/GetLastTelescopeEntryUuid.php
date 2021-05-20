<?php

namespace CyberDuck\Seeder\Commands;

use Illuminate\Console\Command;
use Laravel\Telescope\Storage\EntryModel;

class GetLastTelescopeEntryUuid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cyber-duck:telescope:last-entry-uuid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the last telescope entry UUID';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $entry = EntryModel::latest()->first();

        if ($entry == null) {
            $this->line("There are no entries in Telescope!");
            return 0;
        }

        $this->line("The last entry uuid is: <info>{$entry->uuid}</info> ({$entry->created_at})");

        return 0;
    }
}
