<?php

namespace App\Http\Controllers\Backoffice\Users;

use App\Domains\Backoffice\Users\Services\Interfaces\BackofficeUsersServiceInterface;
use App\Exceptions\Backoffice\UserNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Users\ListBackofficeUsersRequest;
use App\Http\Resources\Backoffice\Users\BackofficeUserResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BackofficeUsersController extends Controller
{
    /**
     * @param BackofficeUsersServiceInterface $usersService
     */
    public function __construct(
        private readonly BackofficeUsersServiceInterface $usersService,
    ) {}

    /**
     * @param ListBackofficeUsersRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(ListBackofficeUsersRequest $request): AnonymousResourceCollection
    {
        try {
            $users = $this->usersService->index($request->perPage(), $request->filters());
        } catch (Exception $e) {
            abort(500, 'Internal server error.');
        }

        return BackofficeUserResource::collection($users);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = $this->usersService->show($id);
        } catch (UserNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error.'], 500);
        }

        return (new BackofficeUserResource($user))
            ->response()
            ->setStatusCode(200);
    }
}
