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
                'regex:/^[a-zA-Z\s]+$/'
            ],
            'employee_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'employee_id')->ignore($userId),
                'regex:/^[A-Z0-9-]+$/'
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
                'regex:/^[\+]?[0-9\s\-\(\)]+$/'
            ],
          'location' => [
    'required',
    'string',
    'exists:locations,code'
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
            'modules.*.location' => [
                'sometimes',
                'array'
            ],
            'modules.*.location.*' => [
                'sometimes',
                'string',
                'exists:locations,code'
            ],
            'modules.*.module_id' => [
                'sometimes',
                'array'
            ],
            'modules.*.module_id.*' => [
                'sometimes',
                'integer',
                'exists:modules,id'
            ],
            'modules.*.role_id' => [
                'sometimes',
                'array'
            ],
            'modules.*.role_id.*' => [
                'sometimes',
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
            'modules.*.location.*.exists' => 'The selected location for the module is invalid.',
            'modules.*.module_id.*.exists' => 'The selected module is invalid.',
            'modules.*.role_id.*.exists' => 'The selected role for the module is invalid.',
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
            'modules.*.location' => 'module location',
            'modules.*.module_id' => 'module ID',
            'modules.*.role_id' => 'module role',
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
            // If modules are being updated, validate that enabled modules have both module_id and role_id
            $modules = $this->input('modules', []);
            
            foreach ($modules as $index => $moduleData) {
                $locations = $moduleData['location'] ?? [];
                $moduleIds = $moduleData['module_id'] ?? [];
                $roleIds = $moduleData['role_id'] ?? [];

                // Check if any of the arrays are non-empty (indicating an assignment attempt)
                if (!empty($locations) || !empty($moduleIds) || !empty($roleIds)) {
                    // Ensure all required fields are present
                    if (empty($moduleIds)) {
                        $validator->errors()->add(
                            "modules.{$index}.module_id",
                            "Please select at least one module for assignment."
                        );
                    }
                    if (empty($roleIds)) {
                        $validator->errors()->add(
                            "modules.{$index}.role_id",
                            "Please select at least one role for the module."
                        );
                    }
                    if (empty($locations)) {
                        $validator->errors()->add(
                            "modules.{$index}.location",
                            "Please select at least one location for the module."
                        );
                    }
                }
            }
        });
    }
}