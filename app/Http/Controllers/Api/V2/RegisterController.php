<?php

namespace App\Http\Controllers\Api\V2;

use DB;
use Auth;
use Hash;
use App\Models\Role;
use App\Models\User;
use App\Models\Staff;
use App\Models\ClientUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        /*$this->middleware(['permission:view_all_staffs'])->only('index');
        $this->middleware(['permission:add_staff'])->only('create');
        $this->middleware(['permission:edit_staff'])->only('edit');
        $this->middleware(['permission:delete_staff'])->only('destroy');*/
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // $users = User::where('username', '!=', 'super_admin')->get();
        $users = User::all();
        $clients = ClientUser::all();
        foreach ($clients as $client) {
            $client->role = "Client";
            $client->phone_no = $client->phoneno ?? "";
            $client->isactive = $client->is_active ?? "";
        }
        $merged = $users->concat($clients);


        return JsonDataResponse($merged);
    }

public function userlist(Request $request)
{
    $search = $request->input('search');
    $perPage = 20;

    $users = User::query();
    $clients = ClientUser::query();

    if ($search) {
        // If search term is "client" (case insensitive), prioritize ClientUser results
        if (strtolower($search) === 'client') {
            $users->where('id', 0); // Empty result for users
            $clients->where('id', '>', 0); // All clients
        } else {
            $users->where(function($query) use ($search) {
                $query->where('username', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('phone_no', 'LIKE', "%{$search}%")
                      ->orWhere('role', 'LIKE', "%{$search}%");
            });

            $clients->where(function($query) use ($search) {
                $query->where('username', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('phoneno', 'LIKE', "%{$search}%");
            });
        }
    }

    $users = $users->select([
        'id',
        'username',
        'email',
        'phone_no',
        'pro_img_url',
        'role',
        'isactive'
    ]);

    $clients = $clients->select([
        'id',
        'username',
        'email',
        'phoneno as phone_no',
        'pro_img_url',
        DB::raw("'Client' as role"),
        'is_active as isactive'
    ]);

    $combined = $users->union($clients)->paginate($perPage);

    return JsonDataResponse($combined);
}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $table = $request->user_type == 'Client' ? 'addusers_users' : 'supportadmin_support_user';
        $phoneField = $request->user_type == 'Client' ? 'phoneno' : 'phone_no';

        // Validation rules

        $validator = Validator::make($request->all(), [
            'email' => "required|unique:$table,email",
            'username' => "required|unique:$table,username",
            'phone_no' => "required|unique:$table,$phoneField",
            'password' => 'required|string|min:6',
            'user_type' => 'required',
            'image' => 'sometimes|mimes:jpg,jpeg,png,svg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        DB::beginTransaction();

        try {
            $user = null;
            if ($request->user_type == 'Client') {
                $user = ClientUser::create([
                    'username' => $request->username,
                    'designation' => $request->designation ?? null,
                    'email' => $request->email ?? null,
                    'phoneno' => $request->phone_no ?? null,
                    'password' => Hash::make($request->password),
                    'is_active' => $request->status,
                    'is_approved' => true,
                    'is_status_active' => true,
                ]);
            } else {
                $imagePath = null;
                if ($request->hasFile('image')) {
                    $fileExtension = $request->file('image')->extension();
                    $filename = $request->user_type . uniqid() . '.' . $fileExtension;  // Generate a unique filename
                    $imagePath = $request->file('image')->storeAs('uploads/users/profile', $filename);
                }

                $user = User::create([
                    'username' => $request->username,
                    'email' => $request->email ?? null,
                    'phone_no' => $request->phone_no ?? null,
                    'designation' => $request->designation ?? null,
                    'role' => $request->user_type,
                    'password' => $request->password,  // Hash the password
                    'address' => $request->address,
                    'isactive' => $request->status,
                    'pro_img_url' => $imagePath,
                    'is_status_active' => true
                ]);
            }

            $user->assignRole($request->user_type);
            $token = $user->createToken('API Token')->plainTextToken;

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'errors' => $e->getMessage()], 500);
        }

        return response()->json([
            "success" => true,
            "status" => 200,
            'access_token' => $token,
            "message" => "User Created Successfully",
            'data' => $user,
        ]);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        $type = $request->type;
        if ($type == 'Client') {
            $user = ClientUser::findOrFail($id);
            $user->phone_no = $user->phoneno ?? "";
            $user->status = $user->is_active ?? "";
            $user->role = "Client";
            $user->address = $user->area ?? "";
        } else {
            $user = User::findOrFail($id);
            $user->status = $user->isactive ?? "";
            if ($user->role == "support") {
                $user->role = "Support";
            }
            if ($user->role == "admin") {
                $user->role = "Admin";
            }
        }
        return JsonDataResponse($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, $id)
    {

        $user = User::findOrFail($id);
        $roles = Role::where('id', '!=', 1)->orderBy('id', 'desc')->get();
        if ($user) {
            return response()->json([
                "success" => true,
                "status" => 200,
                'data' => $user,
                'roles' => $roles,
            ]);
        } else {
            return response()->json([
                "success" => false,
                "status" => 401,
                'message' => 'No data available',
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id, $BySupport = null)
    {
        $type = $request->user_type;
        $table = ($type == 'Client') ? 'addusers_users' : 'supportadmin_support_user';
        $phoneField = ($type == 'Client') ? 'phoneno' : 'phone_no';
        $validator = Validator::make($request->all(), [
            'email' => "nullable|unique:$table,email,$id",
            'username' => "required|unique:$table,username,$id",
            'user_type' => 'required',
            'image' => 'sometimes|mimes:jpg,jpeg,png,svg|max:2048',
            'phone_no' => "required|unique:$table,$phoneField,$id"
        ]);
        // Check validation
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 422,
                'access_token' => null,
                'data' => null,
                'message' => "validation error!",
                'errors' => $validator->errors(),
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $fileExtension = $request->file('image')->getClientOriginalExtension();
            $filename = uniqid() . '.' . $fileExtension;  // Generate a unique filename
            $imagePath = $request->file('image')->storeAs('uploads/users/profile', $filename);
        }

        if ($type == 'Client') {
            $user = ClientUser::find($id);
            DB::beginTransaction();
            try {
                $user->username = $request->username;
                $user->email = $request->email;
                $user->phoneno = $request->phone_no;
                $user->designation = $request->designation;
                $user->is_active = $request->status;
                $user->area = $request->address;
                if ($request->password) {
                    $user->password = Hash::make($request->password);
                }
                if ($imagePath !== null) {
                    $user->pro_img_url = $imagePath;
                }
                $user->save();
                $user->assignRole($request->user_type);
                $token = $user->createToken('API Token')->plainTextToken;

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'status' => 500,
                    'message' => 'update failed!',
                    'errors' => $e->getMessage()
                ], 500);
            }
        } else {
            $user = User::find($id);
            DB::beginTransaction();
            try {
                $user->username = $request->username;
                $user->email = $request->email;
                $user->phone_no = $request->phone_no;
                $user->designation = $request->designation;
                $user->isactive = $request->status;
                $user->role = $request->user_type;
                $user->address = $request->address??"";
                if ($request->password) {
                    $user->password = $request->password;
                }
                if ($imagePath !== null) {
                    $user->pro_img_url = $imagePath;
                }
                $user->save();
                $user->assignRole($request->user_type);
                $token = $user->createToken('API Token')->plainTextToken;
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'status' => 500,
                    'message' => 'update failed!',
                    'errors' => $e->getMessage()
                ], 500);
            }
        }

        if ($user) {
            return response()->json([
                "success" => true,
                "status" => 200,
                'access_token' => $token,
                'data' => $user,
                'message' => 'User updated successfully',
                'errors' => null
            ]);
        } else {
            return response()->json([
                "success" => false,
                "status" => 401,
                'access_token' => null,
                "data" => null,
                'message' => 'No data available',
                'errors' => null
            ], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $type = $request->type;
        if ($type !== 'Client') {
            $delete = User::destroy($id);
        } else {
            $delete = ClientUser::destroy($id);
        }
        return deleteDataResponse($delete);
    }

    public function getTempUsers(): JsonResponse
    {
        $tempUsers = User::query()
            ->select(['id','username','email','phone_no','isactive','isTemp','role'])
            ->where('isTemp', true)
            ->get();

        $tempClients = ClientUser::query()
            ->select(['id','username','email','phoneno as phone_no','is_active as isactive'])
            ->where('isTemp', true)
            ->get()
            ->map(function ($client) {
                $client->role = 'Client';
                return $client;
            });

        $mergedUsers = $tempUsers->concat($tempClients);

        return JsonDataResponse($mergedUsers);
    }

    public function deleteTempUser(Request $request, $id)
    {
        $type = $request->type;
        if ($type !== 'Client') {
            $delete = User::destroy($id);
        } else {
            $delete = ClientUser::destroy($id);
        }
        return deleteDataResponse($delete);
    }
}
