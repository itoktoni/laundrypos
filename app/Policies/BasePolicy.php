<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class BasePolicy
{
    protected $module;

    protected $restrict;

    public function __construct()
    {
        // ponytail: manual routes lack action 'name'; fall back to first
        // segment of route name so restrictions apply per module.
        $route = request()->route();
        $module = $route?->getAction('name');
        if (empty($module)) {
            $routeName = $route?->getName() ?? '';
            $module = $routeName !== '' ? explode('.', $routeName)[0] : null;
        }
        $this->module = $module;
        $this->restrict = config('permision');
    }

    private function accessProtected($user, $permision)
    {
        $role = $user->role ?? 'guest';

        if (isset($this->restrict[$role][$this->module])) {

            if (in_array($permision, $this->restrict[$role][$this->module])) {
                return true;
            }
        }

        return false;
    }

    public function save(User $user): Response
    {
        return $this->accessProtected($user, __FUNCTION__) ? Response::deny() : Response::allow();
    }

    public function create(User $user): Response
    {
        return $this->accessProtected($user, __FUNCTION__) ? Response::deny() : Response::allow();
    }

    public function update(User $user): Response
    {
        return $this->accessProtected($user, __FUNCTION__) ? Response::deny() : Response::allow();
    }

    public function table(User $user): Response
    {
        return $this->accessProtected($user, __FUNCTION__) ? Response::deny() : Response::allow();
    }

    public function delete(User $user): Response
    {
        return $this->accessProtected($user, __FUNCTION__) ? Response::deny() : Response::allow();
    }

    public function show(User $user): Response
    {
        return $this->accessProtected($user, __FUNCTION__) ? Response::deny() : Response::allow();
    }

    public function prepare(User $user): Response
    {
        return $this->accessProtected($user, __FUNCTION__) ? Response::deny() : Response::allow();
    }

    public function prepareSo(User $user): Response
    {
        return $this->accessProtected($user, __FUNCTION__) ? Response::deny() : Response::allow();
    }

    public function storeship(User $user): Response
    {
        return $this->accessProtected($user, __FUNCTION__) ? Response::deny() : Response::allow();
    }
}
