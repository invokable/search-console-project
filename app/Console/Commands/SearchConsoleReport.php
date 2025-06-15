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
    protected $description = 'Generate Search Console report using service account authentication';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if service account authentication is enabled
        if (!config('google.service.enable')) {
            $this->error('This command requires Google Search Console service account authentication.');
            $this->info('Please set GOOGLE_SERVICE_ENABLED=true in your environment variables.');
            return 1;
        }

        // Check if service account credentials are configured
        $serviceAccountCredentials = config('google.service.file');
        if (empty($serviceAccountCredentials) || !is_array($serviceAccountCredentials)) {
            $this->error('Google service account credentials are not properly configured.');
            $this->info('Please set GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION with valid JSON credentials.');
            $this->info('For GitHub Actions, store the JSON as a string in the environment variable.');
            return 1;
        }

        // Validate essential service account fields
        $requiredFields = ['type', 'project_id', 'private_key', 'client_email'];
        foreach ($requiredFields as $field) {
            if (empty($serviceAccountCredentials[$field])) {
                $this->error("Missing required service account field: {$field}");
                $this->info('Please ensure your service account JSON contains all required fields.');
                return 1;
            }
        }

        try {
            $this->info('Fetching Search Console sites...');
            $sites = SearchConsole::listSites();

            if (empty($sites->siteEntry)) {
                $this->warn('No sites found. Make sure your service account has been added as a user in Google Search Console.');
                return 0;
            }

            $this->info('Found ' . count($sites->siteEntry) . ' site(s):');
            foreach ($sites->siteEntry as $site) {
                $this->info("Site: {$site->siteUrl} - {$site->permissionLevel}");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to fetch Search Console data: ' . $e->getMessage());
            $this->info('Please verify:');
            $this->info('1. Your service account credentials are valid');
            $this->info('2. The service account email has been added to Google Search Console');
            $this->info('3. You have internet connectivity');
            return 1;
        }
    }
}
