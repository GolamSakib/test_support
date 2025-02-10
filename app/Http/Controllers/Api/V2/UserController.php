<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\UserCollection;
use App\Models\User;
use Illuminate\Http\Request;

use Laravel\Sanctum\PersonalAccessToken;


class UserController extends Controller
{
    public function info($id)
    {
        return new UserCollection(User::where('id', auth()->user()->id)->get());
    }

    public function updateName(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $user->update([
            'name' => $request->name
        ]);
        return response()->json([
            'message' => translate('Profile information has been updated successfully')
        ]);
    }

    public function getUserInfoByAccessToken(Request $request)
    {

        $false_response = [
            'result' => false,
            'id' => 0,
            'name' => "",
            'email' => "",
            'avatar' => "",
            'avatar_original' => "",
            'phone' => ""
        ];



        $token = PersonalAccessToken::findToken($request->access_token);
        if (!$token) {
            return response()->json($false_response);
        }

        $user = $token->tokenable;



        if ($user == null) {
            return response()->json($false_response);
        }

        return response()->json([
            'result' => true,
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'avatar_original' => uploaded_asset($user->avatar_original),
            'phone' => $user->phone
        ]);
    }

    /**
     *  @OA\Get(
     *     path="/api/v2/user-list",
     *     tags={"User List"},
     *     summary="Get User List",
     *     security={{"bearerAuth": {} }},
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="See User List.")
     *         )
     *     ),
     * )
     * )
     */

    public function userlist()
    {
        return $userList = User::where('role', '!=', 'admin')
            ->where('isactive',true)
            ->orderBy('username','asc')
            ->get(['id', 'username', 'first_name', 'last_name', 'email', 'phone_no', 'designation','role']);
            return response()->json([
            'success' => true,
            'data' => $userList,
        ], 200);
    }







    public function saleleadlist()
    {
        // $userList = User::where('user_type', '!=', 'client')
        //     ->get(['id', 'name', 'username', 'first_name', 'last_name', 'email', 'phone_no', 'designation', 'photo', 'user_type']);
        return $userList = User::orderBy('username','asc')
        ->get(['id', 'username', 'first_name', 'last_name', 'email', 'phone_no', 'designation', 'role']);
        return response()->json([
            'success' => true,
            'data' => $userList,
        ], 200);
    }


    /**
     *  @OA\Get(
     *     path="/api/v2/getallsupportperson",
     *     tags={"Support List"},
     *     summary="Get Support List",
     *     security={{"bearerAuth": {} }},
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="See User List.")
     *         )
     *     ),
     * )
     * )
     */

    public function supporPerson()
    {
        $support_person = User::where('user_type', 'Support')
        ->orderBy('username','asc')
        ->paginate(15);
        return response()->json([
            'success' => true,
            'data' => $support_person,
        ], 200);
    }
}
