<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\CustomerSoftware;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;

class SaleInformationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v2/sale-information",
     *     tags={"Sale Information"},
     *     summary="Sale Information",
     *     security={{"bearerAuth": {} }},
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="See Sale Information.")
     *         )
     *     ),
     * )
     * )
     */
    public function saleinformation()
    {
        $saleinfo = User::with(['customer_software.customer', 'customer_software.leadBy:id,name', 'customer_software.saleBy:id,name'])->get();
//        dd($saleinfo);
        $tempsaleinfo = [];
        foreach ($saleinfo as $saleinformation) {
            array_push($tempsaleinfo, [
                "user_name" => $saleinformation->name,
                "software_name" => $saleinformation->customer_software->software_name??null,
                "sale_by" => $saleinformation->customer_software->saleBy->name??null,
                "lead_by" => $saleinformation->customer_software->leadBy->name??null,
                "customer_name" => $saleinformation->customer_software->customer->name??null,

            ]);
        }
        $saleinfo = $tempsaleinfo;

        return JsonDataResponse($saleinfo);

    }

    /**
     * @OA\Get(
     *     path="/api/v2/sale-person",
     *     tags={"Sale Person List"},
     *     summary="Sale Person",
     *     security={{"bearerAuth": {} }},
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="See Sale Person.")
     *         )
     *     ),
     * )
     * )
     */
    public function salespersonlist()
    {
        $salepersonlist = CustomerSoftware::with('saleBy')->get();
        $salelist = [];
        foreach ($salepersonlist as $list) {

            array_push($salelist, [
                "id" => $list->saleBy->id,
                "name" => $list->saleBy->name,
            ]);
        }
        $salepersonlist = $salelist;

        return JsonDataResponse($salepersonlist);

    }
    /**
     *  @OA\Get(
     *     path="/api/v2/search-by-sales-person",
     *     tags={"Search Sale Person List"},
     *     summary="Get Search sale Person List",
     *     security={{"bearerAuth": {} }},
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="See Search sale Person List.")
     *         )
     *     ),
     * )
     * )
     */
    public function searchbysalesperson(Request $request)
    {
//        $date = Carbon::now()->subDays(7);
//        $specific_date = $request->date;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $salePersonId = $request->sale_person_id;
        $searchsaleperson = CustomerSoftware::with('saleBy')->select('sale_by');
//        if (isset($date)) {
//            $searchsaleperson->where('created_at', $date);
//        }
        if (isset($from_date) && isset($to_date)) {
            $searchsaleperson->whereBetween('created_at', [
                $from_date, $to_date
            ]);
        }
        if (isset($salePersonId)) {
            $searchsaleperson->where('sale_by', '=', $salePersonId);
        }
        $searchsale = [];

        foreach ($searchsaleperson->groupBy('sale_by')->get() as $search) {
            $softwareName = CustomerSoftware::where('sale_by', $search->sale_by)
                ->with('customer')
                ->get();
            $customers=$softwareName->pluck('customer');
            array_push($searchsale, [
                "sale_person_name" => $search->saleBy->name,
                "software_name" => implode(',', $softwareName->pluck('software_name')->toArray()),
                "client_name" => implode(',', $customers->pluck('name')->toArray())
            ]);
        }
        $searchsaleperson = $searchsale;
        return JsonDataResponse($searchsaleperson);

    }

    /**
     *  @OA\Get(
     *     path="/api/v2/days-sales-information",
     *     tags={"Search by Days for Sales Information"},
     *     summary="Get Search by days for sales information",
     *     security={{"bearerAuth": {} }},
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="See Search by days for sales information List.")
     *         )
     *     ),
     * )
     * )
     */
    public function daysales(Request $request)
    {
        $today = Carbon::now();
        $specific_date = $request->specific_date;
        $searchsaleperson = CustomerSoftware::with('saleBy')->select('sale_by');
        if (isset($specific_date)) {
            $specific_date=Carbon::now()->subDay($specific_date);
            $searchsaleperson->whereBetween('created_at', [$specific_date,$today]);
        }
        if (isset($salePersonId)) {
            $searchsaleperson->where('sale_by', '=', $salePersonId);
        }
        $searchsale = [];

        foreach ($searchsaleperson->groupBy('sale_by')->get() as $search) {
            $softwareName = CustomerSoftware::where('sale_by', $search->sale_by)
                ->with('customer')
                ->get();
            $customers=$softwareName->pluck('customer');
            array_push($searchsale, [
                "sale_person_name" => $search->saleBy->name,
                "software_name" => implode(',', $softwareName->pluck('software_name')->toArray()),
                "client_name" => implode(',', $customers->pluck('name')->toArray()),
                "total_sale" => count($customers->pluck('name')->toArray())
            ]);
        }
        $searchsaleperson = $searchsale;
        return JsonDataResponse($searchsaleperson);

    }




}
