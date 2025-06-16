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
        $summary = $this->generateSummary();

        $message = (new MailMessage)
            ->subject('Search Console Daily Report - '.now()->format('Y-m-d'))
            ->greeting('Search Console Report Summary')
            ->line('Here is your daily Search Console performance summary for the last 30 days:')
            ->line('')
            ->line('**Overall Performance:**')
            ->line('• Total Clicks: '.number_format($summary['totalClicks']))
            ->line('• Total Impressions: '.number_format($summary['totalImpressions']))
            ->line('• Average CTR: '.number_format($summary['averageCtr'], 2).'%')
            ->line('• Average Position: '.number_format($summary['averagePosition'], 1))
            ->line('')
            ->line('**Site Breakdown:**');

        foreach ($summary['sites'] as $siteUrl => $siteData) {
            $message->line('')
                ->line("**{$siteUrl}:**")
                ->line('  • Clicks: '.number_format($siteData['clicks']))
                ->line('  • Impressions: '.number_format($siteData['impressions']))
                ->line('  • CTR: '.number_format($siteData['ctr'], 2).'%')
                ->line('  • Avg Position: '.number_format($siteData['position'], 1));
        }

        $message->line('')
            ->line('Report generated on '.now()->format('Y-m-d H:i:s T'))
            ->salutation('Best regards, Your Search Console Monitor');

        return $message;
    }

    /**
     * Generate summary statistics from the report data.
     */
    private function generateSummary(): array
    {
        if (! is_array($this->reportData) && ! is_object($this->reportData)) {
            return [
                'totalClicks' => 0,
                'totalImpressions' => 0,
                'averageCtr' => 0,
                'averagePosition' => 0,
                'sites' => [],
            ];
        }

        $totalClicks = 0;
        $totalImpressions = 0;
        $totalCtr = 0;
        $totalPosition = 0;
        $siteCount = 0;
        $sites = [];

        try {
            foreach ($this->reportData as $siteUrl => $siteData) {
                if (! is_object($siteData) && ! is_array($siteData)) {
                    continue;
                }

                $siteClicks = 0;
                $siteImpressions = 0;
                $siteCtr = 0;
                $sitePosition = 0;
                $rowCount = 0;

                if (isset($siteData->rows) && is_array($siteData->rows)) {
                    foreach ($siteData->rows as $row) {
                        if (! is_object($row)) {
                            continue;
                        }

                        $siteClicks += $row->clicks ?? 0;
                        $siteImpressions += $row->impressions ?? 0;
                        $siteCtr += $row->ctr ?? 0;
                        $sitePosition += $row->position ?? 0;
                        $rowCount++;
                    }
                }

                $sites[$siteUrl] = [
                    'clicks' => $siteClicks,
                    'impressions' => $siteImpressions,
                    'ctr' => $rowCount > 0 ? ($siteCtr / $rowCount) * 100 : 0,
                    'position' => $rowCount > 0 ? $sitePosition / $rowCount : 0,
                ];

                $totalClicks += $siteClicks;
                $totalImpressions += $siteImpressions;
                $totalCtr += $sites[$siteUrl]['ctr'];
                $totalPosition += $sites[$siteUrl]['position'];
                $siteCount++;
            }
        } catch (\Exception $e) {
            Log::error('Error generating Search Console report summary: '.$e->getMessage());

            return [
                'totalClicks' => 0,
                'totalImpressions' => 0,
                'averageCtr' => 0,
                'averagePosition' => 0,
                'sites' => [],
            ];
        }

        return [
            'totalClicks' => $totalClicks,
            'totalImpressions' => $totalImpressions,
            'averageCtr' => $siteCount > 0 ? $totalCtr / $siteCount : 0,
            'averagePosition' => $siteCount > 0 ? $totalPosition / $siteCount : 0,
            'sites' => $sites,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $summary = $this->generateSummary();

        return [
            'summary' => $summary,
            'generated_at' => now()->toISOString(),
        ];
    }
}
