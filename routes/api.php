<?php


Route::group(['prefix' => 'v2/auth', 'middleware' => ['app_language']], function () {
    Route::post('login', 'App\Http\Controllers\Api\V2\AuthController@login');
    Route::post('login/client', 'App\Http\Controllers\Api\V2\AuthController@clientLogin');
    Route::post('client-app-login', 'App\Http\Controllers\Api\V2\AuthController@clientAppLogin');
    Route::post('signup', 'App\Http\Controllers\Api\V2\AuthController@signup');
    Route::post('social-login', 'App\Http\Controllers\Api\V2\AuthController@socialLogin');
    Route::post('password/forget_request', 'App\Http\Controllers\Api\V2\PasswordResetController@forgetRequest');
    Route::post('password/verify_otp', 'App\Http\Controllers\Api\V2\PasswordResetController@verifyOTP');
    Route::post('password/change_password', 'App\Http\Controllers\Api\V2\PasswordResetController@chanagePassword');
    Route::post('password/confirm_reset', 'App\Http\Controllers\Api\V2\PasswordResetController@confirmReset');
    Route::post('password/resend_code', 'App\Http\Controllers\Api\V2\PasswordResetController@resendCode');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('logout', 'App\Http\Controllers\Api\V2\AuthController@logout');
        Route::get('user', 'App\Http\Controllers\Api\V2\AuthController@user');
    });
    Route::post('resend_code', 'App\Http\Controllers\Api\V2\AuthController@resendCode');
    Route::post('confirm_code', 'App\Http\Controllers\Api\V2\AuthController@confirmCode');

    //for mobile signup
    Route::post('mobile_signup_for_support', 'App\Http\Controllers\Api\V2\AuthController@supportMobileSignup');
    Route::post('mobile_signup_for_client_user', 'App\Http\Controllers\Api\V2\AuthController@clientUserMobileSignup');

});


Route::group(['prefix' => 'v2', 'middleware' => ['app_language']], function () {

    //    Route::get('get-search-suggestions', 'App\Http\Controllers\Api\V2\SearchSuggestionController@getList');
    //    Route::get('chat/get-new-messages/{conversation_id}/{last_message_id}', 'App\Http\Controllers\Api\V2\ChatController@get_new_messages')->middleware('auth:sanctum');
    //    Route::post('chat/create-conversation', 'App\Http\Controllers\Api\V2\ChatController@create_conversation')->middleware('auth:sanctum');
    //    Route::apiResource('App\Http\Controllers\Api\V2\BannerController', 'banners')->only('index');
    Route::controller('App\Http\Controllers\Api\V2\SupportProfileController')->group(function () {
    Route::get('/clients_by_support_person_for_license_generate_for_payroll/{username}', 'ClientsBySupportPersonForAppPayroll');
});



    Route::controller('App\Http\Controllers\Api\V2\SyncController')->group(function () {
        Route::post('createOrUpdatecustomer', 'createOrUpdatecustomer');

    });



    Route::group(['middleware' => ['auth:sanctum']], function () {
        //  Roles
        Route::apiResource('roles', 'App\Http\Controllers\Api\V2\RoleController');
        Route::controller('App\Http\Controllers\Api\V2\SupportRequestController')->group(function () {
            Route::get('cache-clear', function () {
                if (Auth::user()->hasRole('Admin')) {
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('view:clear');
                    if (function_exists('opcache_reset')) {
                        opcache_reset();
                    }

                }

                return 'Cache cleared';
            });

            Route::post('/support-request/store-by-user', 'support_generated_by_user')->name('support-request.store-by-user');
            Route::get('support-requests', 'support_request_list')->name('support-request.support_for_client');
            Route::get('support-requests-for-client', 'support_list_for_client')->name('support-requests-for-client');
            Route::get('support-requests/{clientId}', 'client_support_list');
            Route::get('/get_help_giver_person', 'helpGiverPerson')->name('get_help_giver_person');
            Route::get('/support-person-for-a-client', 'supportpersonForClient')->name('get_support_person');
            Route::post('/change-processing-support-to-done', 'changeProcessingSupportToDone')->name('change-processing-support-to-done');
            Route::post('/convert-pending-support-to-seen', 'changePendingStatusToSeen')->name('change-processing-support-to-done');
            Route::post('/convert-pending-support-to-accepted', 'changeSupportStatusToAccepted')->name('convert-pending-support-to-accepted');
            Route::post('/convert-pending-support-to-help-accepted', 'changeSupportStatusToHelpAccepted');
            Route::post('/convert-pending-support-to-cancel', 'changeSupportStatusToCancel')->name('convert-pending-support-to-cancel');
            Route::post('/support-request/store-by-support-person', 'support_generated_by_support_person')->name('support-request.store-by-support-person');
            Route::post('/change-help-status-of-support-request', 'changeHelpStatus')->name('change-help-status-of-support-request');
            Route::post('/assign-request-to-support-person', 'assignrequesttosupport')->name('assign-request-to-support-person');
        });
        Route::controller('App\Http\Controllers\Api\V2\ComplainController')->group(function () {
            Route::post('/complain/store', 'store')->name('complain.store');
            Route::get('/complain/complainForSupportPerson', 'complainForSupportPerson')->name('complain.complainForSupportPerson');
            Route::get('/complain/all', 'complainAlertAll')->name('complain.all');
            Route::get('/complain/complainAlertbyAssignedPerson/{id}', 'complainAlertbyAssignedPerson')->name('complain.complainAlertbyAssignedPerson');
            Route::post('/convert-pending-complain-to-accepted', 'changeComplainAllertToAccepted')->name('convert-pending-complain-to-accepted');
            Route::post('/convert-pending-complain-to-cancel', 'changeComplainStatusToCancel')->name('convert-pending-complain-to-cancel');


            Route::get('/complain/complainAlertByClient', 'complainAlertByClient')->name('complain.complainAlertByClient');
            Route::post('complain/status/{id}', 'changeStatus')->name('complain.status');
            Route::post('complain/assaign', 'complainAssaign')->name('complain.assaign');
            Route::post('complain/seen', 'complainSeen')->name('complain.seen');
            Route::post('set-cancel-note-for-complain', 'setCancelNote')->name('set-cancel-note-for-complain');
        });
        Route::controller('App\Http\Controllers\Api\V2\InventoryRequestController')->group(function () {
            Route::post('/inventory-request/store', 'store')->name('inventory-request.store');
            Route::get('/inventory-request/inventory_requests', 'inventory_requests')->name('inventory-request.inventory_requests');
            Route::get('/all-inventory', 'index')->name('all-inventory.index');
            Route::get('/inventory-request/inventoryRequestByClient', 'inventoryRequestByClient')->name('inventory-request.inventoryRequestByClient');
            Route::get('/inventory-request/InventoryRequestbyAssignedPerson', 'InventoryRequestbyAssignedPerson')->name('inventory-request.InventoryRequestbyAssignedPerson');
            Route::post('/inventory-request/assign', 'inventoryAssign')->name('inventory-request.assign');
            Route::post('/inventory-request/seen', 'inventoryRequestSeen')->name('inventory-request.seen');
            Route::post('/inventory-done-by-support-person', 'inventoryDoneBySupportPerson')->name('inventory-done-by-support-person');
        });
        Route::controller('App\Http\Controllers\Api\V2\TrainingScheduleController')->group(function () {
            Route::post('training-scheduling/store', 'store')->name('training-scheduling.store');
            Route::get('training-scheduling/index', 'index')->name('training-scheduling.index');
            Route::get('training-alert-for-support-person', 'alertForSupportPerson')->name('training-alert-for-support-person');
            Route::post('training-alert-done-by-support-person', 'updateTrainingAlertForSupportPerson')->name('training-alert-done-by-support-person');
            Route::get('training-scheduling/training_requests_for_support', 'training_requests_for_support')->name('training-scheduling.training_requests_for_support');
            Route::get('training-scheduling/training_requests_for_client', 'training_request_for_client')->name('training-scheduling.training_requests_for_client');
        });

        Route::controller('App\Http\Controllers\Api\V2\LicenseController')->group(function () {
            Route::post('license-request/store', 'licenseRequestStore')->name('license-request.store');
            Route::get('license-request', 'index')->name('license-request');
            Route::get('license-request/LicenseRequestByClient', 'LicenseRequestByClient')->name('license-request.LicenseRequestByClient');
            Route::get('license-request/LicenseRequestByAssignedPerson/{id}', 'LicenseRequestByAssignedPerson')->name('license-request.LicenseRequestByAssignedPerson');
            Route::post('license-request/send', 'licenseRequestUpdate')->name('license-request.send');
            Route::post('license-request/approve', 'licenseRequestApprove')->name('license-request.approve');
            Route::post('license-request/disapprove', 'licenseRequestDisapprove')->name('license-request.disapprove');
        });
        //support solution list
        Route::controller('App\Http\Controllers\Api\V2\SupportSolutionListController')->group(function () {
            Route::get('support-solution-list', 'index')->name('support.solution.list');
        });
        Route::controller('App\Http\Controllers\Api\V2\ClientInformationController')->group(function () {
            Route::get('/client-search', 'clientSearch')->name('clientSearch');
            Route::get('/customer-softwares', 'clientAllSoftwares')->name('customer-softwares');
        });

        Route::controller('App\Http\Controllers\Api\V2\PaymentController')->group(function () {
            Route::post('/payment-alert/store', 'store')->name('payment-alert.store');
            Route::get('/payment-alert/payment-for-support-person', 'paymentAlertForSupport')->name('payment-alert.payment-for-support-person');
            Route::post('/payment-alert/payment-generated-by-support-person', 'paymentGeneratedBySupportPerson')->name('payment-alert.payment-generated-by-support-person');
            Route::get('/payment-alert/all', 'paymentAlertAll')->name('payment.alert.all');
            Route::get('/payment-alert/paymentAlertByClient', 'paymentAlertByClient')->name('payment-alert.paymentAlertByClient');
            Route::get('/payment-alert/paymentAlertbyAssignedPerson/{id}', 'paymentAlertbyAssignedPerson')->name('payment-alert.paymentAlertbyAssignedPerson');
            Route::post('/payment-alert/assign', 'paymentAlertAssign')->name('payment.alert.assign');
            Route::post('/payment-alert/seen', 'paymentAlertSeen')->name('payment.alert.seen');
            Route::post('/payment-alert/updatePaymentBySupport', 'updatePaymentAlertForSupportPerson')->name('payment-alert.updatePaymentBySupport');
        });

        Route::controller('App\Http\Controllers\Api\V2\UserDashboardController')->group(function () {
            Route::get('/user_dashboard', 'user_dashboard')->name('user_dashboard');
        });

        Route::controller('App\Http\Controllers\Api\V2\ChangeRequestController')->group(function () {
            Route::get('/change_request', 'index');
            Route::post('/change_request', 'store');
            Route::get('/change_request_by_client', 'changeRequestByClient');
        });

        Route::apiResource('divisions', 'App\Http\Controllers\Api\V2\DivisionController');
        Route::apiResource('districts', 'App\Http\Controllers\Api\V2\DistrictController');
        Route::apiResource('areas', 'App\Http\Controllers\Api\V2\AreaController');

        Route::controller('App\Http\Controllers\Api\V2\SyncController')->group(function () {
            Route::get('syncCustomer', 'syncCustomer');
        });
        Route::group(['prefix' => 'customer'], function () {
            Route::controller('App\Http\Controllers\Api\V2\CustomerController')->group(function () {
                Route::get('all', 'index');
                Route::get('allWithoutPaginate', 'allWithoutPaginate');
                Route::post('storeOrUpdate', 'storeOrUpdate');
                Route::post('register', 'register');
                Route::post('support/add/{id}', 'supportAdd');
                Route::post('saleperson/add/{id}', 'salepersonAdd');
                Route::post('leadperson/add/{id}', 'leadpersonAdd');
                Route::post('support/delete/{id}', 'supportDelete');
                Route::post('software/add/{id}', 'softwareAdd');
                Route::post('software/delete/{id}', 'softwareDelete');
                Route::post('users/add/{id}', 'usersAdd');
                Route::post('user/checkuserexistence', 'checkuserexistence');
                Route::post('users/update', 'usersUpdate');
                Route::post('users/delete/{id}', 'usersDelete');
                Route::post('assign_role/{id}', 'assign_role');
                Route::get('details/{id}', 'details');
                Route::get('supportList/{id}', 'supportList');
                Route::get('moneyReceitList/{id}', 'moneyReceiptList');
                Route::get('moneyReceiptTempale/{mrno}', 'moneyReceiptTempale');
                Route::get('allUsers', 'allUsers');
                Route::post('push-message', 'pushMessage');
            });
        });
        //software-type
        Route::group(['prefix' => 'software/type'], function () {
            Route::controller('App\Http\Controllers\Api\V2\SoftwareTypeController')->group(function () {
                Route::post('store', 'store')->name('software.type.store');
                Route::get('index', 'index')->name('software.type.index');
                Route::post('update/{id}', 'update')->name('software.type.update');
                Route::delete('delete/{id}', 'destroy')->name('software.type.delete');
            });
        });
        //ProblemType
        Route::group(['prefix' => 'problemtype'], function () {
            Route::controller('App\Http\Controllers\Api\V2\ProblemTypeController')->group(function () {
                Route::get('show', 'index');
                Route::post('store', 'store');
                Route::get('edit/{id}', 'edit')->name('problemtype.edit');
                Route::put('update/{id}', 'update')->name('problemtype.update');
                Route::delete('destroy/{id}', 'destroy')->name('problemtype.destroy');
            });
        });
        //  Registation
        //        Route::apiResource('user', 'App\Http\Controllers\Api\V2\RegisterController');
        //        Route::controller('App\Http\Controllers\Api\V2\RegisterController')->group(function () {
        ////            Route::get('/user/edit/{id}', 'edit')->name('user.edit');
        ////            Route::delete('/user/destroy/{id}', 'destroy');
        //            // Add Permissiom
        //            Route::post('/user/add_permission', 'add_permission')->name('roles.permission');
        //        });


        //support profile
        Route::controller('App\Http\Controllers\Api\V2\SupportProfileController')->group(function () {
            Route::get('/support-profile-list', 'index');
            Route::get('/support-profile-dashboard', 'supportProfileDashboard');
            Route::get('/support-done-by-support-person', 'supportGivenBySupportPerson');
            Route::get('/supportPersonAllData', 'allData');

        });
        Route::controller('App\Http\Controllers\Api\V2\LoginLogController')->group(function () {
            Route::get('/login-log', 'loginLog');
        });

        //software
        //        Route::apiResource('software', 'App\Http\Controllers\Api\V2\SoftwareController')->except(['destroy']);
        //        Route::controller('App\Http\Controllers\Api\V2\SoftwareController')->group(function () {
        //            Route::delete('software/{id}', 'destroy')->name('software.destroy');
        //        });

        //        Route::controller('App\Http\Controllers\Api\V2\SaleInformationController')->group(function () {
        //            Route::get('sale-information', 'saleinformation');
        //        });
        //
        //        Route::controller('App\Http\Controllers\Api\V2\SaleInformationController')->group(function () {
        //            Route::get('sales-person-list', 'salespersonlist');
        //        });

        // Route::controller('App\Http\Controllers\Api\V2\SaleInformationController')->group(function () {
        //     Route::get('search-by-sales-person', 'searchbysalesperson');
        //     Route::get('days-sales-information', 'daysales');
        //     Route::get('sales-person-list', 'salespersonlist');
        //     Route::get('sale-information', 'saleinformation');
        // });
        Route::controller('App\Http\Controllers\Api\V2\SaleInformationController')->group(function () {
            Route::get('search-by-sales-person', 'searchbysalesperson');
            Route::get('days-sales-information', 'daysales');
            Route::get('sales-person-list', 'salespersonlist');
            Route::get('sales-information', 'saleinformation');
        });

        Route::controller('App\Http\Controllers\Api\V2\SalesPersonController')->group(function () {
            Route::get('sale-information', 'sales_information');
            Route::get('search_by_sales_person', 'search_by_sales_person');
            Route::get('search_by_sales_person_for_dashboard', 'search_by_sales_person_for_dashboard');
        });

        Route::controller('App\Http\Controllers\Api\V2\QuickAccessController')->group(function () {
            Route::get('bySupportExecutive', 'bySupportExecutive');
        });

        Route::controller('App\Http\Controllers\Api\V2\SupportInquiryController')->group(function () {
            Route::get('support-inquiry/support_executive_list', 'support_executive_list');
            Route::get('support-inquiry/support_for_clients', 'support_for_clients');
            Route::get('support-inquiry/most-hungry-software', 'mostHungrySoftware');
            Route::get('support-inquiry/client-support-info', 'clientSupportInfo');

        });
        Route::controller('App\Http\Controllers\Api\V2\AdminDashboardController')->group(function () {
            Route::get('admin-dashboard', 'adminDashboard');
            Route::get('home-dashboard', 'homeDashboard');
            Route::get('review_manager', 'reviewManager');
            Route::get('home_dashboard_with_pagination', 'homeDashboardWithPagination');
        });

        //for test
        Route::controller('App\Http\Controllers\Api\V2\AdminDashboardController')->group(function () {
            Route::get('v3/home-dashboard', 'v3homeDashboard');
        });

        Route::controller('App\Http\Controllers\Api\V2\ClientDashboardController')->group(function () {
            Route::get('client-dashboard', 'clientDashBoard');
            Route::post('client_review', 'clientReview');
            Route::get('client_reviewed_support', 'reviewedSupport');
        });
        //support profile
        Route::controller('App\Http\Controllers\Api\V2\SupportProfileController')->group(function () {
            Route::get('/support-profile-list', 'index');
            Route::get('/support-profile-individual', 'SupportProfileDetails');
            Route::get('/support-client-billing/{id}', 'support_billing_info');
            Route::get('/clients_by_support_person', 'ClientsBySupportPerson');
            Route::get('/clients_by_support_person_for_license_generate', 'ClientsBySupportPersonForApp');
            Route::get('/customer_monitoring_for_support_person', 'customer_monitoring_by_support_person');
            Route::get('/individual_support', 'individualSupport');
            Route::get('/customer_monitoring_for_billing_incharge', 'customer_monitoring_for_billing_incharge');
        });
    });


    Route::controller('App\Http\Controllers\Api\V2\RoleController')->group(function () {
        Route::get('rolesWithoutPagination', 'rolesWithoutPagination');
        Route::get('/roles/edit/{id}', 'edit')->name('roles.edit');
        Route::delete('/roles/destroy/{id}', 'destroy')->name('roles.destroyy');
        // Add Permissiom
        Route::post('/roles/add_permission', 'add_permission')->name('roles.permission');
        Route::post('/roles/copy_role', 'copy_role')->name('roles.copy_role');
    });

    //software-type
    Route::group(['prefix' => 'software/type'], function () {
        Route::controller('App\Http\Controllers\Api\V2\SoftwareTypeController')->group(function () {
            Route::post('store', 'store')->name('software.type.store');
            Route::get('index', 'index')->name('software.type.index');
            Route::post('update/{id}', 'update')->name('software.type.update');
            Route::delete('delete/{id}', 'destroy')->name('software.type.delete');
        });
    });

    //problem sub type controller
    Route::resource('problem-sub-types', 'App\Http\Controllers\Api\V2\ProblemSubTypeController')->except(['destroy']);
    Route::controller('App\Http\Controllers\Api\V2\ProblemSubTypeController')->group(function () {
        Route::delete('problem-sub-types/{id}', 'destroy')->name('problem-sub-types.destroy');
    });


    //  Registation
Route::get('user/list', 'App\Http\Controllers\Api\V2\RegisterController@userlist');
Route::resource('user', 'App\Http\Controllers\Api\V2\RegisterController')->except(['destroy', 'update']);

// Route::get('list', 'App\Http\Controllers\Api\V2\RegisterController@userlist');


    Route::get('temp_user_list', 'App\Http\Controllers\Api\V2\RegisterController@getTempUsers');
    Route::delete('temp_user_list/{id}', 'App\Http\Controllers\Api\V2\RegisterController@deleteTempUser');

    Route::controller('App\Http\Controllers\Api\V2\RegisterController')->group(function () {
        // Add Permissiom
        Route::post('/user-update/{id}', 'update')->name('user.update');
    });

    Route::controller('App\Http\Controllers\Api\V2\RegisterController')->group(function () {
        // Add Permissiom
        Route::post('/user/add_permission', 'add_permission')->name('roles.permission');
    });
    Route::delete('user/{id}', 'App\Http\Controllers\Api\V2\RegisterController@destroy')
    ->name('user.destroy');
    //ProblemType
    Route::controller('App\Http\Controllers\Api\V2\ProblemTypeController')->group(function () {
        Route::get('/problemtype/show', 'index');
        Route::post('/problemtype/store', 'store');
        Route::get('/problemtype/edit/{id}', 'edit')->name('problemtype.edit');
        Route::put('/problemtype/update/{id}', 'update')->name('problemtype.update');
        Route::delete('/problemtype/destroy/{id}', 'destroy')->name('problemtype.destroy');
        // Add Permissiom
        //        Route::post('/problemtype/add_permission', 'add_permission')->name('roles.permission');
    });


    Route::controller('App\Http\Controllers\Api\V2\LoginLogController')->group(function () {
        Route::get('/login-log', 'loginLog');
    });

    //software
    Route::get('software/client-wise-software', 'App\Http\Controllers\Api\V2\SoftwareController@clientWiseSoftware')->name('software.client-wise-software');
    Route::resource('software', 'App\Http\Controllers\Api\V2\SoftwareController');
    Route::controller('App\Http\Controllers\Api\V2\SoftwareController')->group(function () {
        //        Route::delete('software/{id}', 'destroy')->name('software.destroy');
        Route::get('software/details/{id}', 'details')->name('software.details');
    });

    //filter login user
    Route::controller('App\Http\Controllers\Api\V2\LoginLogController')->group(function () {
        Route::get('filter-login-user', 'filterloginuser');
    });

    //training status
    Route::controller('App\Http\Controllers\Api\V2\TrainingScheduleController')->group(function () {
        Route::post('training-status', 'trainingstatus');
        Route::post('training-assign', 'trainingassign');
        Route::post('training-seen', 'trainingSeen');
    });

    Route::controller('App\Http\Controllers\Api\V2\UserController')->group(function () {
        Route::get('user-list', 'userlist');
        Route::get('sale-lead-list', 'saleleadlist');
        Route::get('getallsupportperson', 'supporPerson');
    });


    Route::apiResource('permissions', 'App\Http\Controllers\Api\V2\PermissionController')->except(['destroy', 'edit']);
    Route::controller('App\Http\Controllers\Api\V2\PermissionController')->group(function () {
        Route::get('/permissions/edit/{id}', 'edit')->name('permissions.edit');
        Route::get('/permissions/destroy/{id}', 'destroy')->name('permissions.destroy');
        // Add Permissionsection
        Route::get('/permissions/permission/section', 'permissionsection')->name('permission.permissionsection');
    });


    Route::get('/signup_visibility', [App\Http\Controllers\Api\V2\BusinessSettingController::class, 'getSignupVisibility']);
    Route::post('/update_signup_visibility', [App\Http\Controllers\Api\V2\BusinessSettingController::class, 'updateSignupVisibility']);
    Route::get('/check_support_app_version', [App\Http\Controllers\Api\V2\BusinessSettingController::class, 'checkSupportAppVersion']);
    Route::post('/update_support_app_version', [App\Http\Controllers\Api\V2\BusinessSettingController::class, 'updateSupportAppVersion']);


    Route::get('/test',function(){
        return 'Test Route';
    });
});
Route::fallback(function () {
    return response()->json([
        'data' => [],
        'success' => false,
        'status' => 404,
        'message' => 'Invalid Route'
    ]);
});
