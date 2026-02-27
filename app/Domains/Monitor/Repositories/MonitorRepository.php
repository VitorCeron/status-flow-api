<?php

namespace App\Domains\Monitor\Repositories;

use App\Domains\Monitor\Repositories\Interfaces\MonitorRepositoryInterface;
use App\Models\Monitor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class MonitorRepository implements MonitorRepositoryInterface
{
    /**
     * @var Monitor
     */
    protected $model;

    /**
     * @param Monitor $model
     */
    public function __construct(Monitor $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function paginateByUserId(string $userId, int $perPage, array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $userId)
            ->when(isset($filters['status']),    fn (Builder $q) => $q->where('status',    $filters['status']))
            ->when(isset($filters['is_active']), fn (Builder $q) => $q->where('is_active', $filters['is_active']))
            ->when(isset($filters['method']),    fn (Builder $q) => $q->where('method',    $filters['method']))
            ->paginate($perPage);
    }

    /**
     * @inheritDoc
     */
    public function findById(string $id): ?Monitor
    {
        return $this->model->find($id);
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): Monitor
    {
        return $this->model->create($data)->fresh();
    }

    /**
     * @inheritDoc
     */
    public function update(Monitor $monitor, array $data): Monitor
    {
        $monitor->update($data);

        return $monitor->fresh();
    }

    /**
     * @inheritDoc
     */
    public function delete(Monitor $monitor): void
    {
        $monitor->delete();
    }
}
