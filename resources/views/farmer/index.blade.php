@extends('layout.app')
@section('page_name', 'Farmer')

@push('style')
    <style>
        .dataTables_filter {
            margin-bottom: 0;
        }

        .dataTables_filter input {
            height: 32px;
        }

        table.dataTable td {
            vertical-align: middle;
        }

        td:last-child {
            white-space: nowrap;
        }
    </style>
@endpush

@section('content')
    <main class="content">
        <div class="container-fluid p-0">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between filter-row mb-3 flex-wrap gap-3">

                                <!-- Search will appear here -->
                                <div id="custom-search"></div>

                                <a class="btn btn-primary btn-sm d-flex align-items-center" type="button" data-bs-toggle="modal" data-bs-target="#discountModal">
                                    <img src="{{ asset('images/icons/plus.png') }}" class="me-2" width="10px">
                                    Add Farmer
                                </a>
                            </div>

                            <div class="table-responsive">
                                <table id="datatables-reponsive" class="table-striped w-100 table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Nick Name</th>
                                            <th>Mobile Number</th>
                                            <th>Location</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($farmers as $index => $farmer)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $farmer->name }}</td>
                                                <td>{{ $farmer->nick }}</td>
                                                <td>{{ $farmer->phone }}</td>
                                                <td>{{ $farmer->location }}</td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <button class="editCourse" data-id="{{ $farmer->id }}" data-name="{{ $farmer->name }}"  data-whatsapp="{{ $farmer->whats_up }}"
                                                                                   data-nick="{{ $farmer->nick }}" data-phone="{{ $farmer->phone }}" data-location="{{ $farmer->location }}" 
                                                                                   data-bs-toggle="modal" data-bs-target="#discountModalEdit">
                                                            <img src="{{ asset('images/icons/Edit.png') }}" width="20px">
                                                        </button>

                                                        <!-- print -->
                                                        <a href="{{ route('farmer.qr_code', ['farmer_id' => $farmer->id]) }}" class="changeStatus">
                                                            <img src="{{ asset('images/icons/printer.png') }}" width="20px">
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                           
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Modal -->
    <div class="modal fade" id="discountModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-17 fw-medium" id="discountModalLabel">Add Farmer</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form action="{{ route('farmer.store') }}" method="POST" onsubmit="document.getElementById('submit_btn').disabled=true;">
                        @csrf

                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Name <span class="fs-15 text-danger">*</span></label>
                                <input type="text" name="name" class="form-control">
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Nick Name</label>
                                <input type="text" name="nick" class="form-control">
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Mobile Number <span class="fs-15 text-danger">*</span></label>
                                <!-- <input type="text" name="phone" class="form-control" minlength="10" maxlength="10" pattern="[0-9]{10}" inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" required> -->
                                <input type="text" id="phone" name="phone" class="form-control" minlength="10" maxlength="10" pattern="[0-9]{10}"
                                        inputmode="numeric" oninput="checkPhone(this)" required>
                            </div>
                            <span id="phone_error" class="text-danger"></span>

                            <div class="col-md-12 mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">

                                    <label for="whatsapp_number" class="col-form-label fs-14 mb-0">
                                        What's App Number
                                    </label>

                                    <div class="form-check d-flex align-items-center mb-0 gap-1">
                                        <input class="form-check-input mt-0 shadow-none" type="checkbox" id="same_as_phone" style="width: 18px; height: 18px; cursor: pointer;">
                                        <label class="form-check-label fw-semibold text-secondary" for="same_as_phone" style="font-size: 13px; cursor: pointer;">
                                            Same as Phone Number
                                        </label>
                                    </div>

                                </div>

                                <input type="text" class="form-control shadow-none" id="whatsapp_number" name="whats_up">
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Location <span class="fs-15 text-danger">*</span></label>
                                <input type="text" name="location" class="form-control">
                            </div>
                        </div>

                </div>

                <div class="modal-footer px-0">
                    <div class="row w-100">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="submit" id="submit_btn" class="btn btn-primary w-100">
                                Save
                            </button>
                        </div>
                    </div>
                </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="discountModalEdit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="discountModalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-17 fw-medium" id="discountModalEditLabel">Edit Farmer</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form method="POST" action="{{ route('farmer.edit.store') }}" onsubmit="document.getElementById('submit_btn').disabled=true;">
                        @csrf

                        <input type="hidden" name="farmer_id" id="edit_user_id">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Name <span class="fs-15 text-danger">*</span></label>
                                <input type="text" name="name" id="edit_name" class="form-control">
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Nick Name</label>
                                <input type="text" name="nick_name" id="edit_nick_name" class="form-control">
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Mobile Number <span class="fs-15 text-danger">*</span></label>
                                <input type="text" name="phone" id="edit_phone" class="form-control" minlength="10" maxlength="10" pattern="[0-9]{10}" inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                            </div>

                            <div class="col-md-12 mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">

                                    <label for="whatsapp_number" class="col-form-label fs-14 mb-0">
                                        What's App Number
                                    </label>

                                    <!-- <div class="form-check d-flex align-items-center mb-0 gap-1">
                                        <input class="form-check-input mt-0 shadow-none" type="checkbox" id="same_as_phone" style="width: 18px; height: 18px; cursor: pointer;">
                                        <label class="form-check-label fw-semibold text-secondary" for="same_as_phone" style="font-size: 13px; cursor: pointer;">
                                            Same as Phone Number
                                        </label>
                                    </div> -->

                                </div>

                                <input type="text" class="form-control" id="edit_whatsapp" name="whatsapp_number">
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Location <span class="fs-15 text-danger">*</span></label>
                                <input type="text" name="location" id="edit_location" class="form-control">
                            </div>
                        </div>

                </div>

                <div class="modal-footer px-0">
                    <div class="row w-100">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="submit" id="submit_btn" class="btn btn-primary w-100">
                                Save
                            </button>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            var table = $("#datatables-reponsive").DataTable({
                responsive: true,
                scrollX: true,
                ordering: false,
                lengthChange: false,
                info: false,
                autoWidth: true,
                dom: 'frtip'
            });

            $('#datatables-reponsive_filter').appendTo('#custom-search');

        });
        // popup data
        // $(document).on('click', '.editCourse', function() {

        //     $('#discount_id').val($(this).data('id'));
        //     $('select[name="course"]').val($(this).data('course'));
        //     $('input[name="discount"]').val($(this).data('discount'));
        //     $('input[name="description"]').val($(this).data('description'));
        //     $('input[name="start_date"]').val($(this).data('start'));
        //     $('input[name="end_date"]').val($(this).data('end'));

        // });

         $(document).on('click', '.editCourse', function(e) {
            // e.preventDefault();

            $('#edit_user_id').val($(this).data('id'));
            $('#edit_name').val($(this).data('name'));
            $('#edit_nick_name').val($(this).data('nick'));
            $('#edit_phone').val($(this).data('phone'));
            $('#edit_whatsapp').val($(this).data('whatsapp'));
            $('#edit_location').val($(this).data('location'));


            // console.log($(this).data());


            // $('#edit_user_id').val($(this).data('id'));
            // $('#edit_name').val($(this).data('name'));
            // $('#edit_phone').val($(this).data('phone'));
            // $('#edit_password').val($(this).data('password'));

            // var formData = $(this).serialize();

            // $.ajax({
            //     url: "{{ route('user.edit.store') }}",
            //     method: 'POST',
            //     data: formData,
            //     success: function(response) {
            //         location.reload();
            //     },
            //     error: function(xhr) {
            //         alert('An error occurred while updating user.');
            //     }
            // });
        });
    </script>

    <script>
        document.getElementById('same_as_phone').addEventListener('change', function () {

            let phone = document.querySelector('input[name="phone"]').value;
            let whatsapp = document.getElementById('whatsapp_number');

            if (this.checked) {
                whatsapp.value = phone;
                whatsapp.readOnly = true; // optional: prevent editing
            } else {
                whatsapp.value = '';
                whatsapp.readOnly = false;
            }

        });

        // also update if phone number changes while checkbox is checked
        document.querySelector('input[name="phone"]').addEventListener('input', function () {
            if (document.getElementById('same_as_phone').checked) {
                document.getElementById('whatsapp_number').value = this.value;
            }
        });


        function checkPhone(el) {

            let phone = el.value;

            // allow only numbers
            el.value = phone.replace(/[^0-9]/g, '');

            if (phone.length === 10) {

                $.ajax({
                    url: "{{ route('farmer.check.phone') }}",
                    type: "POST",
                    data: {
                        phone: phone,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (res) {

                        if (res.exists) {
                            $('#phone_error').text('Mobile number already exists');
                            $('#phone').focus();

                        } else {
                            $('#phone_error').text('');

                            // move to next input
                            $('#next_input').focus();
                        }

                    }
                });

            }
        }

</script>

    
@endpush
