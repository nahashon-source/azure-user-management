@extends('layouts.app')

@section('title', 'Create New User')

@section('page-title', 'Azure User Management System')

@section('header-actions')
    <a href="{{ route('dashboard.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Back to Dashboard
    </a>
@endsection

@section('additional-styles')
<style>
    .main-content {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        align-items: start;
    }

    .user-form-panel {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .form-section {
        margin-bottom: 30px;
    }

    .section-title {
        font-size: 1.4em;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .form-group input,
    .form-group select {
        padding: 12px 15px;
        border: 2px solid #e1e8ed;
        border-radius: 10px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: white;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .form-group input.is-invalid,
    .form-group select.is-invalid {
        border-color: #e74c3c;
    }

    .invalid-feedback {
        color: #e74c3c;
        font-size: 0.875em;
        margin-top: 5px;
    }

    .modules-section {
        margin-top: 30px;
    }

    .modules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

 .module-card {
        background: #e9ecef;
        border-radius: 12px;
        padding: 20px;
        border: 2px solid #dee2e6;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .module-card:hover {
        background: #dfe3e7;
        border-color: #adb5bd;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .module-card.active {
        border: 3px solid #27ae60 !important;
        background: #d4edda !important;
    }

    .module-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .module-checkbox {
        width: 18px;
        height: 18px;
    }

    .module-name {
        font-weight: 600;
        color: #2c3e50;
    }

    .role-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
        margin-top: 10px;
        max-height: 150px;
        overflow-y: auto;
    }

    .role-group label {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .role-group input[type="checkbox"]:checked + * {
        font-weight: bold;
        color: #27ae60;
    }

    .role-group input[type="checkbox"]:checked {
        transform: scale(1.2);
    }

    .permissions-display {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-top: 15px;
    }

    .permissions-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .permission-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .permission-tag {
        background: #3498db;
        color: white;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.9em;
        font-weight: 500;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 30px;
    }

    @media (max-width: 768px) {
        .main-content {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .modules-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
    <div class="main-content">
        <!-- User Form Panel -->
        <div class="user-form-panel">
            <form id="userForm" method="POST" action="{{ route('users.store') }}">
                @csrf
                
                <!-- User Information Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-server"></i>
                        User Information
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name"><i class="fas fa-user"></i> Name *</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required 
                                   class="@error('name') is-invalid @enderror">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="employee_id"><i class="fas fa-id-badge"></i> Employee ID *</label>
                            <input type="text" 
                                   id="employee_id" 
                                   name="employee_id" 
                                   value="{{ old('employee_id') }}" 
                                   required
                                   class="@error('employee_id') is-invalid @enderror">
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email ID *</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required
                                   class="@error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone"></i> Phone No *</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}" 
                                   required
                                   class="@error('phone') is-invalid @enderror">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="location"><i class="fas fa-map-marker-alt"></i> Location *</label>
                            <select id="location" 
                                    name="location" 
                                    required
                                    class="@error('location') is-invalid @enderror"
                                    onchange="loadCompaniesByLocation()">
                                <option value="">Select Location</option>
                                @foreach($locations as $location)
                                    <option value="{{ strtolower($location->name) }}" 
                                            {{ old('location') == $location->code ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="company_id"><i class="fas fa-building"></i> Company *</label>
                            <select id="company_id" 
                                    name="company_id" 
                                    required
                                    class="@error('company_id') is-invalid @enderror">
                                <option value="">Select Company</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" 
                                            {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="job_title"><i class="fas fa-briefcase"></i> Job Title</label>
                            <input type="text" 
                                   id="job_title" 
                                   name="job_title" 
                                   value="{{ old('job_title') }}" 
                                   class="@error('job_title') is-invalid @enderror">
                            @error('job_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="department"><i class="fas fa-building"></i> Department</label>
                            <input type="text" 
                                   id="department" 
                                   name="department" 
                                   value="{{ old('department') }}" 
                                   class="@error('department') is-invalid @enderror">
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Module Selection Section -->
                <div class="modules-section">
                    <div class="section-title">
                        <i class="fas fa-puzzle-piece"></i>
                        Module & Role Assignment
                    </div>
                    
                    <div class="modules-grid">
                        @foreach($modules as $module)
                            <div class="module-card" data-module-code="{{ $module->code }}">
                                <input type="hidden" 
                                       name="modules[{{ $module->code }}][enabled]" 
                                       value="{{ old("modules.{$module->code}.enabled") ? 1 : 0 }}" 
                                       id="{{ $module->code }}Enabled">
                                <div class="module-header">
                                    <span class="module-name">{{ $module->name }}</span>
                                </div>
                                <div class="role-group" id="{{ $module->code }}Roles">
                                    <label>
                                        <input type="checkbox" 
                                               value="all" 
                                               onchange="toggleAllRoles(this, '{{ $module->code }}')"
                                               {{ count(old("modules.{$module->code}.role_ids", [])) == $module->roles->count() ? 'checked' : '' }}>
                                        All Roles
                                    </label>
                                    @foreach($module->roles as $role)
                                        <label>
                                            <input type="checkbox" 
                                                   name="modules[{{ $module->code }}][role_ids][]" 
                                                   value="{{ $role->id }}"
                                                   onchange="validateRoles(this, '{{ $module->code }}')"
                                                   {{ in_array($role->id, old("modules.{$module->code}.role_ids", [])) ? 'checked' : '' }}>
                                            {{ $role->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="permissions-display">
                        <div class="permissions-title">
                            <i class="fas fa-shield-alt"></i>
                            Selected Permissions:
                        </div>
                        <div class="permission-tags" id="permissionTags">
                            <span class="permission-tag">No permissions selected</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="button" class="btn btn-secondary" onclick="cancelForm()">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('additional-scripts')
<script>
    // Module data for JavaScript access
    const moduleData = @json($modules->keyBy('code'));
    const rolePermissions = @json($rolePermissions ?? []);

    function updateModuleEnabled(moduleCode) {
        const roleGroup = document.getElementById(moduleCode + 'Roles');
        if (!roleGroup) return;
        const allCheckbox = roleGroup.querySelector('input[value="all"]');
        const individualCheckboxes = roleGroup.querySelectorAll('input[name^="modules["]:not([value="all"])');
        const anyChecked = (allCheckbox && allCheckbox.checked) || [...individualCheckboxes].some(cb => cb.checked);
        const hiddenEnabled = document.getElementById(moduleCode + 'Enabled');
        if (hiddenEnabled) {
            hiddenEnabled.value = anyChecked ? '1' : '0';
        }
        const moduleCard = document.querySelector(`[data-module-code="${moduleCode}"]`);
        if (moduleCard) {
            if (anyChecked) {
                moduleCard.classList.add('active');
            } else {
                moduleCard.classList.remove('active');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        Object.keys(moduleData).forEach(moduleCode => {
            updateModuleEnabled(moduleCode);
        });
        
        updatePermissions();
    });

    function toggleAllRoles(allCheckbox, moduleCode) {
        const roleGroup = document.getElementById(moduleCode + 'Roles');
        const individualCheckboxes = roleGroup.querySelectorAll('input[name^="modules["]:not([value="all"])');
        
        individualCheckboxes.forEach(cb => {
            cb.checked = allCheckbox.checked;
        });
        
        updateModuleEnabled(moduleCode);
        updatePermissions(moduleCode);
    }

    function validateRoles(checkbox, moduleCode) {
        const roleGroup = document.getElementById(moduleCode + 'Roles');
        const allCheckbox = roleGroup.querySelector('input[value="all"]');
        const individualCheckboxes = roleGroup.querySelectorAll('input[name^="modules["]:not([value="all"])');
        
        if (!checkbox.checked) {
            allCheckbox.checked = false;
        } else if ([...individualCheckboxes].every(cb => cb.checked)) {
            allCheckbox.checked = true;
        }
        
        updateModuleEnabled(moduleCode);
        updatePermissions(moduleCode);
    }

    function loadCompaniesByLocation() {
        const locationSelect = document.getElementById('location');
        const companySelect = document.getElementById('company_id');
        const location = locationSelect.value;
        
        if (!location) {
            companySelect.innerHTML = '<option value="">Select Company</option>';
            return;
        }
        
        // Show loading state
        companySelect.innerHTML = '<option value="">Loading companies...</option>';
        companySelect.disabled = true;
        
        // Use fetch instead of axios (more reliable, no dependencies)
        fetch(`/api/companies/${location}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(companies => {
                companySelect.innerHTML = '<option value="">Select Company</option>';
                
                if (companies.length === 0) {
                    companySelect.innerHTML += '<option value="">No companies found</option>';
                } else {
                    companies.forEach(company => {
                        const option = document.createElement('option');
                        option.value = company.id;
                        option.textContent = company.name;
                        companySelect.appendChild(option);
                    });
                }
                
                companySelect.disabled = false;
            })
            .catch(error => {
                console.error('Error loading companies:', error);
                companySelect.innerHTML = '<option value="">Failed to load companies</option>';
                companySelect.disabled = false;
                
                // Show alert if function exists
                if (typeof showAlert === 'function') {
                    showAlert('error', 'Error', 'Failed to load companies. Please try again.');
                } else {
                    alert('Failed to load companies. Please try again.');
                }
            });
    }

    function cancelForm() {
        confirmAction(
            'Cancel Form',
            'Are you sure you want to cancel? All unsaved changes will be lost.',
            function() {
                window.location.href = '{{ route("dashboard.index") }}';
            }
        );
    }

    function updatePermissions(changedModule = null) {
        const permissionTags = document.getElementById('permissionTags');
        const permissions = [];
        
        Object.keys(moduleData).forEach(moduleCode => {
            const roleGroup = document.getElementById(moduleCode + 'Roles');
            const selectedCheckboxes = roleGroup.querySelectorAll('input[name^="modules["]:checked:not([value="all"])');
            
            if (roleGroup && selectedCheckboxes.length > 0) {
                selectedCheckboxes.forEach(option => {
                    const roleId = option.value;
                    const roleName = option.closest('label').textContent.trim();
                    permissions.push(`${moduleData[moduleCode].name}: ${roleName}`);
                    
                    if (changedModule === moduleCode) {
                        const permissionsForRole = rolePermissions[roleId] || [];
                        permissionsForRole.forEach(permission => {
                            permissions.push(`${moduleData[moduleCode].name}: ${permission}`);
                        });
                    }
                });
            }
        });
        
        if (permissions.length === 0) {
            permissionTags.innerHTML = '<span class="permission-tag">No permissions selected</span>';
        } else {
            permissionTags.innerHTML = permissions.map(permission => 
                `<span class="permission-tag">${permission}</span>`
            ).join('');
        }
    }
// Form submission
document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!validateForm()) {
        return;
    }
    
    // Build modules array with debugging
    let moduleIndex = 0;
    const modulesDebug = [];
    
    Object.keys(moduleData).forEach(moduleCode => {
        const roleGroup = document.getElementById(moduleCode + 'Roles');
        if (!roleGroup) {
            console.warn(`Role group not found for ${moduleCode}`);
            return;
        }
        
        const selectedRoles = roleGroup.querySelectorAll('input[name^="modules["]:checked:not([value="all"])');
        
        if (selectedRoles.length > 0) {
            modulesDebug.push(`${moduleCode}: ${selectedRoles.length} roles`);
        }
        
        selectedRoles.forEach(roleCheckbox => {
            moduleIndex++;
        });
    });
    
    console.log('Module summary:', modulesDebug.join(', '));
    console.log('Total module assignments:', moduleIndex);
    
    // Check if any modules selected
    if (moduleIndex === 0) {
        showAlert('warning', 'No Modules Selected', 'Please select at least one role from any module before submitting.');
        return;
    }
    
    showLoading();
    
    // Build FormData
    const formData = new FormData();
    
    // Add basic fields
    formData.append('name', document.getElementById('name').value);
    formData.append('employee_id', document.getElementById('employee_id').value);
    formData.append('email', document.getElementById('email').value);
    formData.append('phone', document.getElementById('phone').value);
    formData.append('location', document.getElementById('location').value);
    formData.append('company_id', document.getElementById('company_id').value);
    formData.append('job_title', document.getElementById('job_title').value || '');
    formData.append('department', document.getElementById('department').value || '');
    formData.append('_token', document.querySelector('input[name="_token"]').value);
    
    // Build modules array
    moduleIndex = 0;
    Object.keys(moduleData).forEach(moduleCode => {
        const roleGroup = document.getElementById(moduleCode + 'Roles');
        const selectedRoles = roleGroup.querySelectorAll('input[name^="modules["]:checked:not([value="all"])');
        
        selectedRoles.forEach(roleCheckbox => {
            formData.append(`modules[${moduleIndex}][module_id]`, moduleData[moduleCode].id);
            formData.append(`modules[${moduleIndex}][role_id]`, roleCheckbox.value);
            formData.append(`modules[${moduleIndex}][location]`, document.getElementById('location').value);
            moduleIndex++;
        });
    });
    
    // Submit
    fetch('{{ route("users.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || `HTTP ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert('success', 'Success', 'User created successfully!');
            setTimeout(() => {
                window.location.href = '{{ route("dashboard.index") }}';
            }, 2000);
        } else {
            throw new Error(data.message || 'Failed to create user');
        }
    })
    .catch(error => {
        console.error('Submission error:', error);
        showAlert('error', 'Error', error.message || 'Failed to create user. Please try again.');
    })
    .finally(() => {
        hideLoading();
    });
});
    function validateForm() {
        let isValid = true;
        const requiredFields = ['name', 'employee_id', 'email', 'phone', 'location', 'company_id'];
        
        requiredFields.forEach(field => {
            const element = document.getElementById(field);
            if (!element.value.trim()) {
                element.classList.add('is-invalid');
                isValid = false;
            } else {
                element.classList.remove('is-invalid');
            }
        });
        
        // Check modules
        let hasValidModule = false;
        Object.keys(moduleData).forEach(moduleCode => {
            const roleGroup = document.getElementById(moduleCode + 'Roles');
            const selectedRoles = roleGroup.querySelectorAll('input[name^="modules["]:checked:not([value="all"])').length;
            if (selectedRoles > 0) {
                hasValidModule = true;
            }
        });
        
        if (!hasValidModule) {
            showAlert('warning', 'Warning', 'Please select at least one module with at least one role');
            isValid = false;
        }
        
        return isValid;
    }

    // Utility functions (assumed to be defined in layouts.app or elsewhere)
    function showLoading() {
        // Implementation depends on your app's loading indicator
        console.log('Loading...');
    }

    function hideLoading() {
        // Implementation depends on your app's loading indicator
        console.log('Loading stopped.');
    }

    function showAlert(type, title, message) {
        Swal.fire({
            icon: type,
            title: title,
            html: message
        });
    }

    function confirmAction(title, message, callback) {
        Swal.fire({
            title: title,
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                callback();
            }
        });
    }
</script>
@endsection