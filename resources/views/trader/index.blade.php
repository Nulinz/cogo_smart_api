@extends('layout.app')
@section('page_name', 'Trader')

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
                            </div>

                            <div class="table-responsive">
                                <table id="datatables-reponsive" class="table-striped w-100 table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Nick Name</th>
                                            <th>Mobile Number</th>
                                            <th>Register Date</th>
                                            <th>Subscription Plan</th>
                                            <th>Subscription End</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($traders as $index => $trader)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $trader->name }}</td>
                                                <td>{{ $trader->l_name }}</td>
                                                <td>{{ $trader->phone }}</td>
                                                <td>{{ $trader->register_date }}</td>
                                                <td>{{ $trader->subscription_plan ?? 'N/A' }}</td>
                                                <td>{{ $trader->subscription_end ?? 'N/A' }}</td>
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
    </script>
@endpush
