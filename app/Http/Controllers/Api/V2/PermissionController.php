<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\PermissionSection;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v2/permissions",
     *     tags={"Permissions"},
     *     summary="Problem Type",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Parameter(
     *      name="title",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Show all Data.")
     *         )
     *     ),
     * )
     * )
     */
    public function index()
    {
        $permission = Permission::orderBy('section','asc')->get();
        return JsonDataResponse($permission);

    }


    /**
     * @OA\Post(
     *     path="/api/v2/permissions",
     *     tags={"Permissions"},
     *     summary="Permission Store",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Parameter(
     *      name="title",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Data saved successfully.")
     *         )
     *     ),
     * )
     * )
     */
    public function store(Request $request)
    {
        // dd($request->permissions);

        $request->validate([
            'name' => 'required',
            'section' => 'required',
        ]);
        $permission = Permission::create(['name' => $request->name, 'section' => $request->section]);
        return saveDataResponse($permission);


    }

    /**
     * @OA\Get(
     *     path="/api/v2/permissioms/{id}",
     *     tags={"Permissions"},
     *     summary="Permission List",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Parameter(
     *      name="title",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Edit Data.")
     *         )
     *     ),
     * )
     * )
     */

    public function edit(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        return JsonDataResponse($permission);
    }

    /**
     * @OA\Put(
     *     path="/api/v2/permissions/{id}",
     *     tags={"Permissions"},
     *     summary="Permission List",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Parameter(
     *      name="title",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Data Update successfully.")
     *         )
     *     ),
     * )
     * )
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $permission->name = $request->name;
        $permission->section = $request->section;
        $permission->save();
        return updateDataResponse($permission);
    }


    /**
     * @OA\delete(
     *     path="/api/v2/permissions/{id}",
     *     tags={"Permissions"},
     *     summary="Delete Permission",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Parameter(
     *      name="title",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Data delete successfully.")
     *         )
     *     ),
     * )
     * )
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $delete =  Permission::destroy($id);
        return deleteDataResponse($delete);
    }


    /**
     * @OA\Get(
     *     path="/api/v2/permissions/permission/section",
     *     tags={"Permission Section"},
     *     summary="Section List",
     *     security={ {"bearerAuth": {} }},
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Show All Section List.")
     *         )
     *     ),
     * )
     * )
     */
    public function permissionsection()
    {
        $permissonSection = PermissionSection::with('permissions')->where('is_active', '=', 1)->get();
        return JsonDataResponse($permissonSection);
    }


}
