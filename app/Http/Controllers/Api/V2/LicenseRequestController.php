<?php

namespace App\Http\Controllers\Api\V2;

use Carbon\Carbon;
use App\Models\shop;
use App\Models\User;
use App\Models\Support;
use App\Models\Division;
use App\Models\Software;
use App\Models\ProblemType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\SoftwareSupportPerson;
use App\Http\Controllers\Api\V2\Controller;


class SupportRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function store(Request $request)
     {

     }
}
