@extends('layout.app')
@section('page_name', 'Dashboard')

@push('style')
    <style>

    </style>
@endpush

@section('content')
    <main class="content">
        <div class="container-fluid p-0">

            <div class="row">

                <div class="col-12 col-md-4">
                    <div class="card stat-card stat-card-green">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-title fs-17 fw-semibold mb-0">Total Traders</h6>
                                <div class="icon-wrapper">
                                    <img src="{{ asset('images/icons/up.png') }}" height="20px" />
                                </div>
                            </div>
                            <div class="stat-value">{{ $trader_count }}</div>
                            <div class="d-flex align-items-center">
                                <span class="badge-stat">{{ $trader_monthly_count }}</span>
                                <span class="stat-period">This month</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card stat-card stat-card-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-title fs-17 fw-semibold mb-0">Total Farmer</h6>
                                <div class="icon-wrapper">
                                    <img src="{{ asset('images/icons/down.png') }}" height="20px" />
                                </div>
                            </div>
                            <div class="stat-value text-dark">{{ $farmer_count }}</div>
                            <div class="d-flex align-items-center">
                                <span class="badge-stat">{{ $farmer_monthly_count }}</span>
                                <span class="stat-period text-muted">This month</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card stat-card stat-card-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-title fs-17 fw-semibold mb-0">Total Subscription</h6>
                                <div class="icon-wrapper">
                                    <img src="{{ asset('images/icons/down.png') }}" height="20px" />
                                </div>
                            </div>
                            <div class="stat-value text-dark">₹{{ number_format($total_subscription_amount, 2) }}</div>
                            <div class="d-flex align-items-center">
                                <span class="badge-stat">₹{{ number_format($monthly_subscription_amount, 2) }}</span>
                                <span class="stat-period text-muted">This month</span>
                            </div>
                        </div>
                    </div>
                </div>

                @foreach($subscription_summary  as $type => $count)
                <div class="col-12 col-md-4">
                    <div class="card stat-card stat-card-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-title fs-17 fw-semibold mb-0">{{ ucfirst($type) }}</h6>
                                <div class="icon-wrapper">
                                    <img src="{{ asset('images/icons/down.png') }}" height="20px" />
                                </div>
                            </div>
                            <div class="stat-value text-dark">{{ $count }}</div>
                            <div class="d-flex align-items-center">
                                <span class="badge-stat">{{ $subscription_monthly[$type] ?? 0 }}</span>
                                <span class="stat-period text-muted">This month</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- <div class="col-12 col-md-4">
                    <div class="card stat-card stat-card-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-title fs-17 fw-semibold mb-0">Gold</h6>
                                <div class="icon-wrapper">
                                    <img src="{{ asset('images/icons/down.png') }}" height="20px" />
                                </div>
                            </div>
                            <div class="stat-value text-dark">55</div>
                            <div class="d-flex align-items-center">
                                <span class="badge-stat">5</span>
                                <span class="stat-period text-muted">This month</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card stat-card stat-card-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-title fs-17 fw-semibold mb-0">Platinum</h6>
                                <div class="icon-wrapper">
                                    <img src="{{ asset('images/icons/down.png') }}" height="20px" />
                                </div>
                            </div>
                            <div class="stat-value text-dark">30</div>
                            <div class="d-flex align-items-center">
                                <span class="badge-stat">9</span>
                                <span class="stat-period text-muted">This month</span>
                            </div>
                        </div>
                    </div>
                </div> -->

            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card traders-table-card">
                        <div class="card-body">
                            <h5 class="fw-bold text-dark mb-4">Traders</h5>

                            <div class="table-responsive">
                                <table id="datatables-reponsive" class="w-100 table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Last Name</th>
                                            <th>Mobile Number</th>
                                            <th>Subscription Plan</th>
                                            <th>Subscription End</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trader_today as $index => $trader)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $trader->name }}</td>
                                            <td>{{ $trader->l_name }}</td>
                                            <td>{{ $trader->phone }}</td>
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
            // Initialize DataTables
            $("#datatables-reponsive").DataTable({
                responsive: true,
                scrollX: true,
                ordering: false,
                lengthChange: false,
                info: false,
                autoWidth: true,
                dom: 'frtip'
            });
        });
    </script>
@endpush
