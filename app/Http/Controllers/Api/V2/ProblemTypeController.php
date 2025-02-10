<?php

namespace App\Http\Controllers\Api\V2;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use Cache;
use App\Models\ProblemType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class ProblemTypeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v2/problemtype/show",
     *     tags={"Problem Type"},
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
    public function index(Request $request)
    {

        $problemtype = ProblemType::orderBy('typeName','asc')->get();
        return response()->json([
            'success' => true,
            'data' => $problemtype,
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/problemtype/store",
     *     tags={"Problem Type"},
     *     summary="Problem Type List",
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
        // dd($request->all());
        $data = $this->validate($request, [
            // 'title' => 'required|unique:problem_types,title',
            'title' => 'required|unique:supportadmin_problemtype,typeName',
        ]);

        try {
            $problemType = ProblemType::create([
                'typeName' => $data['title']
            ]);

            if ($problemType) {
                Cache::forget('problems');
                return response()->json(['message' => 'Problem Type Created Successfull', 'data' => $data], 200);
            }
        } catch (ValidationException $e) {
            // Handle validation errors
            $errors = $e->errors();
            return response()->json(['errors' => $errors], 422);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/v2/problemtype/edit/{id}",
     *     tags={"Problem Type"},
     *     summary="Problem Type List",
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
    public function edit($id)

    {
        $problemtype = ProblemType::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Edit Successfully',
            'data' => $problemtype,
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/v2/problemtype/update/{id}",
     *     tags={"Problem Type"},
     *     summary="Problem Type List",
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
        try {
            $data = $this->validate($request, [
                'title' => [
                    'required',
                    Rule::unique('supportadmin_problemtype', 'typeName')->ignore($id, 'id'),
                ],
            ]);

            $problemtype = ProblemType::findOrFail($id);
            $problemtype->typeName = $data['title'];
            $problemtype->save();

            Artisan::call('cache:clear');
            Artisan::call('view:clear');

            return response()->json([
                'success' => true,
                'message' => 'Update Successfully',
                'data' => $problemtype,
            ], 200);

        } catch (ValidationException $e) {
            // Handle validation errors
            $errors = $e->errors();
            return response()->json(['errors' => $errors], 422);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }



    /**
     * @OA\Delete(
     *     path="/api/v2/problemtype/destroy/{id}",
     *     tags={"Problem Type"},
     *     summary="Problem Type List",
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
        $problemtype = ProblemType::find($id);
        $problemtype->delete();
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        return response()->json([
            'success' => true,
            'message' => 'Deleted Successfully',
            'data' => $problemtype,
        ], 200);
    }
}
