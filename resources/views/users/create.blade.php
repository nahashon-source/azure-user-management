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
        background: linear-gradient(145deg, #f0f2f5, #ffffff);
        border-radius: 12px;
        padding: 20px;
        border: 2px solid #e1e8ed;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .module-card:hover {
        border-color: #3498db;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .module-card.active {
        border-color: #27ae60;
        background: linear-gradient(145deg, #e8f5e8, #f0f8f0);
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
                                    <option value="{{ $location->code }}" 
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
                                <div class="module-header">
                                    <input type="checkbox" 
                                           class="module-checkbox" 
                                           id="{{ $module->code }}Module"
                                           name="modules[{{ $module->code }}][enabled]"
                                           value="1"
                                           onchange="toggleModule('{{ $module->code }}')"
                                           {{ old("modules.{$module->code}.enabled") ? 'checked' : '' }}>
                                    <span class="module-name">{{ $module->name }}</span>
                                </div>
                                <div class="role-group" id="{{ $module->code }}Roles">
                                    <label>
                                        <input type="checkbox" 
                                               value="all" 
                                               onchange="toggleAllRoles(this, '{{ $module->code }}')"
                                               {{ count(old("modules.{$module->code}.role_ids", [])) == $module->roles->count() ? 'checked' : '' }}
                                               {{ old("modules.{$module->code}.enabled") ? '' : 'disabled' }}>
                                        All Roles
                                    </label>
                                    @foreach($module->roles as $role)
                                        <label>
                                            <input type="checkbox" 
                                                   name="modules[{{ $module->code }}][role_ids][]" 
                                                   value="{{ $role->id }}"
                                                   onchange="validateRoles(this, '{{ $module->code }}')"
                                                   {{ in_array($role->id, old("modules.{$module->code}.role_ids", [])) ? 'checked' : '' }}
                                                   {{ count(old("modules.{$module->code}.role_ids", [])) == $module->roles->count() ? 'disabled' : '' }}
                                                   {{ old("modules.{$module->code}.enabled") ? '' : 'disabled' }}>
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

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize module states
        Object.keys(moduleData).forEach(moduleCode => {
            const checkbox = document.getElementById(moduleCode + 'Module');
            const roleGroup = document.getElementById(moduleCode + 'Roles');
            const moduleCard = checkbox.closest('.module-card');
            const allCheckbox = roleGroup.querySelector('input[value="all"]');
            const individualCheckboxes = roleGroup.querySelectorAll('input[name^="modules["]:not([value="all"])');

            if (checkbox.checked) {
                moduleCard.classList.add('active');
                allCheckbox.disabled = false;
                individualCheckboxes.forEach(cb => cb.disabled = allCheckbox.checked);
            }
        });
        
        updatePermissions();
    });

    function toggleModule(moduleCode) {
        const checkbox = document.getElementById(moduleCode + 'Module');
        const roleGroup = document.getElementById(moduleCode + 'Roles');
        const moduleCard = checkbox.closest('.module-card');
        const allCheckbox = roleGroup.querySelector('input[value="all"]');
        const individualCheckboxes = roleGroup.querySelectorAll('input[name^="modules["]:not([value="all"])');
        
        if (checkbox.checked) {
            moduleCard.classList.add('active');
            allCheckbox.disabled = false;
            individualCheckboxes.forEach(cb => {
                cb.disabled = false;
                // If no roles selected, perhaps select all by default or leave empty
            });
            if (individualCheckboxes.length > 0 && Array.from(individualCheckboxes).every(cb => !cb.checked) && !allCheckbox.checked) {
                allCheckbox.checked = true;
                toggleAllRoles(allCheckbox, moduleCode);
            }
        } else {
            moduleCard.classList.remove('active');
            allCheckbox.checked = false;
            allCheckbox.disabled = true;
            individualCheckboxes.forEach(cb => {
                cb.checked = false;
                cb.disabled = true;
            });
        }
        
        updatePermissions(moduleCode);
    }

    function toggleAllRoles(allCheckbox, moduleCode) {
        const roleGroup = document.getElementById(moduleCode + 'Roles');
        const individualCheckboxes = roleGroup.querySelectorAll('input[name^="modules["]:not([value="all"])');
        
        individualCheckboxes.forEach(cb => {
            cb.checked = allCheckbox.checked;
            cb.disabled = allCheckbox.checked;
        });
        
        updatePermissions(moduleCode);
    }

    function validateRoles(checkbox, moduleCode) {
        const roleGroup = document.getElementById(moduleCode + 'Roles');
        const allCheckbox = roleGroup.querySelector('input[value="all"]');
        const individualCheckboxes = roleGroup.querySelectorAll('input[name^="modules["]:not([value="all"])');
        
        if (checkbox.checked) {
            allCheckbox.checked = false;
            individualCheckboxes.forEach(cb => cb.disabled = false);
        }
        
        const anyIndividualChecked = Array.from(individualCheckboxes).some(cb => cb.checked);
        if (!anyIndividualChecked) {
            allCheckbox.checked = true;
            toggleAllRoles(allCheckbox, moduleCode);
        }
        
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
        
        showLoading();
        
        axios.get(`/api/companies/${location}`)
            .then(response => {
                companySelect.innerHTML = '<option value="">Select Company</option>';
                response.data.forEach(company => {
                    companySelect.innerHTML += `<option value="${company.id}">${company.name}</option>`;
                });
                hideLoading();
            })
            .catch(error => {
                console.error('Error loading companies:', error);
                showAlert('error', 'Error', 'Failed to load companies');
                hideLoading();
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
            const checkbox = document.getElementById(moduleCode + 'Module');
            const roleGroup = document.getElementById(moduleCode + 'Roles');
            
            if (checkbox && roleGroup && checkbox.checked) {
                const selectedCheckboxes = roleGroup.querySelectorAll('input[name^="modules["]:checked:not([value="all"])');
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
        
        const formData = new FormData(this);
        
        // Handle 'all' checkboxes before submission
        Object.keys(moduleData).forEach(moduleCode => {
            const roleGroup = document.getElementById(moduleCode + 'Roles');
            const allCheckbox = roleGroup.querySelector('input[value="all"]');
            if (allCheckbox && allCheckbox.checked) {
                allCheckbox.checked = false; // Deselect 'all'
            }
        });
        
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        showLoading();
        
        // Submit to Laravel backend
        axios.post('{{ route("users.store") }}', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        .then(response => {
            if (response.data.success) {
                showAlert('success', 'Success', 'User created successfully!');
                setTimeout(() => {
                    window.location.href = '{{ route("dashboard.index") }}';
                }, 2000);
            } else {
                throw new Error(response.data.message || 'Failed to create user');
            }
        })
        .catch(error => {
            console.error('Error creating user:', error);
            
            let errorMessage = 'Failed to create user';
            if (error.response && error.response.data) {
                if (error.response.data.errors) {
                    const errors = error.response.data.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                    
                    Object.keys(errors).forEach(field => {
                        const element = document.getElementById(field);
                        if (element) {
                            element.classList.add('is-invalid');
                        }
                    });
                } else if (error.response.data.message) {
                    errorMessage = error.response.data.message;
                }
            }
            
            showAlert('error', 'Error', errorMessage);
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
            const checkbox = document.getElementById(moduleCode + 'Module');
            const roleGroup = document.getElementById(moduleCode + 'Roles');
            const selectedRoles = roleGroup.querySelectorAll('input[name^="modules["]:checked:not([value="all"])').length;
            if (checkbox.checked && selectedRoles > 0) {
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