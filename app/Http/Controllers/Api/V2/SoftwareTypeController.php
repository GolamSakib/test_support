<?php

namespace App\Http\Controllers\Api\V2;

use Cache;
use App\Models\SoftwareType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class SoftwareTypeController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/v2/software/type/index",
     *     tags={"Software type"},
     *     summary="Software Type",
     *     @OA\Parameter(
     *      name="software_type",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *     @OA\Response(response="200", description="An example endpoint")
     * )
     */

    public function index(Request $request)
    {
        if ($request->has('page')){
            $page=$request->page;
        }else{
            $page=1;
        }
        $softwareTypes =SoftwareType::with(['software'])->get();
        return JsonDataResponse($softwareTypes);


    }



    /**
     * @OA\Post(
     *     path="/api/v2/software/type/store",
     *     tags={"Software type"},
     *     summary="Software Type",
     *     @OA\Parameter(
     *      name="software_type",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *     @OA\Response(response="200", description="An example endpoint")
     * )
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'software_type' => 'required|string|unique:software_types,software_type',
            ]);

            $software = SoftwareType::create($validatedData);

            return response()->json(['message' => 'Software type created successfully', 'data' => $software], 201);
        } catch (ValidationException $e) {
            // Handle validation errors, including unique constraint violation
            return response()->json(['error' => $e->errors()], 422);
        }
    }

    /**
     * @OA\post(
     *     path="/api/v2/software/type/update/{id}",
     *     tags={"Software type"},
     *     summary="Update Software type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Update Software type",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *       @OA\Parameter(
     *      name="software_type",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Response(
     *         response=200,
     *         description="Update Software type",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Update Software not type",
     *         @OA\JsonContent()
     *     ),
     * )
     */

    public function update(Request $request, $id)
    {
        $software = SoftwareType::find($id);
        $validatedData = $request->validate([
            'software_type' => 'required|string|unique:software_types,software_type',
        ]);

        $software->software_type = $validatedData['software_type'];
        $software->save();
        return updateDataResponse($software);
    }

    /**
     * @OA\get(
     *     path="/api/v2/software/type/delete/{id}",
     *     tags={"Software type"},
     *     summary="Delete Software type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Delete Software type",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Delete Software type",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Delete Software not type",
     *         @OA\JsonContent()
     *     ),
     * )
     */



    public function destroy($id)
    {
        $software = SoftwareType::find($id);
        $delete =  $software->delete();
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        return deleteDataResponse($delete);
    }
}
