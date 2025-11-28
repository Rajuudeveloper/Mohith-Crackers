@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">User Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="#" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-plus me-1"></i>Add New User
        </a>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Search by name or email..." 
                           value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                </select>
            </div>
            <div class="col-md-3">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-refresh me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Users List</h5>
    </div>
    <div class="card-body">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Email Verified</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF" 
                                                 alt="{{ $user->name }}" 
                                                 class="rounded-circle"
                                                 width="40"
                                                 height="40">
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            {{ $user->name }}
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Verified
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-clock me-1"></i>Pending
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('M j, Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="#" 
                                           class="btn btn-sm btn-outline-primary"
                                           data-bs-toggle="tooltip" 
                                           title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" 
                                           class="btn btn-sm btn-outline-info"
                                           data-bs-toggle="tooltip" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteUserModal{{ $user->id }}"
                                                title="Delete User">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>

                                    <!-- Delete Confirmation Modal -->
                                    <div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete user <strong>{{ $user->name }}</strong>? This action cannot be undone.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="#" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete User</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
                </div>
                
                <nav aria-label="Users pagination">
                    <ul class="pagination mb-0">
                        {{-- Previous Page Link --}}
                        @if ($users->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->previousPageUrl() }}" rel="prev">Previous</a>
                            </li>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                            @if ($page == $users->currentPage())
                                <li class="page-item active">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($users->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->nextPageUrl() }}" rel="next">Next</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">Next</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>

        @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h4>No users found</h4>
                <p class="text-muted">
                    @if(request()->has('search') && request('search'))
                        No users match your search criteria.
                    @else
                        No users in the system yet.
                    @endif
                </p>
                @if(request()->has('search') && request('search'))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                        <i class="fas fa-refresh me-1"></i>Clear Search
                    </a>
                @else
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add First User
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush