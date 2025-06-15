<?php

namespace Tests\Feature;

use Tests\TestCase;

class GitHubActionsIntegrationTest extends TestCase
{
    public function test_search_console_command_works_with_github_actions_environment(): void
    {
        // Simulate GitHub Actions environment variables as described in the issue
        putenv('GOOGLE_SERVICE_ENABLED=true');
        putenv('GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION={"type":"service_account","project_id":"test-project-123","private_key_id":"key123","private_key":"-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQC7VJTUt9Us8cKB\n-----END PRIVATE KEY-----\n","client_email":"test-service-account@test-project-123.iam.gserviceaccount.com","client_id":"123456789","auth_uri":"https://accounts.google.com/o/oauth2/auth","token_uri":"https://oauth2.googleapis.com/token","auth_provider_x509_cert_url":"https://www.googleapis.com/oauth2/v1/certs","client_x509_cert_url":"https://www.googleapis.com/robot/v1/metadata/x509/test-service-account%40test-project-123.iam.gserviceaccount.com"}');

        // Clear Laravel's config cache to pick up environment changes
        $this->app->make('config')->set('google.service.enable', true);
        $this->app->make('config')->set('google.service.file', json_decode(getenv('GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION'), true));

        // The command should now pass validation and attempt to make API calls
        // We expect it to fail with a network error, which means authentication is properly configured
        $this->artisan('sc:report')
            ->expectsOutput('Fetching Search Console sites...')
            ->assertExitCode(1); // Expected to fail due to network/API, not config

        // Clean up environment variables
        putenv('GOOGLE_SERVICE_ENABLED');
        putenv('GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION');
    }

    public function test_github_actions_missing_environment_variables(): void
    {
        // Ensure clean state
        putenv('GOOGLE_SERVICE_ENABLED');
        putenv('GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION');

        // Test scenario where GitHub Actions secrets are not configured
        putenv('GOOGLE_SERVICE_ENABLED=true');
        // Missing GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION

        // Clear Laravel's config cache
        $this->app->make('config')->set('google.service.enable', true);
        $this->app->make('config')->set('google.service.file', null);

        $this->artisan('sc:report')
            ->expectsOutput('Google service account credentials are not properly configured.')
            ->expectsOutput('Please set GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION with valid JSON credentials.')
            ->expectsOutput('For GitHub Actions, store the JSON as a string in the environment variable.')
            ->assertExitCode(1);

        // Clean up
        putenv('GOOGLE_SERVICE_ENABLED');
        putenv('GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION');
    }
}
