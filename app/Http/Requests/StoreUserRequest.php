<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
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
                'unique:users,employee_id',
                'regex:/^[A-Z0-9-]+$/'
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email'
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
                'in:kenya,uganda'
            ],
            'company_id' => [
                'required',
                'integer',
                'exists:companies,id'
            ],
            'job_title' => [
                'nullable',
                'string',
                'max:255'
            ],
            'department' => [
                'nullable',
                'string',
                'max:255'
            ],
            // FIXED: Accept numeric array of modules
            'modules' => [
                'required',
                'array',
                'min:1'
            ],
            'modules.*.module_id' => [
                'required',
                'integer',
                'exists:modules,id'
            ],
            'modules.*.role_id' => [
                'required',
                'integer',
                'exists:roles,id'
            ],
            'modules.*.location' => [
                'nullable',
                'string',
                'max:255'
            ]
        ];
    }

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
            'modules.required' => 'Please select at least one module.',
            'modules.min' => 'Please select at least one module with a role.',
            'modules.*.module_id.required' => 'Module ID is required.',
            'modules.*.module_id.exists' => 'The selected module is invalid.',
            'modules.*.role_id.required' => 'Role is required for each module.',
            'modules.*.role_id.exists' => 'The selected role is invalid.'
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name ?? ''),
            'employee_id' => strtoupper(trim($this->employee_id ?? '')),
            'email' => strtolower(trim($this->email ?? '')),
            'phone' => preg_replace('/[^0-9\+\-\(\)\s]/', '', $this->phone ?? ''),
            'location' => strtolower($this->location ?? '')
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $modules = $this->input('modules', []);
            
            // Check if at least one module is provided
            if (empty($modules)) {
                $validator->errors()->add('modules', 'At least one module with a role must be assigned');
            }
            
            // Validate each module has required fields
            foreach ($modules as $index => $module) {
                if (empty($module['module_id'])) {
                    $validator->errors()->add("modules.{$index}.module_id", 'Module ID is required');
                }
                if (empty($module['role_id'])) {
                    $validator->errors()->add("modules.{$index}.role_id", 'Role is required');
                }
            }
        });
    }
}