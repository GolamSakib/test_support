<?php

namespace App\Http\Controllers\Api\V2;

use Cache;
use App\Models\ProblemType;
use Illuminate\Http\Request;
use App\Models\ProblemSubType;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class ProblemSubTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     *     path="/api/v2/problem-sub-types",
     *     tags={"Problem Sub Type"},
     *     summary="Get Problem Sub Type",
     *     @OA\Parameter(
     *      name="title",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *         name="problem_type_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
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
         $problemTypes = Cache::remember('subproblems'.$page, 86400, function () {
             return ProblemSubType::with(['problem' => function ($query) {
                 $query->select('id', 'title');
             }])->select('id', 'title', 'problem_type_id')->paginate(12);
         });

         return JsonDataResponse($problemTypes);
     }





    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Post(
     *     path="/api/v2/problem-sub-types",
     *     tags={"Problem Sub Type"},
     *     summary="Store Problem Sub Type",
     *     @OA\Parameter(
     *      name="title",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *         name="problem_type_id",
     *         in="path",
     *         description="Update Software type",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Response(response="200", description="An example endpoint")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'problem_type_id' => 'required|numeric',
            'title' => 'required|string',
        ]);

        $problem = ProblemType::find($request->problem_type_id);

        if ($problem) {
            $problemSubType = ProblemSubType::create($validatedData);
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            return saveDataResponse($problemSubType);
        } else {
            return jsonDataResponse($problem);
        }
    }



    public function show($id)
    {
        //
    }


    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Put(
     *     path="/api/v2/problem-sub-types/{id}",
     *     tags={"Problem Sub Type"},
     *     summary="Update Problem Sub Type",
     *     @OA\Parameter(
     *      name="title",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *         name="problem_type_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Response(response="200", description="An example endpoint")
     * )
     */
    public function update(Request $request, $id)
    {

        $problemSubType = ProblemSubType::find($id);
        $validatedData = $request->validate([
            'title' => 'required|string',
            'problem_type_id' => 'required|numeric'

        ]);

        $problemSubType->title = $validatedData['title'];
        $problemSubType->problem_type_id = $validatedData['problem_type_id'];
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        return updateDataResponse($problemSubType->save());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Delete(
     *     path="/api/v2/problem-sub-types/{id}",
     *     tags={"Problem Sub Type"},
     *     summary="Delete Problem Sub Type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *
     *     @OA\Response(response="200", description="An example endpoint")
     * )
     */
    public function destroy($id)
    {
        $ProblemSubType = ProblemSubType::find($id);
        $delete =  $ProblemSubType->delete();
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        return deleteDataResponse($delete);
    }
}
