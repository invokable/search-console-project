<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Revolution\Google\SearchConsole\Facades\SearchConsole;

class SearchConsoleReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sc:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $sites = SearchConsole::listSites();

        foreach ($sites->siteEntry as $site) {
            $this->info("Site: {$site->siteUrl} - {$site->permissionLevel}");
        }
    }
}
