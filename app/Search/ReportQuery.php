<?php

namespace App\Search;

use Revolution\Google\SearchConsole\Query\AbstractQuery;

class ReportQuery extends AbstractQuery
{
    public function init(): void
    {
        $this->setStartDate(now()->subDays(30)->toDateString());
        $this->setEndDate(now()->toDateString());
        $this->setDimensions(['date']);
    }
}
