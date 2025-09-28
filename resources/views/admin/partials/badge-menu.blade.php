<!-- Badge Management -->
<div class="sidebar-divider"></div>
<div class="sidebar-item">
    <i class="bi bi-award"></i>
    <span>Badge Management</span>
</div>
<a href="{{ route('admin.badges.index') }}" class="sidebar-item {{ request()->routeIs('admin.badges.*') ? 'active' : '' }}">
    <i class="bi bi-trophy"></i>
    <span>Kelola Badge</span>
</a>
