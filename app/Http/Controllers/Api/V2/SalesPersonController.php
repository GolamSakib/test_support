<?php
namespace App\Http\Controllers\Api\V2;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Support;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\CustomerSoftware;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SalesPersonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/v2/support-inquiry/support_executive_list",
     *     tags={"Support Inquiry"},
     *     security={{"bearerAuth": {}}},
     *     summary="Get support executive list with average support time",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(
     *                     property="first_name",
     *                     type="string",
     *                     example="John"
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     type="string",
     *                     example="Doe"
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                     example="johndoe"
     *                 ),
     *                 @OA\Property(
     *                     property="accepted_support_id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="t_avg",
     *                     type="number",
     *                     format="float",
     *                     example=86400
     *                 ),
     *                 @OA\Property(
     *                     property="days",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="hours",
     *                     type="integer",
     *                     example=0
     *                 ),
     *                 @OA\Property(
     *                     property="minutes",
     *                     type="integer",
     *                     example=0
     *                 ),
     *                 @OA\Property(
     *                     property="remaining_seconds",
     *                     type="number",
     *                     format="float",
     *                     example=0
     *                 ),
     *                 @OA\Property(
     *                     property="total",
     *                     type="integer",
     *                     example=10
     *                 ),
     *                 @OA\Property(
     *                     property="active_days",
     *                     type="string",
     *                     example="1, 2, 3"
     *                 ),
     *             )
     *         )
     *     )
     * )
     */
    public function sales_information(Request $request)
    {
        $user_id = $request->sale_by ?? null;
        $data    = CustomerSoftware::when($request->has('sale_by'), function ($query) use ($request) {
            $query->whereRaw("sell_by_id::jsonb @> ?", [$request->sale_by]);
        })->get();

        $modifiedData = $data->flatMap(function ($item) {
            $sales_lead_names = json_decode($item->sell_by) ?? [];
            $sales_lead_ids   = json_decode($item->sell_by_id) ?? [];
            $results          = [];

            if (! empty($sales_lead_ids) && count($sales_lead_ids) > 1) {
                // If there are multiple sell_by_ids, create a new record for each
                foreach ($sales_lead_ids as $key => $val) {
                    $newItem                = $item->replicate();
                    $newItem->sell_by       = $sales_lead_names[$key] ?? null;
                    $newItem->sell_by_id    = $val;
                    $newItem->customer_name = Customer::where('id', $item->client_id)->pluck('cusname')->first();
                    $results[]              = $newItem;
                }
                return $results; // Return the new records
            }

            if (! empty($sales_lead_ids) && count($sales_lead_ids) == 1) {
                $item->sell_by_id    = $sales_lead_ids[0];
                $item->sell_by       = $sales_lead_names[0] ?? null;
                $item->customer_name = Customer::where('id', $item->client_id)->pluck('cusname')->first();
                return [$item];
            }

            return []; // Handle any cases where sell_by_ids is null or empty
        });

        $modifiedData = $modifiedData->filter(function ($item) {
            $user = User::where('id', $item->sell_by_id)->first();
            if ($user) {
                $item->sale_person = $user->username ?? "";
                return true;
            } else {
                return false;
            }
        });

        if ($user_id !== null) {
            $modifiedData = $modifiedData->filter(function ($item) use ($user_id) {
                return $item->sell_by_id == (int) $user_id; // Ensure $user_id is compared as an integer
            });
        }

        $modifiedData = $modifiedData->values();
        return JsonDataResponse($modifiedData);
    }

    public function search_by_sales_person(Request $request)
    {
        try {
            // Build cache key from request parameters
            $cacheKey = 'sales_search_' . md5(json_encode([
                'user_id'    => $request->sale_by,
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'specific'   => $request->specific,
            ]));

            // Try to get from cache first
            // return Cache::remember($cacheKey, now()->addHours(24), function () use ($request) {
            $user_id                = $request->sale_by ?? null;
            $start_date             = $request->start_date ? Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay() : null;
            $end_date               = $request->end_date ? Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay() : null;
            $specific               = $request->specific ?? null;
            $customerAllBillingData = $this->getCustomerAllBillingData();

            $query = CustomerSoftware::select([
                'sell_by_id',
                'sell_by',
                'software_name',
                'client_name',
                'client_id',
            ]);

            // Apply filters
            if ($user_id) {
                $query->whereRaw("sell_by_id::jsonb @> ?", [$user_id]);
            }

            if ($start_date && $end_date) {
                $query->whereBetween('agreement_date', [$start_date, $end_date]);
            } else {
                $query->whereYear('agreement_date', Carbon::now()->year)
                    ->whereMonth('agreement_date', Carbon::now()->month);
            }

            if ($specific) {
                $query->where('agreement_date', '>=', Carbon::now()->subDays($specific)->format('Y-m-d'));
            }

            $data = $query->groupBy('sell_by_id', 'sell_by', 'client_name', 'software_name', 'client_id')
                ->orderBy('sell_by_id')
                ->get();

            foreach ($data as $key => $value) {
                $customerBillingData = collect($customerAllBillingData)
                    ->where('cusID', $value->client_id)
                    ->values();

                $value->sale_amount = $customerBillingData->sum('totalAmt') ?? 0;
                $value->paid_amount = $customerBillingData->sum('paidAmt') ?? 0;
                $value->due_amount  = $customerBillingData->sum('balanceAmt') ?? 0;
            }

            $modifiedData = $data->flatMap(function ($item) {
                $sales_lead_names = json_decode($item->sell_by) ?: [];
                $sales_lead_ids   = json_decode($item->sell_by_id) ?: [];

                if (empty($sales_lead_ids)) {
                    return [];
                }

                if (count($sales_lead_ids) > 1) {
                    return collect($sales_lead_ids)->map(function ($val, $key) use ($item, $sales_lead_names) {
                        $newItem             = $item->replicate();
                        $newItem->sell_by    = $sales_lead_names[$key] ?? null;
                        $newItem->sell_by_id = $val;
                        return $newItem;
                    });
                }

                $item->sell_by_id = $sales_lead_ids[0] ?? null;
                $item->sell_by    = $sales_lead_names[0] ?? null;
                return [$item];
            });

            if ($user_id !== null) {
                $modifiedData = $modifiedData->filter(function ($item) use ($user_id) {
                    return $item->sell_by_id == (int) $user_id;
                });
            }

            $result = ['result' => [], 'total_sale' => 0];

            if ($modifiedData->isNotEmpty()) {
                $result['result'] = $modifiedData->groupBy('sell_by_id')
                    ->map(function ($group) {
                        return [
                            'sell_by_id'     => $group->first()->sell_by_id ?? null,
                            'sell_by'        => $group->first()->sell_by ?? null,
                            'client_names'   => $group->pluck('client_name')->filter()->unique()->implode(', '),
                            'software_names' => $group->pluck('software_name')->filter()->unique()->implode(', '),
                            'number_of_sale' => $group->pluck('client_id')->filter()->unique()->count(),
                            'sale_amount'    => round($group->groupBy('client_id')->map(function ($clientGroup) {
                                return $clientGroup->first()->sale_amount;
                            })->sum()),
                            'paid_amount'    => round($group->groupBy('client_id')->map(function ($clientGroup) {
                                return $clientGroup->first()->paid_amount;
                            })->sum()),
                            'due_amount'     => round($group->groupBy('client_id')->map(function ($clientGroup) {
                                return $clientGroup->first()->due_amount;
                            })->sum()),
                        ];
                    })
                    ->values()
                    ->filter(function ($item) {
                        return User::where('id', $item['sell_by_id'])->exists();
                    })
                    ->map(function ($item) {
                        $item['sell_by'] = User::where('id', $item['sell_by_id'])->value('username') ?? $item['sell_by'];
                        return $item;
                    })
                    ->values()
                    ->toArray();

                $result['total_sale_data'] = $modifiedData
                    ->groupBy('client_id')
                    ->map(function ($group) {
                        return [
                            'client_name'    => $group->first()['client_name'],
                            'sale_amount'    => $group->first()['sale_amount'],
                            'paid_amount'    => $group->first()['paid_amount'],
                            'due_amount'     => $group->first()['due_amount'],
                            'number_of_sale' => 1,
                        ];
                    })
                    ->values()
                    ->pipe(function ($grouped) {
                        return [
                            'total_sale'        => $grouped->sum('number_of_sale'),
                            'total_sale_amount' => $grouped->sum('sale_amount'),
                            'total_paid_amount' => $grouped->sum('paid_amount'),
                            'total_due_amount'  => $grouped->sum('due_amount'),
                        ];
                    });

// Replace the existing calculations with:
                $result['total_sale']        = $result['total_sale_data']['total_sale'];
                $result['total_sale_amount'] = $result['total_sale_data']['total_sale_amount'];
                $result['total_paid_amount'] = $result['total_sale_data']['total_paid_amount'];
                $result['total_due_amount']  = $result['total_sale_data']['total_due_amount'];

                $result['export_data'] = $modifiedData
                    ->groupBy('client_id')
                    ->map(function ($group) {
                        return [
                            'sell_by_id'    => $group->first()['sell_by_id'],
                            'sell_by'       => $group->first()['sell_by'],
                            'software_name' => $group->pluck('software_name')->unique()->implode(', '),
                            'client_name'   => $group->first()['client_name'],
                            'client_id'     => $group->first()['client_id'],
                            'sale_amount'   => round($group->first()['sale_amount']),
                            'paid_amount'   => round($group->first()['paid_amount']),
                            'due_amount'    => round($group->first()['due_amount']),
                        ];
                    })
                    ->values()
                    ->toArray()
                ;
            }

            return response()->json([
                'success' => true,
                'status'  => 200,
                'data'    => $result,
            ]);
            // });

        } catch (\Exception $e) {
            \Log::error('Sales Search Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while processing your request',
            ], 500);
        }
    }

    public function getCustomerAllBillingData()
    {
        $response = Http::get('http://software.mediasoftbd.com/msbill/api/BillCollection/GetAllBillByType?BillType=Software');
        if ($response->successful()) {
            $customerAllBillingData = $response->json()['data'] ?? [];
            return $customerAllBillingData;
        }

    }

    // public function search_by_sales_person_for_dashboard(Request $request)
    // {

    //     $user_id = $request->sale_by ?? null;
    //     $start_date = $request->start_date ?? null;
    //     $end_date = $request->end_date ?? null;
    //     $specific = $request->specific ?? null;
    //     if ($start_date) {
    //         $start_date = Carbon::createFromFormat('Y-m-d', $start_date)->startOfDay();
    //     }else{
    //         $start_date = now()->subMonths(12)->startOfMonth()->toDateString();
    //     }

    //     if ($end_date) {
    //         $end_date = Carbon::createFromFormat('Y-m-d', $end_date)->endOfDay();
    //     }else{
    //         $end_date = now()->endOfMonth()->toDateString();
    //     }

    //     $data = CustomerSoftware::select(
    //         'sell_by_id',
    //         'sell_by',
    //         DB::raw('EXTRACT(YEAR FROM agreement_date) AS year'),
    //         DB::raw('COUNT(DISTINCT software_name) as number_of_software'),
    //         // DB::raw('COUNT(client_name) as number_of_client'),
    //         DB::raw('COUNT(*) as number_of_sale'),
    //         // DB::raw('EXTRACT(YEAR FROM agreement_date)')
    //     )
    //         ->when($user_id, function ($query) use ($user_id) {
    //             // Assuming sell_by_id is JSONB, otherwise, remove ::jsonb
    //             $query->whereRaw("sell_by_id::jsonb @> ?", [$user_id]);
    //         })
    //         ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
    //             // Ensure start_date and end_date are checked separately
    //             if ($start_date && $end_date) {
    //                 $query->whereBetween('agreement_date', [$start_date, $end_date]);
    //             }
    //         })
    //         ->when($specific, function ($query) use ($specific) {
    //             $query->where('agreement_date', '>=', Carbon::now()->subDays($specific)->format('Y-m-d'));
    //         })
    //         ->whereNotNull('agreement_date')
    //         ->groupBy('sell_by_id', 'sell_by',DB::raw('EXTRACT(YEAR FROM agreement_date)'))
    //         ->orderBy('year')
    //         // ->orderBy('sell_by_id')
    //         ->get();

    //     // $sale_chart_by_year = DB::table('addusers_softwarelist')
    //     //     ->selectRaw('EXTRACT(YEAR FROM agreement_date) AS year')
    //     //     ->selectRaw('COUNT(DISTINCT software_name) AS number_of_software')
    //     //     // ->selectRaw('COUNT(DISTINCT client_name) AS number_of_client')
    //     //     ->selectRaw('COUNT(*) AS number_of_sale')
    //     //     ->whereNotNull('agreement_date')
    //     //     ->groupByRaw('EXTRACT(YEAR FROM agreement_date)')
    //     //     ->get();

    //     $modifiedData = $data->flatMap(function ($item) {
    //         $sales_lead_names = json_decode($item->sell_by);
    //         $sales_lead_ids = json_decode($item->sell_by_id);

    //         $results = [];

    //         if ($sales_lead_ids !== null && count($sales_lead_ids) > 1) {
    //             // If there are multiple sell_by_ids, create a new record for each
    //             foreach ($sales_lead_ids as $key => $val) {
    //                 $newItem = $item->replicate();
    //                 $newItem->sell_by = $sales_lead_names[$key] ?? null;
    //                 $newItem->sell_by_id = $val;
    //                 $results[] = $newItem;
    //             }
    //             return $results; // Return the new records
    //         }

    //         if ($sales_lead_ids !== null && count($sales_lead_ids) == 1) {
    //             $item->sell_by_id = $sales_lead_ids[0];
    //             $item->sell_by = $sales_lead_names[0];
    //             return [$item];
    //         }

    //         return []; // Handle any cases where sell_by_ids is null or empty
    //     });

    //     if ($user_id !== null) {
    //         $modifiedData = $modifiedData->filter(function ($item) use ($user_id) {
    //             return $item->sell_by_id == (int) $user_id;
    //         });
    //     }

    //     $total_number_of_sale = $modifiedData->sum('number_of_sale');
    //     $total_number_of_software = $modifiedData->sum('number_of_software');
    //     // $total_number_of_client = $modifiedData->sum('number_of_client');

    //     $result['total_number_of_sale'] = $total_number_of_sale;
    //     $result['total_number_of_software'] = $total_number_of_software;
    //     // $result['total_number_of_client'] = $total_number_of_client;
    //     $result['sale_chart_by_year'] = $modifiedData;
    // return JsonDataResponse($result);
    // }

    public function search_by_sales_person_for_dashboard(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'sale_by'    => 'nullable|integer',
                'start_date' => 'nullable|date_format:Y-m-d',
                'end_date'   => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
                'specific'   => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'status'  => 422,
                    'message' => 'Validation error',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            // Extract and process request parameters
            $userId   = $request->sale_by;
            $specific = $request->specific;

            // Process dates
            try {
                $startDate = $request->start_date
                ? Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay()
                : now()->subMonths(12)->startOfMonth();

                $endDate = $request->end_date
                ? Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay()
                : now()->endOfMonth();
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'status'  => 422,
                    'message' => 'Invalid date format',
                ], 422);
            }

            // Build query with Query Builder
            $query = CustomerSoftware::select([
                'sell_by_id',
                'sell_by',
                DB::raw('EXTRACT(YEAR FROM agreement_date) AS year'),
                DB::raw('COUNT(DISTINCT software_name) as number_of_software'),
                DB::raw('COUNT(*) as number_of_sale'),
            ])
                ->whereNotNull('agreement_date');

            // Apply filters
            if ($userId) {
                // Check if PostgreSQL or MySQL and handle JSONB accordingly
                if (DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql') {
                    $query->whereRaw("sell_by_id::jsonb @> ?", [$userId]);
                } else {
                    $query->whereRaw("JSON_CONTAINS(sell_by_id, ?)", [$userId]);
                }
            }

            if ($specific) {
                $query->where('agreement_date', '>=', now()->subDays($specific));
            } else {
                $query->whereBetween('agreement_date', [$startDate, $endDate]);
            }

            // Group and order
            $data = $query->groupBy('sell_by_id', 'sell_by', DB::raw('EXTRACT(YEAR FROM agreement_date)'))
                ->orderBy('year')
                ->get();

            // Process the data
            $modifiedData = collect();
            foreach ($data as $item) {
                $salesLeadNames = json_decode($item->sell_by, true) ?? [];
                $salesLeadIds   = json_decode($item->sell_by_id, true) ?? [];

                // Handle multiple sales leads
                if (! empty($salesLeadIds)) {
                    foreach ($salesLeadIds as $key => $id) {
                        $newItem = (object) [
                            'year'               => $item->year,
                            'sell_by_id'         => $id,
                            'sell_by'            => $salesLeadNames[$key] ?? null,
                            'number_of_software' => $item->number_of_software,
                            'number_of_sale'     => $item->number_of_sale,
                        ];

                        // Filter by user_id if specified
                        if (! $userId || $id == $userId) {
                            $modifiedData->push($newItem);
                        }
                    }
                }
            }
            $result = $modifiedData->groupBy('sell_by_id')
                ->map(function ($group) {
                    return [
                        'sell_by_id'         => $group->first()->sell_by_id,
                        'sell_by'            => $group->first()->sell_by,
                        'year'               => $group->first()->year,
                        'number_of_software' => $group->sum('number_of_software'),
                        'number_of_sale'     => $group->sum('number_of_sale'),
                    ];
                });

            $userIds = User::select('id')->pluck('id');
            $result  = $result->filter(function ($item) use ($userIds) {
                return $userIds->contains($item['sell_by_id']);
            });

            // Calculate totals
            $totals = [
                'total_number_of_sale'     => $modifiedData->sum('number_of_sale'),
                'total_number_of_software' => $modifiedData->sum('number_of_software'),
                'sale_chart_by_year'       => $result->values(),
            ];

            return response()->json([
                'success' => true,
                'status'  => 200,
                'data'    => $totals,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Sales Dashboard Error: ' . $e->getMessage(), [
                'user_id' => $userId ?? null,
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => config('app.debug')
                ? $e->getMessage()
                : 'An error occurred while processing your request',
            ], 500);
        }
    }

    // Helper method to determine if caching should be enabled
    protected function shouldCache()
    {
        return config('app.enable_dashboard_cache', false);
    }
}
