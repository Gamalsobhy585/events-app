<?php

namespace App\Http\Controllers;

use App\Services\Interface\UserServiceInterface;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\StoreDetailRequest;
use App\Http\Requests\UpdateDetailRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Detail;
use App\Traits\ResponseTrait;
use App\Http\Resources\DetailResource;
use App\Events\UserSaved;

class UserController extends Controller
{
    use ResponseTrait;
    protected $userService;
    protected $user;
    protected $detail;

    public function __construct(UserServiceInterface $userService, User $user, Detail $detail)
    {
        $this->userService = $userService;
        $this->user = $user;
        $this->detail = $detail;
    }

    public function index()
    {
        $users = $this->user->with('details')->paginate(10);
        return $this->returnDataWithPagination(
            'Users retrieved successfully', 
            200, 
            UserResource::collection($users)
        );
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->user->create($request->validated());
        event(new UserSaved($user)); 
        
        return $this->returnData(
            'User created successfully', 
            201, 
            new UserResource($user->load('details'))
        );
    }

    public function show(User $user)
    {
        return $this->returnData(
            'User retrieved successfully', 
            200, 
            new UserResource($user->load('details'))
        );
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());
        event(new UserSaved($user)); 
        
        return $this->returnData(
            'User updated successfully', 
            200, 
            new UserResource($user->fresh()->load('details'))
        );
    }

    public function destroy(User $user)
    {
        $user->delete();
        return $this->success('User deleted successfully', 200);
    }

    public function indexDetails(User $user)
    {
        $details = $this->userService->getUserDetails($user);
        return $this->returnDataWithPagination(
            'Details retrieved successfully', 
            200, 
            DetailResource::collection($details)
        );
    }

    public function storeDetail(StoreDetailRequest $request, User $user)
    {
        $detail = $this->userService->createUserDetail($user, $request->validated());
        return $this->returnData(
            'Detail created successfully', 
            201, 
            new DetailResource($detail)
        );
    }

    public function showDetail(User $user, Detail $detail)
    {
        if ($detail->user_id !== $user->id) {
            return $this->returnErrorNotAbort('Detail not found for this user', 404);
        }

        $userDetail = $this->userService->getUserDetail($user, $detail->id);
        return $this->returnData(
            'Detail retrieved successfully', 
            200, 
            new DetailResource($userDetail)
        );
    }

    public function updateDetail(UpdateDetailRequest $request, User $user, Detail $detail)
    {
        if ($detail->user_id !== $user->id) {
            return $this->returnErrorNotAbort('Detail not found for this user', 404);
        }

        $updatedDetail = $this->userService->updateUserDetail($detail, $request->validated());
        return $this->returnData(
            'Detail updated successfully', 
            200, 
            new DetailResource($updatedDetail)
        );
    }

    public function destroyDetail(User $user, Detail $detail)
    {
        if ($detail->user_id !== $user->id) {
            return $this->returnErrorNotAbort('Detail not found for this user', 404);
        }

        $this->userService->deleteUserDetail($detail);
        return $this->success('Detail deleted successfully', 200);
    }
}