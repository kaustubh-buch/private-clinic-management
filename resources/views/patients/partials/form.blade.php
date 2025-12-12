<div class="mb-3">
    <label class="form-label">First Name</label>
    <input type="text" name="first_name" class="form-control" 
        value="{{ old('first_name', $patient->first_name ?? '') }}">
    @error('first_name') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Last Name</label>
    <input type="text" name="last_name" class="form-control" 
        value="{{ old('last_name', $patient->last_name ?? '') }}">
    @error('last_name') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" 
        value="{{ old('email', $patient->email ?? '') }}">
    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Contact Number</label>
    <input type="text" name="contact_number" class="form-control"
        value="{{ old('contact_number', $patient->contact_number ?? '') }}">
    @error('contact_number') <small class="text-danger">{{ $message }}</small> @enderror
</div>
