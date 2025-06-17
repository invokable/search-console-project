<?php

namespace App\Search;

use Revolution\Google\SearchConsole\Query\AbstractQuery;

class ReportQuery extends AbstractQuery
{
    public function init(): void
    {
        // Actual data is reflected with a three-day delay.
        // Fetch 7 days of data: from 9 to 3 days ago.
        // Ensures only complete data is included.
        $this->setStartDate(now()->subDays(9)->toDateString());
        $this->setEndDate(now()->subDays(3)->toDateString());
        $this->setDimensions(['date']);
        $this->setRowLimit(1000);
        $this->setStartRow(0);
    }
}
