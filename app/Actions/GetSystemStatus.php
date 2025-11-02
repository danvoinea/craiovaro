<?php

namespace App\Actions;

use App\Services\SystemStatusService;

class GetSystemStatus
{
    public function __construct(
        protected SystemStatusService $statusService
    ) {}

    public function execute(): array
    {
        return $this->statusService->appInfo();
    }
}
