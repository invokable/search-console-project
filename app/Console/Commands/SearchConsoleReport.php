<?php

namespace App\Console\Commands;

use App\Notifications\SearchConsoleReportNotification;
use App\Search\ReportQuery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
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
    protected $description = 'Generate Search Console report using service account authentication';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Fetching Search Console sites...');
            $sites = SearchConsole::listSites();

            if (empty($sites->siteEntry)) {
                $this->warn('No sites found. Make sure your service account has been added as a user in Google Search Console.');

                return 0;
            }

            $this->info('Found '.count($sites->siteEntry).' site(s):');
            foreach ($sites->siteEntry as $site) {
                //$this->info("Site: $site->siteUrl - $site->permissionLevel");
                $query = SearchConsole::query($site->siteUrl, new ReportQuery());

                // Results are summarized and sent via email
                Notification::route('mail', [config('mail.from.address') => config('mail.from.name')])
                    ->notify(new SearchConsoleReportNotification($query));
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to fetch Search Console data: '.$e->getMessage());
            $this->info('Please verify:');
            $this->info('1. Your service account credentials are valid');
            $this->info('2. The service account email has been added to Google Search Console');
            $this->info('3. You have internet connectivity');

            return 1;
        }
    }
}
