<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // User validation
            'user' => 'required|array|min:1',
            'user.*.name' => 'required|string|max:255',
            'user.*.email' => 'nullable|email|unique:addusers_users,email',
            'user.*.phone' => 'required|string|regex:/^[0-9]{11}$/',
            'user.*.designation' => 'nullable|string|max:255',
            'user.*.shop_address' => 'nullable|string|max:255',
            'user.*.district_id' => 'required|exists:addusers_district,id',
            'user.*.area' => 'nullable|exists:areas,id',
            'user.*.image' => 'nullable|image|max:2048', // Optional image, max 2MB

            // Software validation
            'software' => 'required|array|min:1',
            'software.*' => 'exists:client_support_admin_softwarelistall,id',

            // Support Person validation
            'support_person' => 'required|array|min:1',
            'support_person.*.supportPerson' => 'required|exists:supportadmin_support_user,id',
            'support_person.*.billingIncharge' => 'nullable',
            'support_person.*.supervisor' => 'nullable',

            // Customer validation
            'customer_id' => 'required|exists:addusers_customer,id',

            // Sales validation
            'sales' => 'required|array|min:1',
            'sales.*' => 'exists:supportadmin_support_user,id',

            // Leads validation
            'leads' => 'required|array|min:1',
            'leads.*' => 'exists:supportadmin_support_user,id',
        ];
    }

    public function messages()
    {
        return [
            // User validation messages
            'user.required' => 'User information is required.',
            'user.*.name.required' => 'User name is required.',
            'user.*.email.required' => 'User email is required.',
            'user.*.email.email' => 'Please provide a valid email address.',
            'user.*.email.unique' => 'This email is already registered.',
            'user.*.phone.required' => 'Phone number is required.',
            'user.*.phone.regex' => 'Phone number must be 11 digits.',
            'user.*.district_id.required' => 'District is required.',
            'user.*.district_id.exists' => 'Selected district is invalid.',

            // Software validation messages
            'software.required' => 'Software information is required.',
            'software.*.exists' => 'One or more selected software are invalid.',

            // Support Person validation messages
            'support_person.required' => 'Support person information is required.',
            'support_person.*.supportPerson.required' => 'Support person is required.',
            'support_person.*.supportPerson.exists' => 'Selected support person is invalid.',

            // Customer validation messages
            'customer_id.required' => 'Customer ID is required.',
            'customer_id.exists' => 'Customer not found.',

            // Sales validation messages
            'sales.required' => 'Sales person information is required.',
            'sales.*.exists' => 'One or more selected sales persons are invalid.',

            // Leads validation messages
            'leads.required' => 'Leads information is required.',
            'leads.*.exists' => 'One or more selected leads are invalid.',
        ];
    }
}
