<?php

namespace App\Search;

use Revolution\Google\SearchConsole\Query\AbstractQuery;

class ReportQuery extends AbstractQuery
{
    public function init(): void
    {
        $this->setStartDate(now()->subDays(7)->toDateString());
        $this->setEndDate(now()->toDateString());
        $this->setDimensions(['date']);
        $this->setRowLimit(1000);
        $this->setStartRow(0);
    }
}
