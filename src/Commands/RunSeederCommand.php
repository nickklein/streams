<?php

namespace NickKlein\Streams\Commands;

use Illuminate\Console\Command;
use NickKlein\Streams\Seeders\StreamerSeeder;
use NickKlein\Streams\Seeders\StreamHandleSeeder;
use NickKlein\Streams\Seeders\UserStreamHandleSeeder;

class RunSeederCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:stream-seeder';

    /**
     * The console Clean up user related things.
     *
     * @var string
     */
    protected $description = 'Runs Seeder for Streams';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(public StreamerSeeder $streamerSeeder, public StreamHandleSeeder $streamHandleSeeder, public UserStreamHandleSeeder $userStreamHandleSeeder)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->streamerSeeder->run();
        $this->streamHandleSeeder->run();
        $this->userStreamHandleSeeder->run();
    }
}
