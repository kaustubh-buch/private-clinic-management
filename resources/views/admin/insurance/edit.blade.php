@extends('admin.layouts.admin')
@section('content')

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-lg-3 col-md-2">
        </div>
        <div class="col-sm-6">
          <h1>Edit Insurance</h1>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" title="Dashboard">Dashboard</a></li>
            <li class="breadcrumb-item">
                <a href="{{ route('admin.insurance.index') }}" title="Insurance">Insurance</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-3 col-md-2">
        </div>
        <div class="col-lg-6 col-md-8">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Edit Insurance</h3>
            </div>

            <form id="editForm" action="{{ route('admin.insurance.update', $insurance->id) }}" method="POST" class="insurance_edit">
              @csrf
              @method('PUT')

              <div class="card-body">
                <div class="form-group">
                  <label for="abbreviation">Insurance Abbreviation</label>
                  <input type="text" class="form-control" id="abbreviation" name="abbreviation" placeholder="Enter Abbreviation"
                    value="{{ old('abbreviation', $insurance->abbreviation) }}">
                  @error('abbreviation')
                    <span id="abbreviation-error" class="error invalid-error">{{ $message }}</span>
                  @enderror
                </div>

                <div class="form-group">
                  <label for="common_name">Name commonly referred to as</label>
                  <input type="text" class="form-control" id="common_name" name="common_name" placeholder="Enter Common name"
                    value="{{ old('common_name', $insurance->common_name) }}">
                  @error('common_name')
                    <span id="common_name-error" class="error invalid-error">{{ $message }}</span>
                  @enderror
                </div>
                <div class="form-group">
                  <label for="admin_status">Status</label>
                  <select name="admin_status" id="admin_status" class="form-control">
                      <option value="">Select Status</option>
                      <option value="approved" {{ old('admin_status', $insurance->admin_status) == 'approved' ? 'selected' : '' }}>Approved</option>
                      <option value="inactive" {{ old('admin_status', $insurance->admin_status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                  </select>
                  @error('admin_status')
                    <span id="admin_status-error" class="error invalid-error">{{ $message }}</span>
                  @enderror
                </div>
              </div>

              <div class="card-footer">
                <button type="button" onclick="window.location.href='{{ route('admin.insurance.index') }}'" class="btn btn-secondary" title="Back">Back</button>
                <button type="submit" class="btn btn-primary" title="Submit">Submit</button>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </section>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function () {
      
        $(".insurance_edit").validate({
            rules: {
                abbreviation: {
                    required: true,
                },
                common_name: {
                    required: true,
                },
                status: {
                    required: true,
                }
            },
            messages: {
                abbreviation: {
                    required: "{{ __('messages.validation.abbreviation_required') }}",
                },
                common_name: {
                    required: "{{ __('messages.validation.common_name_required') }}",
                },
                status: {
                    required: "{{ __('messages.validation.status_required') }}",
                }
            },
            errorPlacement: function (error, element) {
                if (element.attr("name") == "common_name") {
                    error.insertAfter("#common_name");
                } else {
                    error.insertAfter(element);
                }
            }
        });
        showLoaderIfFormValid('.insurance_edit');

        
    });
</script>
@endpush
