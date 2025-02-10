<?php


namespace App\AppTrait;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

trait AuthTrait
{

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return self::getUser()->id;
    }

    /**
     * @return Authenticatable|null
     */
    public function getUser()
    {
        return Auth::user();
    }

    /**
     * @return mixed
     */
    public function getUserRoleId()
    {
        return $this->getUser()->role_id;
    }

    public function getUserRoles()
    {
        return $this->getUser()->getRoleNames();
    }

    /**
     * @param $permission
     * @return void
     */
    public function givePermissionTo($permission)
    {
        $this->getUser()->givePermissionTo($permission);
    }

    /**
     * @param $role
     * @return void
     */
    public function assignRole($role)
    {
        $this->getUser()->assignRole($role);
    }


    /**
     * @return mixed
     */
    public function getAllPermissions()
    {

        return $this->getUser()->getAllPermissions();
    }

    /**
     * @return mixed
     */
    public function getDirectPermissions()
    {

        return $this->getUser()->getDirectPermissions();
    }

    /**
     * @return mixed
     */
    public function getPermissionsViaRoles()
    {
        return $this->getUser()->getPermissionsViaRoles();
    }
}
