{{-- Sidebar --}}
    <div class="dashboard-layout">
        <div class="dashboard-sidebar">
            <div class="sidebar-heading">
                <i class ="bi bi-list"></i>
                    <span>Navigation</span>
            </div>

            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}" >
                        <i class="bi bi-house-door"></i>
                            <span>Dashboard</span>
                    </a>
                </li>
            
                <li class="sidebar-item">
                    <a href="{{ url('/employeedetails') }}" class="{{ request()->is('employeedetails') ? 'active' : '' }}">
                        <i class="fa fa-id-card-o"></i>
                            <span>Employees Details</span>
                            <i class="bi bi-chevron-right ms-auto"></i>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="{{ url('/employeelist') }}" class="{{ request()->is('employeelist') ? 'active' : '' }}">
                        <i class="fa fa-users"></i>
                            <span>Employees List</span>
                            <i class="bi bi-chevron-right ms-auto"></i>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a href="{{ url('/generateroster') }}" class="{{ request()->is('generateroster') ? 'active' : '' }}">
                        <i class="fa fa-calendar-plus-o"></i>
                            <span>Generate Roster</span>
                            <i class="bi bi-chevron-right ms-auto"></i>
                    </a>
                </li>

                <li class="sidebar-item"> 
                    <a href="{{ url('leaverequest') }}" class="{{ request()->is('leaverequest') ? 'active' : '' }}">
                        <i class="fa fa-calendar-check-o"></i>
                            <span>Leave Requests</span>
                            <i class="bi bi-chevron-right ms-auto"></i>
                    </a>
                </li>
                
                <li class="sidebar-item">
                    <a href="{{ url('preferencesrequest') }}" class="{{ request()->is('preferencesrequest') ? 'active' : '' }}">
                        <i class="fa fa-sliders"></i>
                        <span>Preferences Requests</span>
                        <i class="bi bi-chevron-right ms-auto"></i>
                    </a>
                </li>


                <li class="sidebar-item">
                    <a href="{{ url('setting') }}" class="{{ request()->is('setting') ? 'active' : '' }}">
                        <i class="fa fa-gear"></i>
                            <span>Settings</span>
                            <i class="bi bi-chevron-right ms-auto"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>