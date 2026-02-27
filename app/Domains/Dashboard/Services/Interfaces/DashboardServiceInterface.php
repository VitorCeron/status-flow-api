<?php

namespace App\Domains\Dashboard\Services\Interfaces;

use App\Models\User;

interface DashboardServiceInterface
{
    public function summary(User $user): array;
}
