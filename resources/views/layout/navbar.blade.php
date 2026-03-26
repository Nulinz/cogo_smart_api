<style>
    #logoPreview,
    #signaturePreview {
        object-fit: contain;
        border: 1px solid #dee2e6;
    }
</style>
<nav class="navbar navbar-expand navbar-light navbar-bg">
    <a class="sidebar-toggle js-sidebar-toggle">
        <i class="hamburger align-self-center"></i>
    </a>

    <h3 class="d-none d-lg-block fw-medium ms-lg-2 mb-0 ms-0">@yield('page_name')</h3>

    <div class="navbar-collapse collapse">

        <ul class="navbar-nav navbar-align">

            <ul class="navbar-nav d-flex align-items-center ms-auto flex-row gap-2">

                <li class="nav-item dropdown">
                    <a class="nav-icon dropdown-toggle text-decoration-none" data-bs-toggle="dropdown">
                        <img src="{{ asset('images/icons/avatar.png') }}" class="avatar img-fluid rounded" />
                        <span class="fs-5 ms-2">{{ Auth::user()->name }}</span>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end">

                        <div class="sidebar-user mb-2 px-2 px-3 py-1" style="border-bottom: 1.5px solid #ccc !important">
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="position-relative flex-shrink-0">
                                    <img src="{{ asset('images/icons/avatar.png') }}" class="img-fluid me-1 rounded" width="35px" height="35px" id="profileImage">
                                </div>
                                <div class="flex-grow-1 ps-2">
                                    <a>Name</a>
                                    <div class="sidebar-user-subtitle">Type</div>
                                </div>
                            </div>
                        </div>

                        <a data-bs-toggle="modal" class="dropdown-item px-3" data-bs-target="#centeredModal"><img src="{{ asset('images/icons/password-check.png') }}"
                                class="me-1" width="20px"> Change
                            Password</a>

                        <a class="dropdown-item px-3" data-bs-toggle="modal" data-bs-target="#exampleModalToggleLog"><img src="{{ asset('images/icons/logout.png') }}"
                                class="me-1" width="20px"> Log
                            out</a>
                    </div>
                </li>
            </ul>
    </div>
</nav>

<!-- change password -->
<div class="modal fade" id="centeredModal" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
    <div class="modal-dialog modal-xs modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="fs-4 fw-bold modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" onsubmit="return checkPasswordMatch(this)">
                    @csrf

                    <!-- Old Password -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Old Password</label>
                        <input type="password" name="old_password" id="old_password" class="form-control" minlength="6" required placeholder="Enter Old Password">
                    </div>

                    <!-- New Password -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Password</label>
                        <div class="position-relative">
                            <input type="password" class="form-control py-2" name="password" id="new_password" required oninput="checkPasswordMatch()">
                            <span class="position-absolute top-50 translate-middle-y end-0 cursor-pointer pe-3" onclick="togglePassword('new_password', this)">
                                <i class="fas fa-eye-slash"></i>
                            </span>
                        </div>
                        <div id="passwordRulesError" class="text-danger mt-1" style="display:none;"></div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Confirm Password</label>
                        <div class="position-relative">
                            <input type="password" class="form-control py-2" name="password_confirmation" id="confirm_password" required oninput="checkPasswordMatch()">
                            <span class="position-absolute top-50 translate-middle-y end-0 cursor-pointer pe-3" onclick="togglePassword('confirm_password', this)">
                                <i class="fas fa-eye-slash"></i>
                            </span>
                        </div>
                        <div id="passwordError" class="text-danger mt-1" style="display:none;"></div>
                    </div>

                    <div class="d-flex gap-2 border-0 p-0">
                        <button type="button" class="btn btn-outline-secondary w-50" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary btn-bg w-50" id="addClientSave" disabled>Change</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- log out --}}
<div class="modal fade" id="exampleModalToggleLog" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
    <div class="modal-dialog modal-xss modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <!-- <form action="{{ route('admin.logout') }}" method="post">
                    @csrf -->

                    <div class="mb-4 text-center">
                        <h4 class="fw-bold">Ready to head out?</h4>
                        <p class="fw-medium">You’re about to log out. See you next time!</p>
                    </div>

                    <div class="d-flex gap-2">
                         <a href="{{ route('admin.logout') }}" class="btn btn-outline-dark w-50">
                            Logout
                        </a>
                        <button type="button" class="btn btn-primary btn-bg w-50" data-bs-dismiss="modal">
                            Stay Logged In
                        </button>
                    </div>
                <!-- </form> -->
            </div>
        </div>
    </div>
</div>
