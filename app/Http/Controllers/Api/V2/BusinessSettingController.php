<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\V2\BusinessSettingCollection;

class BusinessSettingController extends Controller
{


    public function getSignupVisibility()
    {
        try {
            $settings = BusinessSetting::whereIn('name', [
                'support_mobile_signup',
                'client_mobile_signup',
            ])->pluck('value', 'name')->toArray();

            $visibility = [
                'client_signup' => [
                    'visible' => filter_var($settings['client_mobile_signup'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    // 'message' => $settings['client_mobile_signup'] ?? 'Client registration is currently unavailable.',
                ],
                'support_signup' => [
                    'visible' => filter_var($settings['support_mobile_signup'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    // 'message' => $settings['support_mobile_signup'] ?? 'Support user registration is currently unavailable.',
                ],

            ];

            return response()->json([
                'success' => true,
                'status' => 200,
                'data' => $visibility,
            ]);

        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Error fetching signup visibility settings.',
            ], 500);
        }
    }

    public function updateSignupVisibility(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'client_mobile_signup' => 'required|boolean',
                'support_mobile_signup' => 'required|boolean',
            ]);

            $data = [
                'client_mobile_signup' => $validated['client_mobile_signup'],
                'support_mobile_signup' => $validated['support_mobile_signup'],
            ];

            foreach ($data as $key => $value) {
                BusinessSetting::updateOrCreate([ 'name' => $key],[
                    'value' => $value,
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => 200,
                // 'data' => $data,
                'message' => 'Signup visibility settings updated successfully.',
            ]);

        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Error updating signup visibility settings.',
            ], 500);
        }
    }

public function updateSupportAppVersion(Request $request)
{
    try {
        $validated = $request->validate([
            'support_app_version' => 'required|string',
        ]);

        BusinessSetting::updateOrCreate(
            ['name' => 'support_app_version'],
            ['value' => $validated['support_app_version']]
        );

        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'Support App Version updated successfully.',
        ]);

    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'status' => 422,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'status' => 500,
            'message' => $e->getMessage(),
        ], 500);
    }
}

public function checkSupportAppVersion(){
    try {
        $supportAppVersion = BusinessSetting::where('name', 'support_app_version')->first();
        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $supportAppVersion,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'status' => 500,
            'message' => $e->getMessage(),
        ], 500);
    }

}
}
