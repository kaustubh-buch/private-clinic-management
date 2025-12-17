@extends('admin.layouts.admin')
@section('content')

<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-lg-3 col-md-2">
          </div>
          <div class="col-sm-6">
            <h1>Add Insurance</h1>
          </div>
          <div class="col-lg-3 col-md-4 col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" title="Dashboard">Dashboard</a></li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.insurance.index') }}" title="Insurance">Insurance</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
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
                    <h3 class="card-title"></h3>
                </div>
                <form id="insurance" action="{{ route('admin.insurance.store') }}" method="POST" class="insurance_create">
                  @csrf
                  <div class="card-body">
                    <div class="form-group">
                        <label for="abbreviation">Insurance Abbreviation</label>
                        <input type="text" class="form-control" id="abbreviation" name="abbreviation" placeholder="Enter Abbreviation" maxlength="{{ config('constants.MAX_LENGTH.ABBREVIATION') }}">
                        @error('abbreviation')
                            <span id="abbreviation-error" class="error invalid-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="common_name">Name commonly referred to as</label>
                        <input type="text" class="form-control" id="common_name" name="common_name" placeholder="Enter Common name" maxlength="{{ config('constants.MAX_LENGTH.COMMON_NAME') }}">
                        @error('common_name')
                            <span id="common_name-error" class="error invalid-error">{{ $message }}</span>
                        @enderror
                    </div>
                     <div class="form-group">
                        <label for="admin_status">Status</label>
                        <select class="form-control" id="admin_status" name="admin_status">
                            <option value="">-- Select Status --</option>
                            <option value="approved" {{ old('admin_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="inactive" {{ old('admin_status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('admin_status')
                            <span id="status-error" class="error invalid-error">{{ $message }}</span>
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

        $(".insurance_create").validate({
            rules: {
                abbreviation: {
                    required: true,
                    maxlength: "{{ config('constants.MAX_LENGTH.ABBREVIATION') }}"
                },
                common_name: {
                    required: true,
                    maxlength: "{{ config('constants.MAX_LENGTH.COMMON_NAME') }}"
                },
                admin_status: {
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
                admin_status: {
                    required: "{{ __('messages.validation.admin_status_required') }}",
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
        showLoaderIfFormValid('.insurance_create');


    });
</script>
@endpush