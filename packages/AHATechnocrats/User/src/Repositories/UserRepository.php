<?php

namespace AHATechnocrats\User\Repositories;

use AHATechnocrats\Core\Eloquent\Repository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class UserRepository extends Repository
{
    /**
     * Searchable fields
     */
    protected $fieldSearchable = [
        'name',
        'email',
        'status',
        'view_permission',
        'role_id',
    ];

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'AHATechnocrats\User\Contracts\User';
    }

    /**
     * This function will return user ids of current user's groups
     *
     * @return array
     */
    public function getCurrentUserGroupsUserIds()
    {
        $userIds = $this->scopeQuery(function ($query) {
            return $query->select('users.*')
                ->leftJoin('user_groups', 'users.id', '=', 'user_groups.user_id')
                ->leftJoin('groups', 'user_groups.group_id', 'groups.id')
                ->whereIn('groups.id', auth()->guard('user')->user()->groups()->pluck('id'));
        })->get()->pluck('id')->toArray();

        return $userIds;
    }

    public function updateProfileImage(int $userId, ?UploadedFile $file = null, bool $removeWhenMissing = false): void
    {
        $user = $this->findOrFail($userId);

        if ($file) {
            if ($user->image) {
                Storage::delete($user->image);
            }

            $user->update(['image' => $file->store('admins/'.$userId)]);

            return;
        }

        if ($removeWhenMissing && $user->image) {
            Storage::delete($user->image);
            $user->update(['image' => null]);
        }
    }

    public function profileImageFromRequest(string $field): ?UploadedFile
    {
        $files = request()->file($field);

        if (! $files) {
            return null;
        }

        $file = Arr::first(Arr::wrap($files));

        return $file instanceof UploadedFile ? $file : null;
    }
}
