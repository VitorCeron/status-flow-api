<?php

namespace App\Domains\Monitor\Services;

use App\Domains\Monitor\Repositories\Interfaces\MonitorRepositoryInterface;
use App\Domains\Monitor\Services\Interfaces\MonitorServiceInterface;
use App\Exceptions\Monitor\MonitorNotFoundException;
use App\Exceptions\Monitor\UnauthorizedMonitorAccessException;
use App\Models\Monitor;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class MonitorService implements MonitorServiceInterface
{
    /**
     * @param MonitorRepositoryInterface $monitorRepository
     */
    public function __construct(
        private readonly MonitorRepositoryInterface $monitorRepository,
    ) {}

    /**
     * @inheritDoc
     */
    public function index(User $user, int $perPage, array $filters = []): LengthAwarePaginator
    {
        return $this->monitorRepository->paginateByUserId($user->id, $perPage, $filters);
    }

    /**
     * @inheritDoc
     */
    public function create(User $user, array $data): Monitor
    {
        $data['user_id'] = $user->id;

        return $this->monitorRepository->create($data);
    }

    /**
     * @inheritDoc
     */
    public function show(User $user, string $id): Monitor
    {
        $monitor = $this->monitorRepository->findById($id);

        if (! $monitor) {
            throw new MonitorNotFoundException();
        }

        if ($monitor->user_id !== $user->id) {
            throw new UnauthorizedMonitorAccessException();
        }

        return $monitor;
    }

    /**
     * @inheritDoc
     */
    public function update(User $user, string $id, array $data): Monitor
    {
        $monitor = $this->monitorRepository->findById($id);

        if (!$monitor) {
            throw new MonitorNotFoundException();
        }

        if ($monitor->user_id !== $user->id) {
            throw new UnauthorizedMonitorAccessException();
        }

        return $this->monitorRepository->update($monitor, $data);
    }

    /**
     * @inheritDoc
     */
    public function delete(User $user, string $id): void
    {
        $monitor = $this->monitorRepository->findById($id);

        if (!$monitor) {
            throw new MonitorNotFoundException();
        }

        if ($monitor->user_id !== $user->id) {
            throw new UnauthorizedMonitorAccessException();
        }

        $this->monitorRepository->delete($monitor);
    }
}
