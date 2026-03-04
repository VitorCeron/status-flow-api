<?php

namespace App\Domains\Dashboard\Services\Interfaces;

use App\Models\User;

interface DashboardServiceInterface
{
    /**
     *
     * @param User $user
     * @return array
     */
    public function summary(User $user): array;
}
