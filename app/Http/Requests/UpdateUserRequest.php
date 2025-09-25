<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We handle authorization in middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-Z\s]+$/' // Only letters and spaces
            ],
            'employee_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'employee_id')->ignore($userId),
                'regex:/^[A-Z0-9-]+$/' // Uppercase letters, numbers, and hyphens
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/' // Phone number format
            ],
            'location' => [
                'required',
                'string',
                'in:kenya,uganda'
            ],
            'company_id' => [
                'required',
                'integer',
                'exists:companies,id'
            ],
            'modules' => [
                'sometimes',
                'array'
            ],
            'modules.*.enabled' => [
                'sometimes',
                'boolean'
            ],
            'modules.*.role_id' => [
                'required_if:modules.*.enabled,1',
                'integer',
                'exists:roles,id'
            ],
            'status' => [
                'sometimes',
                'string',
                'in:active,pending,inactive'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.regex' => 'The name may only contain letters and spaces.',
            'employee_id.required' => 'The employee ID is required.',
            'employee_id.unique' => 'This employee ID is already taken.',
            'employee_id.regex' => 'Employee ID must contain only uppercase letters, numbers, and hyphens.',
            'email.required' => 'The email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone.required' => 'The phone number is required.',
            'phone.regex' => 'Please enter a valid phone number.',
            'location.required' => 'Please select a location.',
            'location.in' => 'The selected location is invalid.',
            'company_id.required' => 'Please select a company.',
            'company_id.exists' => 'The selected company is invalid.',
            'modules.*.role_id.required_if' => 'Please select a role for the enabled module.',
            'modules.*.role_id.exists' => 'The selected role is invalid.',
            'status.in' => 'The selected status is invalid.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'name',
            'employee_id' => 'employee ID',
            'email' => 'email address',
            'phone' => 'phone number',
            'location' => 'location',
            'company_id' => 'company',
            'modules' => 'modules',
            'status' => 'status'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format data before validation
        $this->merge([
            'name' => trim($this->name ?? ''),
            'employee_id' => strtoupper(trim($this->employee_id ?? '')),
            'email' => strtolower(trim($this->email ?? '')),
            'phone' => preg_replace('/[^0-9\+\-\(\)\s]/', '', $this->phone ?? ''),
            'location' => strtolower($this->location ?? '')
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // If modules are being updated, validate enabled modules have roles
            $modules = $this->input('modules', []);
            
            foreach ($modules as $moduleCode => $moduleData) {
                if (isset($moduleData['enabled']) && $moduleData['enabled']) {
                    if (empty($moduleData['role_id'])) {
                        $validator->errors()->add(
                            "modules.{$moduleCode}.role_id",
                            "Please select a role for the {$moduleCode} module."
                        );
                    }
                }
            }
        });
    }
}