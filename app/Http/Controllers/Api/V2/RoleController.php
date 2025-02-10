<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
// use App\Models\Role;
use App\Models\RoleTranslation;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        /* $this->middleware(['permission:view_staff_roles'])->only('index');
        $this->middleware(['permission:add_staff_role'])->only('create');
        $this->middleware(['permission:edit_staff_role'])->only('edit');
        $this->middleware(['permission:delete_staff_role'])->only('destroy');*/
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return JsonDataResponse($roles);
    }

    public function rolesWithoutPagination()
    {
        $roles = Role::all();
        return JsonDataResponse($roles);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);
        $role = Role::create(['name' => $request->name,'guard_name'=>'web']);
        $role->givePermissionTo($request->permissions);
        $role_translation = RoleTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'role_id' => $role->id]);
        $role_translation->name = $request->name;
        return saveDataResponse($role_translation->save());
    }

    public function copy_role(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'roleName' => 'required',
        ]);
        $existing_role = Role::findByName($request->roleName); // Replace 'role_name' with the actual role name
        $permissions = $existing_role->permissions;
        $role = Role::create(['name' => $request->name]);
        $data = $role->givePermissionTo($permissions);
        $role_translation = RoleTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'role_id' => $role->id]);
        $role_translation->name = $request->name;
        return saveDataResponse($role_translation->save());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return JsonDataResponse($role);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        return JsonDataResponse($role);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->name = $request->name;
        $role->syncPermissions($request->permissions);
        $role->save();

        // Role Translation
        $role_translation = RoleTranslation::firstOrNew(['lang' => 1, 'role_id' => $role->id]);
        $role_translation->name = $request->name;
        return updateDataResponse($role_translation->save());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        /* dd($role->role_translations);
        foreach ($role->role_translations as $key => $role_translation) {
            $role_translation->delete();
        }*/
        $delete =  Role::destroy($id);
        return deleteDataResponse($delete);
    }

    public function add_permission(Request $request)
    {
        $request->validate([
            'role_id' => 'required',
            'permissions' => 'required',
        ]);

        $role = Role::findOrFail($request->role_id);
        $role->syncPermissions($request->permissions);
        $role->save();


        return JsonDataResponse($role);
    }

    public function create_admin_permissions()
    {
    }
}
