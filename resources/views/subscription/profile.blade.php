@extends('layout.app')
@section('page_name', 'Subscription Profile')

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

        /* Card Container */
        .plan-card {
            border: 1px solid #eaedf1;
            border-radius: 8px;
            background-color: #ffffff;
        }

        /* Left Column Typography */
        .plan-title {
            color: #2b3445;
            font-size: 16px;
            font-weight: 600;
        }

        .plan-meta {
            color: #6b7280;
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .plan-desc {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.6;
        }

        /* Right Column Typography */
        .features-title {
            color: #2b3445;
            font-size: 15px;
            font-weight: 600;
        }

        /* Custom Blue Halo Bullet Points */
        .custom-feature-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .custom-feature-list li {
            position: relative;
            padding-left: 28px;
            margin-bottom: 12px;
            color: #4b5563;
            font-size: 13px;
        }

        .custom-feature-list li::before {
            content: '';
            position: absolute;
            left: 4px;
            top: 6px;
            /* Adjusts vertical alignment with text */
            width: 6px;
            height: 6px;
            background-color: #5a67d8;
            /* Inner solid blue dot */
            border-radius: 50%;
            box-shadow: 0 0 0 4px #e0e7ff;
            /* Outer light blue halo */
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

                            <div class="row mb-4">

                                <div class="col-12 col-md-5 mb-md-0 mb-4">
                                    <div class="plan-title mb-3">Silver</div>
                                    <div class="plan-meta">₹5000</div>
                                    <div class="plan-meta mb-3">6 Month</div>
                                    <p class="plan-desc mb-0">
                                        Infectious diseases and acute febrile illnesses, Dengue, Malaria, Typhoid, Diabetes, Hypertension, Thyroid issues, Respiratory
                                        diseases, Tuberculosis, Asthma
                                    </p>
                                </div>

                                <div class="col-12 col-md-7">
                                    <div class="features-title mb-3">Features</div>
                                    <ul class="custom-feature-list">
                                        <li>Quick and easy appointment scheduling</li>
                                        <li>Flexible time slots to fit your schedule</li>
                                        <li>Confirmation within 24 hours</li>
                                        <li>Option for in-person or virtual consultations</li>
                                    </ul>
                                </div>

                            </div>

                            <div class="d-flex align-items-center justify-content-between filter-row mb-3 flex-wrap gap-3">
                                <!-- Search will appear here -->
                                <div id="custom-search"></div>
                            </div>

                            <table id="datatables-reponsive" class="table-striped w-100 table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Nick Name</th>
                                        <th>Mobile Number</th>
                                        <th>Subscription Plan</th>
                                        <th>Subscription End</th>
                                        <th>Subscription Amount</th>
                                        <th>Subscription Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                   @php $i = 1; @endphp
                                   @foreach ($traders as $trader)

                                        @foreach ($trader['subscriptions'] as $sub)
                                            <tr>
                                                <td>{{ $i++; }}</td>
                                                <td>{{ $trader['name'] }}</td>
                                                <td>{{ $trader['l_name'] }}</td>
                                                <td>{{ $trader['phone'] }}</td>

                                                <td>{{ $sub['plan'] }} - {{ $sub['duration'] }} months</td>
                                                <td>{{ $sub['expiry_date'] }}</td>
                                                <td>{{ $sub['amount'] }}</td>
                                                <td>{{ $sub['created_at'] }}</td>
                                            </tr>
                                        @endforeach

                                    @endforeach

                                </tbody>
                            </table>
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
                responsive: false,
                scrollX: true,
                ordering: false,
                lengthChange: false,
                info: false,
                autoWidth: false,
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
