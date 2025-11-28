<nav class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
           href="{{ route('admin.dashboard') }}">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" 
           href="{{ route('admin.users.index') }}">
            <i class="fas fa-users me-2"></i>Users
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('agents.*') ? 'active' : '' }}" 
           href="{{ route('agents.index') }}">
            <i class="fas fa-users me-2"></i>Agents
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" 
           href="{{ route('admin.settings') }}">
            <i class="fas fa-cog me-2"></i>Settings
        </a>
    </li>
</nav>