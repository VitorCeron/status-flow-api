<?php

namespace App\Domains\Backoffice\Users\Repositories;

use App\Domains\Backoffice\Users\Repositories\Interfaces\BackofficeUsersRepositoryInterface;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class BackofficeUsersRepository implements BackofficeUsersRepositoryInterface
{
    /**
     * @param User $model
     */
    public function __construct(
        private readonly User $model,
    ) {}

    /**
     * @inheritdoc
     */
    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator
    {
        $isDeleted = $filters['is_deleted'] ?? null;

        return $this->model
            ->withTrashed()
            ->when($isDeleted === true,  fn (Builder $q) => $q->whereNotNull('deleted_at'))
            ->when($isDeleted === false, fn (Builder $q) => $q->whereNull('deleted_at'))
            ->where('role', RoleEnum::USER->value)
            ->when(
                isset($filters['search']),
                fn (Builder $q) => $q->where(
                    fn (Builder $inner) => $inner
                        ->where('name', 'LIKE', "%{$filters['search']}%")
                        ->orWhere('email', 'LIKE', "%{$filters['search']}%")
                )
            )
            ->paginate($perPage);
    }

    /**
     * @inheritdoc
     */
    public function findById(string $id): ?User
    {
        return $this->model
            ->withTrashed()
            ->where('role', RoleEnum::USER->value)
            ->find($id);
    }
}
