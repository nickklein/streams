<?php

namespace NickKlein\Streams\Commands;

use App\Models\User;
use App\Services\LogsService;
use App\Services\PushoverService;
use Illuminate\Console\Command;
use NickKlein\Streams\Services\StreamService;

class NotifyFavouritesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:stream-favourites';

    /**
     * The console Clean up user related things.
     *
     * @var string
     */
    protected $description = 'notify stream favourites';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private StreamService $streamService, private PushoverService $pushoverService, private LogsService $log)
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
        // TODO: Clean up necessary? 
        $user = User::find(1);
        $userId = 1; // Base user
        // Get all favourite handles
        $handles = $this->streamService->getAllHandleIds($user->id, 1);

        // Get the profiles + live content from the api for each handle
        $profiles = array_map(
            fn($handle) => $this->streamService->getProfile($user->id, $handle['id']),
            $handles
        );

        // Filter out offline profiles
        $profiles = $this->streamService->filterIsLiveStatus($profiles);

        $usersToNotify = [];
        // Loop through profiles, check if we already sent a notification for them today, if not send one.
        foreach ($profiles as $profile) {
            if (!$this->log->doesLogExistLast24Hours(['description' => "%{$profile['name']}%"], $user->timezone)) {
                $usersToNotify[] = $profile['name'];
                $this->log->handle("notify.mobile.stream", $profile['name']);
            }
        }

        if (!empty($usersToNotify)) {
            $this->pushoverService->send(
                'Users are currently streaming: ' . implode(', ', $usersToNotify),
                'Streaming now'
            );
        }
    }
}
