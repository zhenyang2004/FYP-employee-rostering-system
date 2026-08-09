{{-- Top Navbar --}}
@php
    $currentUser = auth()->user();
@endphp

<nav class="dashboard-topbar">
    <div class="topbar-left">
        <a href="{{ url('/dashboard') }}" class="topbar-brand">
            <i class="bi bi-calendar-week"></i>
            <span>Employee Rostering System</span>
        </a>
    </div>

    <div class="topbar-right">
        <div class="dropdown topbar-user-dropdown">
            <button class="topbar-user-btn" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                @if ($currentUser && $currentUser->profile_pic)
                    <img src="{{ asset('storage/' . $currentUser->profile_pic) }}" alt="Profile Picture" class="topbar-profile-img">
                @else
                    <div class="user-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                @endif

                <span class="user-name">
                    {{ $currentUser->first_name ?? 'User' }} {{ $currentUser->last_name ?? '' }}
                </span>

                <i class="bi bi-chevron-down small"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end profile-dropdown">
                <li>
                    <a class="dropdown-item" href="{{ url('/userprofile') }}">
                        <i class="bi bi-person-circle"></i>
                        My Profile
                    </a>
                </li> 
            </ul>
        </div>

        <div class="topbar-logout">
            <form action="{{ route('user.logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-button">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>
</nav>