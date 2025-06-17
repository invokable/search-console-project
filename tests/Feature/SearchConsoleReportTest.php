<?php

namespace Tests\Feature;

use App\Notifications\SearchConsoleReportNotification;
use App\Search\ReportQuery;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Revolution\Google\SearchConsole\Facades\SearchConsole;
use Tests\TestCase;

class SearchConsoleReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up mail configuration for testing
        config(['mail.to.address' => 'test@example.com']);
        config(['mail.to.name' => 'Test User']);
    }

    /**
     * Test successful report generation with sites found.
     */
    public function test_generates_report_successfully_with_sites(): void
    {
        // Fake notifications to capture what's sent
        Notification::fake();

        // Mock the SearchConsole facade
        $mockSites = (object) [
            'siteEntry' => [
                (object) ['siteUrl' => 'https://example.com/'],
                (object) ['siteUrl' => 'https://test.com/'],
            ],
        ];

        $mockReportData = (object) [
            'rows' => [
                (object) [
                    'clicks' => 100,
                    'impressions' => 1000,
                    'ctr' => 0.1,
                    'position' => 5.5,
                ],
                (object) [
                    'clicks' => 50,
                    'impressions' => 500,
                    'ctr' => 0.1,
                    'position' => 3.2,
                ],
            ],
        ];

        SearchConsole::expects('listSites')
            ->once()
            ->andReturn($mockSites);

        SearchConsole::expects('query')
            ->twice()
            ->with(Mockery::type('string'), Mockery::type(ReportQuery::class))
            ->andReturn($mockReportData);

        // Run the command
        $this->artisan('sc:report')
            ->expectsOutput('Fetching Search Console sites...')
            ->expectsOutput('Found 2 site(s):')
            ->assertExitCode(0);

        // Assert notification was sent
        Notification::assertSentOnDemand(SearchConsoleReportNotification::class, function ($notification, $channels, $notifiable) {
            // Verify the notification has the expected report data
            $this->assertEquals(['mail'], $channels);
            $this->assertInstanceOf(\Illuminate\Notifications\AnonymousNotifiable::class, $notifiable);

            return true;
        });
    }

    /**
     * Test command behavior when no sites are found.
     */
    public function test_handles_no_sites_found(): void
    {
        // Fake notifications to ensure none are sent
        Notification::fake();

        // Mock SearchConsole to return empty sites
        $mockSites = (object) [
            'siteEntry' => [],
        ];

        SearchConsole::expects('listSites')
            ->once()
            ->andReturn($mockSites);

        // SearchConsole::query should not be called when no sites
        SearchConsole::shouldNotReceive('query');

        // Run the command
        $this->artisan('sc:report')
            ->expectsOutput('Fetching Search Console sites...')
            ->expectsOutput('No sites found. Make sure your service account has been added as a user in Google Search Console.')
            ->assertExitCode(0);

        // Assert no notifications were sent
        Notification::assertNothingSent();
    }

    /**
     * Test command behavior when no siteEntry property exists.
     */
    public function test_handles_missing_site_entry(): void
    {
        // Fake notifications to ensure none are sent
        Notification::fake();

        // Mock SearchConsole to return object without siteEntry
        $mockSites = (object) [];

        SearchConsole::expects('listSites')
            ->once()
            ->andReturn($mockSites);

        // SearchConsole::query should not be called
        SearchConsole::shouldNotReceive('query');

        // Run the command
        $this->artisan('sc:report')
            ->expectsOutput('Fetching Search Console sites...')
            ->expectsOutput('No sites found. Make sure your service account has been added as a user in Google Search Console.')
            ->assertExitCode(0);

        // Assert no notifications were sent
        Notification::assertNothingSent();
    }

    /**
     * Test command handles exceptions gracefully.
     */
    public function test_handles_exceptions_gracefully(): void
    {
        // Fake notifications to ensure none are sent
        Notification::fake();

        // Mock SearchConsole to throw an exception
        SearchConsole::expects('listSites')
            ->once()
            ->andThrow(new \Exception('API connection failed'));

        // SearchConsole::query should not be called
        SearchConsole::shouldNotReceive('query');

        // Run the command
        $this->artisan('sc:report')
            ->expectsOutput('Fetching Search Console sites...')
            ->expectsOutput('Failed to fetch Search Console data: API connection failed')
            ->expectsOutput('Please verify:')
            ->expectsOutput('1. Your service account credentials are valid')
            ->expectsOutput('2. The service account email has been added to Google Search Console')
            ->expectsOutput('3. You have internet connectivity')
            ->assertExitCode(1);

        // Assert no notifications were sent
        Notification::assertNothingSent();
    }

    /**
     * Test command handles exception during query phase.
     */
    public function test_handles_query_exception(): void
    {
        // Fake notifications to ensure none are sent
        Notification::fake();

        // Mock SearchConsole listSites to succeed
        $mockSites = (object) [
            'siteEntry' => [
                (object) ['siteUrl' => 'https://example.com/'],
            ],
        ];

        SearchConsole::expects('listSites')
            ->once()
            ->andReturn($mockSites);

        // Mock SearchConsole query to throw an exception
        SearchConsole::expects('query')
            ->once()
            ->with('https://example.com/', Mockery::type(ReportQuery::class))
            ->andThrow(new \Exception('Query failed'));

        // Run the command
        $this->artisan('sc:report')
            ->expectsOutput('Fetching Search Console sites...')
            ->expectsOutput('Found 1 site(s):')
            ->expectsOutput('Failed to fetch Search Console data: Query failed')
            ->expectsOutput('Please verify:')
            ->expectsOutput('1. Your service account credentials are valid')
            ->expectsOutput('2. The service account email has been added to Google Search Console')
            ->expectsOutput('3. You have internet connectivity')
            ->assertExitCode(1);

        // Assert no notifications were sent
        Notification::assertNothingSent();
    }

    /**
     * Test successful report generation with single site.
     */
    public function test_generates_report_with_single_site(): void
    {
        // Fake notifications to capture what's sent
        Notification::fake();

        // Mock the SearchConsole facade with single site
        $mockSites = (object) [
            'siteEntry' => [
                (object) ['siteUrl' => 'https://example.com/'],
            ],
        ];

        $mockReportData = (object) [
            'rows' => [
                (object) [
                    'clicks' => 200,
                    'impressions' => 2000,
                    'ctr' => 0.1,
                    'position' => 4.8,
                ],
            ],
        ];

        SearchConsole::expects('listSites')
            ->once()
            ->andReturn($mockSites);

        SearchConsole::expects('query')
            ->once()
            ->with('https://example.com/', Mockery::type(ReportQuery::class))
            ->andReturn($mockReportData);

        // Run the command
        $this->artisan('sc:report')
            ->expectsOutput('Fetching Search Console sites...')
            ->expectsOutput('Found 1 site(s):')
            ->assertExitCode(0);

        // Assert notification was sent
        Notification::assertSentOnDemand(SearchConsoleReportNotification::class);
    }

    /**
     * Test successful report generation with empty report data.
     */
    public function test_generates_report_with_empty_data(): void
    {
        // Fake notifications to capture what's sent
        Notification::fake();

        // Mock the SearchConsole facade
        $mockSites = (object) [
            'siteEntry' => [
                (object) ['siteUrl' => 'https://example.com/'],
            ],
        ];

        $mockReportData = (object) [
            'rows' => [],
        ];

        SearchConsole::expects('listSites')
            ->once()
            ->andReturn($mockSites);

        SearchConsole::expects('query')
            ->once()
            ->with('https://example.com/', Mockery::type(ReportQuery::class))
            ->andReturn($mockReportData);

        // Run the command
        $this->artisan('sc:report')
            ->expectsOutput('Fetching Search Console sites...')
            ->expectsOutput('Found 1 site(s):')
            ->assertExitCode(0);

        // Assert notification was sent even with empty data
        Notification::assertSentOnDemand(SearchConsoleReportNotification::class);
    }

    /**
     * Test that daily data is correctly formatted from 7 days of data.
     */
    public function test_formats_daily_data_correctly(): void
    {
        // Fake notifications to capture what's sent
        Notification::fake();

        // Mock the SearchConsole facade with 7 days of data
        $mockSites = (object) [
            'siteEntry' => [
                (object) ['siteUrl' => 'https://example.com/'],
            ],
        ];

        $mockReportData = (object) [
            'rows' => [
                (object) [
                    'keys' => ['2024-01-01'],
                    'clicks' => 100,
                    'impressions' => 1000,
                    'ctr' => 0.1,
                    'position' => 5.5,
                ],
                (object) [
                    'keys' => ['2024-01-02'],
                    'clicks' => 150,
                    'impressions' => 1200,
                    'ctr' => 0.125,
                    'position' => 4.2,
                ],
                (object) [
                    'keys' => ['2024-01-03'],
                    'clicks' => 80,
                    'impressions' => 900,
                    'ctr' => 0.089,
                    'position' => 6.1,
                ],
            ],
        ];

        SearchConsole::expects('listSites')
            ->once()
            ->andReturn($mockSites);

        SearchConsole::expects('query')
            ->once()
            ->with('https://example.com/', Mockery::type(ReportQuery::class))
            ->andReturn($mockReportData);

        // Run the command
        $this->artisan('sc:report')
            ->expectsOutput('Fetching Search Console sites...')
            ->expectsOutput('Found 1 site(s):')
            ->assertExitCode(0);

        // Assert notification was sent with daily data
        Notification::assertSentOnDemand(SearchConsoleReportNotification::class, function ($notification) {
            // Verify the notification contains daily data, not aggregated data
            $arrayData = $notification->toArray((object) []);

            $this->assertArrayHasKey('daily_data', $arrayData);
            $this->assertArrayHasKey('https://example.com/', $arrayData['daily_data']);

            $dailyData = $arrayData['daily_data']['https://example.com/'];
            $this->assertCount(3, $dailyData);

            // Verify the data is sorted by date (newest first)
            $this->assertEquals('2024-01-03', $dailyData[0]['date']);
            $this->assertEquals('2024-01-02', $dailyData[1]['date']);
            $this->assertEquals('2024-01-01', $dailyData[2]['date']);

            // Verify data structure
            $this->assertEquals(80, $dailyData[0]['clicks']);
            $this->assertEquals(900, $dailyData[0]['impressions']);
            $this->assertEquals(8.9, $dailyData[0]['ctr']); // CTR should be converted to percentage
            $this->assertEquals(6.1, $dailyData[0]['position']);

            return true;
        });
    }
}
