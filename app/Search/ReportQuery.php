<?php

namespace App\Search;

use Revolution\Google\SearchConsole\Query\AbstractQuery;

class ReportQuery extends AbstractQuery
{
    public function init(): void
    {
        // Data is updated with a 3-day delay.
        // Fetch 7 days of data: from 10 to 3 days ago.
        // Ensures only complete data is included.
        $this->setStartDate(now()->subDays(10)->toDateString());
        $this->setEndDate(now()->subDays(3)->toDateString());
        $this->setDimensions(['date']);
        $this->setRowLimit(1000);
        $this->setStartRow(0);
    }
}
