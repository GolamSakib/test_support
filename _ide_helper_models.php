<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Addon
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $unique_identifier
 * @property string|null $version
 * @property int $activated
 * @property string|null $image
 * @property string|null $purchase_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Addon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Addon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Addon query()
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereActivated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon wherePurchaseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereUniqueIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Addon whereVersion($value)
 */
	class Addon extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Address
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $address
 * @property int|null $country_id
 * @property int $state_id
 * @property int|null $city_id
 * @property float|null $longitude
 * @property float|null $latitude
 * @property string|null $postal_code
 * @property string|null $phone
 * @property int $set_default
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\State|null $state
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereSetDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Address whereUserId($value)
 */
	class Address extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Area
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $district_id
 * @property int|null $division_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\District|null $district
 * @property-read \App\Models\Division|null $division
 * @method static \Illuminate\Database\Eloquent\Builder|Area newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Area newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Area query()
 * @method static \Illuminate\Database\Eloquent\Builder|Area whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Area whereDistrictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Area whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Area whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Area whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Area whereUpdatedAt($value)
 */
	class Area extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Brand
 *
 * @property int $id
 * @property string $name
 * @property string|null $logo
 * @property int $top
 * @property string|null $slug
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Brand newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand query()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereUpdatedAt($value)
 */
	class Brand extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BusinessSetting
 *
 * @property int $id
 * @property string $type
 * @property string|null $value
 * @property string|null $lang
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessSetting whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessSetting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessSetting whereValue($value)
 */
	class BusinessSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Complain
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $user_name
 * @property string|null $client_phone
 * @property int|null $assigned_to
 * @property int|null $software_id
 * @property int $is_seen
 * @property int $is_forward
 * @property int|null $shop_id
 * @property int|null $customer_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Complain newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Complain newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Complain query()
 * @method static \Illuminate\Database\Eloquent\Builder|Complain whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complain whereClientPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complain whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complain whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complain whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complain whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complain whereIsForward($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complain whereIsSeen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complain whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complain whereSoftwareId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complain whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complain whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complain whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complain whereUserName($value)
 */
	class Complain extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Currency
 *
 * @property int $id
 * @property string $name
 * @property string $symbol
 * @property float $exchange_rate
 * @property int $status
 * @property string|null $code
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency query()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereUpdatedAt($value)
 */
	class Currency extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Customer
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $accountant_phone
 * @property int $status
 * @property string|null $sms_phone_no
 * @property string|null $agreement_date
 * @property string|null $operation_start_date
 * @property int|null $customer_id
 * @property int|null $is_registered
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereAccountantPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereAgreementDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereIsRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereOperationStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereSmsPhoneNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereUpdatedAt($value)
 */
	class Customer extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CustomerSoftware
 *
 * @property int $id
 * @property int|null $customer_id
 * @property string|null $software_name
 * @property int|null $software_id
 * @property int|null $sale_by
 * @property int|null $lead_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Customer|null $customer
 * @property-read \App\Models\User|null $leadBy
 * @property-read \App\Models\User|null $saleBy
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerSoftware newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerSoftware newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerSoftware query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerSoftware whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerSoftware whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerSoftware whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerSoftware whereLeadBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerSoftware whereSaleBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerSoftware whereSoftwareId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerSoftware whereSoftwareName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerSoftware whereUpdatedAt($value)
 */
	class CustomerSoftware extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\District
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $division_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Area> $areas_under_district
 * @property-read int|null $areas_under_district_count
 * @property-read \App\Models\Division|null $division
 * @method static \Illuminate\Database\Eloquent\Builder|District newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|District newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|District query()
 * @method static \Illuminate\Database\Eloquent\Builder|District whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|District whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|District whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|District whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|District whereUpdatedAt($value)
 */
	class District extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Division
 *
 * @property int $id
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\District> $districts
 * @property-read int|null $districts_count
 * @method static \Illuminate\Database\Eloquent\Builder|Division newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Division newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Division query()
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereUpdatedAt($value)
 */
	class Division extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Inventory
 *
 * @property int $id
 * @property string|null $user_name
 * @property string|null $client_phone
 * @property string|null $client_name
 * @property string|null $note
 * @property int|null $assigned_to
 * @property int|null $software_id
 * @property int $is_seen
 * @property int $is_approved
 * @property int $is_assigned
 * @property int|null $customer_id
 * @property int $is_done
 * @property string|null $completed_note
 * @property int|null $shop_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory query()
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereClientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereClientPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereCompletedNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereIsAssigned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereIsDone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereIsSeen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereSoftwareId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereUserName($value)
 */
	class Inventory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Language
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $app_lang_code
 * @property int $rtl
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Language newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Language newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Language query()
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereAppLangCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereRtl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Language whereUpdatedAt($value)
 */
	class Language extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\License
 *
 * @property int $id
 * @property int|null $shop_id
 * @property int|null $user_id
 * @property int|null $software_id
 * @property string|null $license_no
 * @property string|null $note
 * @property int|null $assigned_to
 * @property int $is_seen
 * @property int $is_approved
 * @property int $is_done
 * @property int $is_accept
 * @property string|null $license_note
 * @property string|null $request_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|License newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|License newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|License query()
 * @method static \Illuminate\Database\Eloquent\Builder|License whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereIsAccept($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereIsDone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereIsSeen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereLicenseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereLicenseNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereRequestTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereSoftwareId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|License whereUserId($value)
 */
	class License extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LoginLog
 *
 * @property int $id
 * @property int $user_id
 * @property string $login_time
 * @property string $logout_time
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $login_ip
 * @property string|null $city
 * @property string|null $country
 * @property string|null $area_address
 * @property string|null $timezone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog whereAreaAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog whereLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog whereLoginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog whereLogoutTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginLog whereUserId($value)
 */
	class LoginLog extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Payment
 *
 * @property int $id
 * @property int|null $shop_id
 * @property int|null $user_id
 * @property int|null $software_id
 * @property string|null $payment_type
 * @property string|null $client_phone
 * @property string $payment_amount
 * @property string|null $collection_note
 * @property int|null $assigned_to
 * @property string|null $note
 * @property int $is_seen
 * @property int $is_assigned
 * @property int $is_collected
 * @property int|null $customer_id
 * @property int $is_accept
 * @property string|null $assigned_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAssignedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereClientPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCollectionNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereIsAccept($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereIsAssigned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereIsCollected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereIsSeen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePaymentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereSoftwareId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUserId($value)
 */
	class Payment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PermissionSection
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionSection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionSection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionSection query()
 */
	class PermissionSection extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Plan
 *
 * @property int $id
 * @property string|null $plan
 * @property string|null $description
 * @property int|null $customer_id
 * @property string|null $start_time
 * @property string|null $end_time
 * @property int|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Plan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan wherePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereUpdatedAt($value)
 */
	class Plan extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlanPerson
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $plan_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPerson newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPerson newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPerson query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPerson whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPerson whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPerson wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPerson whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanPerson whereUserId($value)
 */
	class PlanPerson extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProblemSubType
 *
 * @property int $id
 * @property string $title
 * @property int $problem_type_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ProblemType|null $problem
 * @method static \Illuminate\Database\Eloquent\Builder|ProblemSubType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProblemSubType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProblemSubType query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProblemSubType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProblemSubType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProblemSubType whereProblemTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProblemSubType whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProblemSubType whereUpdatedAt($value)
 */
	class ProblemSubType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ProblemType
 *
 * @property int $id
 * @property string|null $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProblemSubType> $subproblem
 * @property-read int|null $subproblem_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProblemType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProblemType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProblemType query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProblemType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProblemType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProblemType whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProblemType whereUpdatedAt($value)
 */
	class ProblemType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Role
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RoleTranslation> $role_translations
 * @property-read int|null $role_translations_count
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereUpdatedAt($value)
 */
	class Role extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\RoleTranslation
 *
 * @property int $id
 * @property int $role_id
 * @property string $name
 * @property string $lang
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Role|null $role
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTranslation whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTranslation whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTranslation whereUpdatedAt($value)
 */
	class RoleTranslation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SmsTemplate
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SmsTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsTemplate query()
 */
	class SmsTemplate extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Software
 *
 * @property int $id
 * @property string|null $software_name
 * @property int|null $software_type_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SoftwareType|null $softwaretypes
 * @method static \Illuminate\Database\Eloquent\Builder|Software newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Software newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Software query()
 * @method static \Illuminate\Database\Eloquent\Builder|Software whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Software whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Software whereSoftwareName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Software whereSoftwareTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Software whereUpdatedAt($value)
 */
	class Software extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SoftwareSupportPerson
 *
 * @property int $id
 * @property int|null $customer_software_id
 * @property int|null $software_id
 * @property int|null $user_id
 * @property int $is_support
 * @property int $is_billing_in_charge
 * @property int $is_supervisor
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $support_person
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSupportPerson newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSupportPerson newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSupportPerson query()
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSupportPerson whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSupportPerson whereCustomerSoftwareId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSupportPerson whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSupportPerson whereIsBillingInCharge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSupportPerson whereIsSupervisor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSupportPerson whereIsSupport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSupportPerson whereSoftwareId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSupportPerson whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSupportPerson whereUserId($value)
 */
	class SoftwareSupportPerson extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SoftwareType
 *
 * @property int $id
 * @property string|null $software_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Software> $software
 * @property-read int|null $software_count
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareType query()
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareType whereSoftwareType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareType whereUpdatedAt($value)
 */
	class SoftwareType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Staff
 *
 * @property int $id
 * @property int $user_id
 * @property int $role_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Role|null $role
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Staff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Staff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Staff query()
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Staff whereUserId($value)
 */
	class Staff extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\State
 *
 * @property int $id
 * @property string $name
 * @property int $country_id
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|State newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|State newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|State query()
 * @method static \Illuminate\Database\Eloquent\Builder|State whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|State whereUpdatedAt($value)
 */
	class State extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Support
 *
 * @property int $id
 * @property string|null $title
 * @property int|null $problem_type_id
 * @property int|null $user_id
 * @property string|null $description
 * @property string|null $image
 * @property int|null $is_urgent
 * @property int|null $shop_id
 * @property int|null $is_done
 * @property int|null $is_processing
 * @property int|null $is_pending
 * @property int|null $is_support
 * @property int|null $accepted_support_id
 * @property string|null $requested_time
 * @property string|null $completed_time
 * @property string|null $assigned_time
 * @property int|null $is_accepted
 * @property int|null $is_transfer
 * @property int|null $is_rated
 * @property int|null $is_assigned
 * @property int|null $is_helped
 * @property float|null $rating
 * @property string|null $rating_comment
 * @property int|null $software_id
 * @property int|null $helped_by
 * @property int|null $refused_by
 * @property int|null $is_requested_help
 * @property int|null $is_help_request_seen
 * @property int|null $is_help_done
 * @property string|null $pron_file_url
 * @property string|null $support_time_taken
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $customer_id
 * @property-read \App\Models\User|null $customer
 * @property-read \App\Models\ProblemType|null $problemType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SoftwareSupportPerson> $softwareSupportPerson
 * @property-read int|null $software_support_person_count
 * @method static \Illuminate\Database\Eloquent\Builder|Support newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Support newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Support query()
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereAcceptedSupportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereAssignedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereCompletedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereHelpedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereIsAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereIsAssigned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereIsDone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereIsHelpDone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereIsHelpRequestSeen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereIsHelped($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereIsPending($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereIsProcessing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereIsRated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereIsRequestedHelp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereIsSupport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereIsTransfer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereIsUrgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereProblemTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support wherePronFileUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereRatingComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereRefusedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereRequestedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereSoftwareId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereSupportTimeTaken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Support whereUserId($value)
 */
	class Support extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SupportRating
 *
 * @property int $id
 * @property int|null $support_id
 * @property string|null $comment
 * @property int $is_rated
 * @property string|null $solved_text
 * @property string|null $suggestion_text
 * @property float|null $rating
 * @property int|null $shop_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SupportRating newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SupportRating newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SupportRating query()
 * @method static \Illuminate\Database\Eloquent\Builder|SupportRating whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportRating whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportRating whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportRating whereIsRated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportRating whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportRating whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportRating whereSolvedText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportRating whereSuggestionText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportRating whereSupportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportRating whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportRating whereUserId($value)
 */
	class SupportRating extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Training
 *
 * @property int $id
 * @property int|null $shop_id
 * @property string|null $note
 * @property int $is_accept
 * @property string|null $training_start_time
 * @property string|null $training_end_time
 * @property int $is_seen
 * @property int $is_approved
 * @property int $is_done
 * @property int $is_assigned
 * @property int|null $software_id
 * @property int|null $user_id
 * @property int|null $no_of_person
 * @property int|null $assigned_to
 * @property string|null $training_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $customer_id
 * @property-read \App\Models\User|null $assigned
 * @property-read \App\Models\Customer|null $customer
 * @property-read \App\Models\shop|null $shop
 * @method static \Illuminate\Database\Eloquent\Builder|Training newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Training newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Training query()
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereIsAccept($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereIsAssigned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereIsDone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereIsSeen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereNoOfPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereSoftwareId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereTrainingEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereTrainingStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereTrainingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Training whereUserId($value)
 */
	class Training extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Translation
 *
 * @property int $id
 * @property string|null $lang
 * @property string|null $lang_key
 * @property string|null $lang_value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Translation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Translation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Translation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Translation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Translation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Translation whereLang($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Translation whereLangKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Translation whereLangValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Translation whereUpdatedAt($value)
 */
	class Translation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Upload
 *
 * @property int $id
 * @property string|null $file_original_name
 * @property string|null $file_name
 * @property int|null $user_id
 * @property int|null $file_size
 * @property string|null $extension
 * @property string|null $type
 * @property string|null $external_link
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Upload newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Upload newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Upload onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Upload query()
 * @method static \Illuminate\Database\Eloquent\Builder|Upload whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Upload whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Upload whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Upload whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Upload whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Upload whereFileOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Upload whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Upload whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Upload whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Upload whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Upload whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Upload withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Upload withoutTrashed()
 */
	class Upload extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $username
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property string|null $phone_no
 * @property string|null $designation
 * @property string|null $photo
 * @property string $user_type
 * @property string|null $address
 * @property int $status
 * @property int $login_status
 * @property string|null $firebase_token
 * @property string|null $last_login
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $verification_code
 * @property string|null $remember_token
 * @property int|null $customer_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Customer> $clients
 * @property-read int|null $clients_count
 * @property-read \App\Models\CustomerSoftware|null $customer_software
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SoftwareSupportPerson> $softwareSupportPerson
 * @property-read int|null $software_support_person_count
 * @property-read \App\Models\Staff|null $staff
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDesignation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirebaseToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLoginStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhoneNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereVerificationCode($value)
 */
	class User extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

namespace App\Models{
/**
 * App\Models\shop
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $address
 * @property string|null $phone
 * @property int|null $user_id
 * @property int|null $customer_id
 * @property int $status
 * @property int|null $division_id
 * @property int|null $district_id
 * @property int|null $area_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Area|null $area
 * @property-read \App\Models\Customer|null $customer
 * @property-read \App\Models\District|null $district
 * @property-read \App\Models\Division|null $division
 * @method static \Illuminate\Database\Eloquent\Builder|shop newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|shop newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|shop query()
 * @method static \Illuminate\Database\Eloquent\Builder|shop whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|shop whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|shop whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|shop whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|shop whereDistrictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|shop whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|shop whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|shop whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|shop wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|shop whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|shop whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|shop whereUserId($value)
 */
	class shop extends \Eloquent {}
}

