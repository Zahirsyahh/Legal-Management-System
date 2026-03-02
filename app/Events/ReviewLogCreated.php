<?php

namespace App\Events;

use App\Models\ContractReviewLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewLogCreated
{
    use Dispatchable, SerializesModels;

    public ContractReviewLog $log;

    public function __construct(ContractReviewLog $log)
    {
        $this->log = $log;
    }
}
