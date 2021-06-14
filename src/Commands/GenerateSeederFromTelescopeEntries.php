<?php

namespace CyberDuck\Seeder\Commands;

use CyberDuck\Seeder\SeederGenerator;
use CyberDuck\Seeder\TelescopeExporter;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;

class GenerateSeederFromTelescopeEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cyber-duck:seeder:generate {class} {uuid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a seeder from telescope entries';

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
        Telescope::stopRecording();

        app(SeederGenerator::class)->generate($this->argument('class'), $this->queries());

        $this->line("Seeder <info>{$this->argument('class')}</info> generated!");

        return 0;
    }

    private function queries()
    {
        return app(TelescopeExporter::class)->getDMSAfter($this->argument('uuid'));
    }
}
