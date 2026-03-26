 <nav id="sidebar" class="sidebar js-sidebar">
     <div class="sidebar-content js-simplebar">
         <a class='sidebar-brand pb-0'>
             <img src="{{ asset('images/icons/cogosmart-logo.png') }}" class="barnd w-100" height="45x" alt="">
             <svg class="sidebar-brand-icon align-middle" width="32px" height="32px" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="square"
                 stroke-linejoin="miter" color="#FFFFFF" style="margin-left: -3px">
                 <path d="M12 4L20 8.00004L12 12L4 8.00004L12 4Z"></path>
                 <path d="M20 12L12 16L4 12"></path>
                 <path d="M20 16L12 20L4 16"></path>
             </svg>
         </a>

         <ul class="sidebar-nav mt-3">

            @if (Auth::user()->role == 'admin')
             <li class="sidebar-item {{ Route::is('dashboard') ? 'active' : '' }}">
                 <a class='sidebar-link' href='{{ route("dashboard") }}'>
                     <img src="{{ asset('images/icons/dashboard.png') }}" width="20px"> <span class="ms-2 align-middle">Dashboard</span>
                 </a>
             </li>
            @endif
            @if (Auth::user()->role == 'admin')
             <li class="sidebar-item {{ Route::is('trader*') ? 'active' : '' }}">
                 <a class='sidebar-link' href='{{ route("trader.list") }}'>
                     <img src="{{ asset('images/icons/people.png') }}" width="20px"> <span class="ms-2 align-middle">Trader</span>
                 </a>
             </li>
            @endif
            @if ((Auth::user()->role == 'admin') || Auth::user()->role == ('emp'))

             <li class="sidebar-item {{ Route::is('farmer*') ? 'active' : '' }}">
                 <a class='sidebar-link' href='{{ route("farmer.list") }}'>
                     <img src="{{ asset('images/icons/farmer.png') }}" width="20px"> <span class="ms-2 align-middle">Farmer</span>
                 </a>
             </li>
            @endif
            @if (Auth::user()->role == 'admin')
             <li class="sidebar-item {{ Route::is('subscription*') ? 'active' : '' }}">
                 <a class='sidebar-link' href='{{ route("subscription.list") }}'>
                     <img src="{{ asset('images/icons/subscription.png') }}" width="20px"> <span class="ms-2 align-middle">Subscription</span>
                 </a>
             </li>
             @endif
             @if (Auth::user()->role == 'admin')

             <li class="sidebar-item {{ Route::is('user*') ? 'active' : '' }}">
                 <a class='sidebar-link' href='{{ route("user.list") }}'>
                     <img src="{{ asset('images/icons/user.png') }}" width="20px"> <span class="ms-2 align-middle">Users</span>
                 </a>
             </li>
             @endif

         </ul>
     </div>
 </nav>

   @if (session('login_error') || session('message')) 
    <div id="loginToastWrapper" class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div class="toast show align-items-center text-white border-0 shadow {{ session('login_error') ? 'bg-danger' : 'bg-success' }}">
            <div class="d-flex">
                <div class="toast-body fw-medium">
                    {{ session('login_error') ?? session('message') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
     @endif
    <script>
        setTimeout(function() {
            let toast = document.getElementById('loginToastWrapper');
            if (toast) {
                toast.style.display = 'none';
            }
        }, 3000); // 3 seconds
    </script>
