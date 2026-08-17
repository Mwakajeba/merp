@php
    $isEdit = isset($user);
@endphp

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bx bx-error-circle me-2"></i>
    Please fix the following errors:
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<form action="{{ $isEdit ? route('users.update', $user) : route('users.store') }}"
      method="POST" id="userForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <div class="col-12">
            <div class="card radius-10 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name ?? '') }}"
                                   placeholder="Enter full name" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $user->phone ?? '') }}"
                                   placeholder="255XXXXXXXXX" required>
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email ?? '') }}"
                                   placeholder="Enter email address (optional)">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            @php
                                $isSuspendedForReprint = $isEdit
                                    && ($user->status ?? '') === 'suspended'
                                    && ($user->status_reason ?? '') === \App\Services\Sales\PosReceiptPrintService::BLOCK_REASON;
                                $canReactivateSuspended = auth()->user()->hasAnyRole(['admin', 'super-admin']);
                            @endphp
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $user->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $user->status ?? 'active') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                @if($isEdit && ($user->status ?? '') === 'suspended')
                                <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }} disabled>Suspended</option>
                                @endif
                            </select>
                            @if($isSuspendedForReprint)
                            <small class="text-danger d-block mt-2">
                                This account was suspended for attempting to reprint a POS receipt.
                                @if($canReactivateSuspended)
                                    Change status to <strong>Active</strong> to reactivate.
                                @else
                                    Only an administrator can reactivate this account.
                                @endif
                            </small>
                            @endif
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card radius-10 mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bx bx-shield-quarter me-2"></i>Account & Access</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role_id" id="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}"
                                        {{ old('role_id', $isEdit ? ($user->roles->first()->id ?? '') : '') == $role->id ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if(!$isEdit)
                        <div class="col-md-6 mb-3">
                            <label class="form-label">POS Login PIN</label>
                            <input type="text" class="form-control" value="Auto-generated (4 digits)" disabled>
                            <div class="form-text">A random PIN will be created and shown after saving.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Password</label>
                            <input type="text" class="form-control" value="12345 (user can change later)" disabled>
                            <div class="form-text">Used for phone/password login until the user updates it.</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(!$isEdit)
        <div class="col-12">
            <div class="card radius-10 mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bx bx-buildings me-2"></i>Branch & Location</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Assign the user's primary branch and location now. To give access to more than one branch or location later, use
                        <strong>Assign Branch</strong> and <strong>Assign Locations</strong> on the user profile page.
                    </p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" id="branch_id"
                                    class="form-control @error('branch_id') is-invalid @enderror" required>
                                <option value="">Select Branch</option>
                                @foreach(($branches ?? []) as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="location_id" class="form-label">Location <span class="text-danger">*</span></label>
                            <select name="location_id" id="location_id"
                                    class="form-control @error('location_id') is-invalid @enderror" required disabled>
                                <option value="">Select branch first</option>
                            </select>
                            @error('location_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($isEdit)
        <div class="col-12">
            <div class="card radius-10 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-git-branch me-2"></i>Branch & Location</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">
                        To change branch or location access, open the user profile and use
                        <strong>Assign/View Branch</strong> and <strong>Assign/View Locations</strong>.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card radius-10 mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bx bx-key me-2"></i>POS Login PIN</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Current PIN</label>
                            <input type="text" class="form-control" value="{{ $user->getPlainPin() ?? '—' }}" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pin" class="form-label">New PIN</label>
                            <input type="text" name="pin" id="pin"
                                   class="form-control @error('pin') is-invalid @enderror"
                                   maxlength="4" inputmode="numeric" pattern="[0-9]{4}"
                                   placeholder="Enter new 4-digit PIN"
                                   value="{{ old('pin') }}">
                            @error('pin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Leave blank to keep the current PIN.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-between">
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to Users
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> {{ $isEdit ? 'Update User' : 'Create User' }}
        </button>
    </div>
</form>

@push('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
document.addEventListener('DOMContentLoaded', function () {
    const pinInput = document.getElementById('pin');
    const userForm = document.getElementById('userForm');
    const branchSelect = document.getElementById('branch_id');
    const locationSelect = document.getElementById('location_id');
    const savedLocationId = @json(old('location_id'));

    function loadLocations(branchId, selectedLocationId = null) {
        if (!locationSelect) {
            return;
        }

        if (!branchId) {
            locationSelect.innerHTML = '<option value="">Select branch first</option>';
            locationSelect.disabled = true;
            return;
        }

        locationSelect.innerHTML = '<option value="">Loading locations...</option>';
        locationSelect.disabled = true;

        fetch(`{{ url('/api/branches') }}/${branchId}/locations`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((response) => response.json())
            .then((locations) => {
                locationSelect.innerHTML = '<option value="">Select Location</option>';

                if (!locations.length) {
                    locationSelect.innerHTML = '<option value="">No locations for this branch</option>';
                    return;
                }

                locations.forEach((location) => {
                    const option = document.createElement('option');
                    option.value = location.id;
                    option.textContent = location.name;
                    if (String(selectedLocationId) === String(location.id)) {
                        option.selected = true;
                    }
                    locationSelect.appendChild(option);
                });

                locationSelect.disabled = false;
            })
            .catch(() => {
                locationSelect.innerHTML = '<option value="">Could not load locations</option>';
            });
    }

    if (branchSelect && locationSelect) {
        branchSelect.addEventListener('change', function () {
            loadLocations(this.value);
        });

        if (branchSelect.value) {
            loadLocations(branchSelect.value, savedLocationId);
        }
    }

    if (pinInput) {
        pinInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 4);
        });
    }

    if (userForm) {
        userForm.addEventListener('submit', function (e) {
            const role = document.getElementById('role_id').value;

            if (!document.getElementById('name').value.trim()) {
                e.preventDefault();
                alert('Please enter the user\'s full name.');
                return false;
            }

            if (!document.getElementById('phone').value.trim()) {
                e.preventDefault();
                alert('Please enter the user\'s phone number.');
                return false;
            }

            if (!role) {
                e.preventDefault();
                alert('Please select a role.');
                return false;
            }

            if (branchSelect && !branchSelect.value) {
                e.preventDefault();
                alert('Please select a branch.');
                return false;
            }

            if (locationSelect && !locationSelect.value) {
                e.preventDefault();
                alert('Please select a location.');
                return false;
            }
        });
    }
});
</script>
@endpush
