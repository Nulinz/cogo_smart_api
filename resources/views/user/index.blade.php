@extends('layout.app')
@section('page_name', 'Users')

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
                                    Add User
                                </a>
                            </div>

                            <div class="table-responsive">
                                <table id="datatables-reponsive" class="table-striped w-100 table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Mobile Number</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->phone }}</td>
                                                <td>
                                                    <span class="badge rounded-pill fw-bold fs-12 {{ $user->status === 'active' ? 'badge-success' : 'badge-danger' }} me-2">
                                                        {{ $user->status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">

                                                        <a class="changeStatus" data-id="{{ $user->id }}" data-status="{{ $user->status }}">
                                                            <img src="{{ asset('images/icons/correct.png') }}" width="20px">
                                                        </a>

                                                        <button class="editCourse" data-id="{{ $user->id }}" data-name="{{ $user->name }}" data-phone="{{ $user->phone }}" data-password="{{ $user->password }}" id="editForm"
                                                            data-bs-toggle="modal" data-bs-target="#discountModalEdit">
                                                            <img src="{{ asset('images/icons/Edit.png') }}" width="20px">
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach

                                       
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
                    <h1 class="modal-title fs-17 fw-medium" id="discountModalLabel">Add User</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form action="{{ route('user.add') }}" method="POST" onsubmit="document.getElementById('submit_btn').disabled=true;">
                        @csrf

                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Name </label>
                                <input type="text" name="name" class="form-control">
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Mobile Number <span class="fs-15 text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" minlength="10" maxlength="10" pattern="[0-9]{10}" inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Password </label>
                                <input type="password" name="password" class="form-control">
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Confirm Password </label>
                                <input type="password" name="conf_passowrd" class="form-control">
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
                    <h1 class="modal-title fs-17 fw-medium" id="discountModalEditLabel">Edit User</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form  action="{{ route('user.edit.store') }}" method="POST" onsubmit="document.getElementById('submit_btn').disabled=true;">
                        @csrf

                        <input type="hidden" name="user_id" id="edit_user_id">

                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Name </label>
                                <input type="text" name="name" id="edit_name" class="form-control">
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Mobile Number <span class="fs-15 text-danger">*</span></label>
                                <input type="text" name="phone" id="edit_phone" class="form-control" minlength="10" maxlength="10" pattern="[0-9]{10}" inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Password </label>
                                <input type="password" name="password" id="edit_password" class="form-control">
                            </div>

                            <!-- <div class="col-md-12 mb-2">
                                <label class="col-form-label">Confirm Password </label>
                                <input type="password" name="conf_passowrd" id="edit_conf_password" class="form-control">
                            </div> -->

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
        $(document).on('click', '.editCourse', function() {

            $('#discount_id').val($(this).data('id'));
            $('select[name="course"]').val($(this).data('course'));
            $('input[name="discount"]').val($(this).data('discount'));
            $('input[name="description"]').val($(this).data('description'));
            $('input[name="start_date"]').val($(this).data('start'));
            $('input[name="end_date"]').val($(this).data('end'));

        });

        $(document).on('click', '.changeStatus', function() {
            var userId = $(this).data('id');
            var currentStatus = $(this).data('status');

            var confirm = window.confirm('Are you sure you want to change the status of this user?');

            if(!confirm) {
                return;
            }

            $.ajax({
                url: "{{ route('user.status.update') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    user_id: userId,
                    status: currentStatus
                },
                success: function(response) {
                    location.reload();

                },
                error: function(xhr) {
                    alert('An error occurred while updating status.');
                }
            });
        });

        $(document).on('click', '#editForm', function(e) {
            // e.preventDefault();

            $('#edit_user_id').val($(this).data('id'));
            $('#edit_name').val($(this).data('name'));
            $('#edit_phone').val($(this).data('phone'));
            $('#edit_password').val($(this).data('password'));

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


@endpush
