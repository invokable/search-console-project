<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SearchConsoleReportNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected array $reportData)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $dailyData = $this->generateDailyData();
        $markdownContent = $this->buildMarkdownContent($dailyData);

        return (new MailMessage)
            ->subject('Search Console Daily Report - '.now()->format('Y-m-d'))
            ->markdown('mail.report', [
                'markdownContent' => $markdownContent,
                'generatedAt' => now()->format('Y-m-d H:i:s T'),
            ]);
    }

    /**
     * Generate daily data from the report data.
     */
    private function generateDailyData(): array
    {
        if (! is_array($this->reportData) && ! is_object($this->reportData)) {
            return [];
        }

        $dailyData = [];

        try {
            foreach ($this->reportData as $siteUrl => $siteData) {
                if (! is_object($siteData) && ! is_array($siteData)) {
                    continue;
                }

                $dailyData[$siteUrl] = [];

                if (isset($siteData->rows) && is_array($siteData->rows)) {
                    foreach ($siteData->rows as $row) {
                        if (! is_object($row)) {
                            continue;
                        }

                        // Each row represents one day's data
                        $dailyData[$siteUrl][] = [
                            'date' => $row->keys[0] ?? 'Unknown', // date is the first key when dimension is 'date'
                            'clicks' => $row->clicks ?? 0,
                            'impressions' => $row->impressions ?? 0,
                            'ctr' => ($row->ctr ?? 0) * 100, // Convert to percentage
                            'position' => $row->position ?? 0,
                        ];
                    }
                }

                // Sort by date (newest first)
                usort($dailyData[$siteUrl], function ($a, $b) {
                    return strcmp($b['date'], $a['date']);
                });
            }
        } catch (\Exception $e) {
            Log::error('Error generating Search Console daily data: '.$e->getMessage());

            return [];
        }

        return $dailyData;
    }

    /**
     * Build markdown content with daily data tables.
     */
    private function buildMarkdownContent(array $dailyData): string
    {
        if (empty($dailyData)) {
            return 'No data available for the selected period.';
        }

        $content = '';

        foreach ($dailyData as $siteUrl => $siteRows) {
            $content .= "\n\n## {$siteUrl}\n\n";

            if (empty($siteRows)) {
                $content .= "No data available for this site.\n";

                continue;
            }

            // Create markdown table
            $content .= "| Date | Clicks | Impressions | CTR (%) | Avg Position |\n";
            $content .= "|------|--------|-------------|---------|---------------|\n";

            foreach ($siteRows as $row) {
                $content .= sprintf(
                    "| %s | %s | %s | %.2f | %.1f |\n",
                    $row['date'],
                    number_format($row['clicks']),
                    number_format($row['impressions']),
                    $row['ctr'],
                    $row['position']
                );
            }
        }

        return $content;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $dailyData = $this->generateDailyData();

        return [
            'daily_data' => $dailyData,
            'generated_at' => now()->toISOString(),
        ];
    }
}
