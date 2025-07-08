@extends('layouts.admin')
@section('title', 'Email / Employee Management')

@section('content')
<div class="container mt-4">

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- USERS --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2>Users (Employees)</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th><th>Email</th><th>Role</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->role }}</td>
                <td class="d-flex gap-1">
                    {{-- Edit Button --}}
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">Edit</button>

                    {{-- Delete --}}
                    <form action="{{ route('admin.user.delete', $user->id) }}" method="POST" onsubmit="return confirm('Delete this user?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>

            {{-- Edit Modal --}}
            <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('admin.user.update', $user->id) }}">
                        @csrf @method('PUT')
                        <div class="modal-content">
                            <div class="modal-header"><h5>Edit User</h5></div>
                            <div class="modal-body">
                                <div class="mb-2">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                </div>
                                <div class="mb-2">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                                </div>
                                <div class="mb-2">
                                    <label>Role</label>
                                    <select name="role" class="form-control">
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="cashier" {{ $user->role === 'cashier' ? 'selected' : '' }}>Cashier</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label>New Password <small>(leave blank to keep current)</small></label>
                                    <input type="password" name="password" class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-success">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endforeach
        </tbody>
    </table>

    {{-- TECHNICIANS --}}
    <div class="d-flex justify-content-between align-items-center mt-5 mb-2">
        <h2>Technicians</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTechModal">Add Technician</button>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th><th>Position</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($technicians as $tech)
            <tr>
                <td>{{ $tech->name }}</td>
                <td>{{ $tech->position }}</td>
                <td class="d-flex gap-1">
                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editTechModal{{ $tech->id }}">Edit</button>

                    <form action="{{ route('admin.technician.delete', $tech->id) }}" method="POST" onsubmit="return confirm('Delete this technician?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>

            {{-- Edit Technician Modal --}}
            <div class="modal fade" id="editTechModal{{ $tech->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('admin.technician.update', $tech->id) }}">
                        @csrf @method('PUT')
                        <div class="modal-content">
                            <div class="modal-header"><h5>Edit Technician</h5></div>
                            <div class="modal-body">
                                <div class="mb-2">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ $tech->name }}" required>
                                </div>
                                <div class="mb-2">
                                    <label>Position</label>
                                    <input type="text" name="position" class="form-control" value="{{ $tech->position }}" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-success">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Add User Modal --}}
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.user.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5>Add User</h5></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="cashier">Cashier</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Add User</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Add Technician Modal --}}
<div class="modal fade" id="addTechModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.technician.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5>Add Technician</h5></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Position</label>
                        <input type="text" name="position" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Add Technician</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
