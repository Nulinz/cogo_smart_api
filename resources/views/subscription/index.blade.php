@extends('layout.app')
@section('page_name', 'Subscription')

@push('style')
   
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

                                <!-- <a class="btn btn-primary btn-sm d-flex align-items-center" type="button" data-bs-toggle="modal" data-bs-target="#discountModal">
                                    <img src="{{ asset('images/icons/plus.png') }}" class="me-2" width="10px">
                                    Add Subscription
                                </a> -->
                            </div>

                            <div class="table-responsive">
                                <table id="datatables-reponsive" class="table-striped w-100 table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Amount</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($subscriptions as $subscription)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $subscription->type }}</td>
                                            <td>{{ $subscription->amount }}</td>
                                            <td>{{ $subscription->duration }} -  Months</td>
                                            <!-- <td>{{ $subscription->status }}</td> -->
                                            <td>
                                                <span class="badge rounded-pill fw-bold fs-12 {{ $subscription->status === 'active' ? 'badge-success' : 'badge-danger' }} me-2">
                                                    {{ ($subscription->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">

                                                    <!-- <a class="changeStatus">
                                                        <img src="{{ asset('images/icons/correct.png') }}" width="20px">
                                                    </a> -->

                                                    <button class="editCourse" data-bs-toggle="modal" data-bs-target="#discountModalEdit" data-id="{{ $subscription->id }}" data-name="{{ $subscription->type }}" data-duration="{{ $subscription->duration }}" data-amount="{{ $subscription->amount }}">
                                                        <img src="{{ asset('images/icons/Edit.png') }}" width="20px">
                                                    </button>

                                                    <a href="{{ route('subscription.profile', ['type' => $subscription->type]) }}" class="changeStatus">
                                                        <img src="{{ asset('images/icons/export.png') }}" width="20px">
                                                    </a>
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
                    <h1 class="modal-title fs-17 fw-medium" id="discountModalLabel">Add Subscription</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form action="" method="POST" onsubmit="document.getElementById('submit_btn').disabled = true;">
                        @csrf

                        <input type="hidden" name="sub_id" id="">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Name </label>
                                <input type="text" name="name" id="" class="form-control">
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Duration</label>
                                <input type="number" name="duration" id="" class="form-control">
                                <!-- <select name="duration" id="" class="form-select">
                                    <option value="" disabled selected>select option</option>
                                </select> -->
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Amount </label>
                                <input type="number" name="amount" id="" class="form-control">
                            </div>

                            <!-- <div class="col-md-12 mb-2">
                                <label class="col-form-label">Description </label>
                                <textarea name="desc" id="" class="form-control" rows="2"></textarea>
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

    <!-- Edit Modal -->
    <div class="modal fade" id="discountModalEdit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="discountModalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-17 fw-medium" id="discountModalEditLabel">Edit Subscription</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form action="{{ route('subscription.store') }}" method="POST" onsubmit="document.getElementById('submit_btn').disabled = true;">
                        @csrf

                        <input type="hidden" name="sub_id" id="edit_sub_id">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Name </label>
                                <input type="text" name="name" id="edit_name" class="form-control">
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Duration (months)</label>
                                <input type="number" name="duration" id="edit_duration" class="form-control">
                                <!-- <select name="duration" id="" class="form-select">
                                    <option value="" disabled selected>select option</option>
                                </select> -->
                            </div>

                            <div class="col-md-12 mb-2">
                                <label class="col-form-label">Amount </label>
                                <input type="number" name="amount" id="edit_amount" class="form-control">
                            </div>

                            <!-- <div class="col-md-12 mb-2">
                                <label class="col-form-label">Description </label>
                                <textarea name="desc" id="" class="form-control" rows="2"></textarea>
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

            // console.log($(this).data());
            $('#edit_sub_id').val($(this).data('id'));
            $('#edit_name').val($(this).data('name'));
            $('#edit_duration').val($(this).data('duration'));
            $('#edit_amount').val($(this).data('amount'));
          
        });
    </script>
@endpush
