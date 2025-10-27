{{-- resources/views/users/index.blade.php --}}

@extends('layouts.app') 

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Manajemen User</h5>
                    <button class="btn btn-primary" id="btn-add-user" data-bs-toggle="modal" data-bs-target="#userModal">
                        <i class="bi bi-plus-circle"></i> Tambah User
                    </button>
                </div>
                <div class="card-body">
                    
                    {{-- Tampilkan pesan sukses --}}
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Tampilkan error validasi (jika ada) --}}
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <h4 class="alert-heading">Terjadi Kesalahan!</h4>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge {{ $user->role == 'admin' ? 'bg-success' : 'bg-secondary' }}">{{ $user->role }}</span></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm btn-edit"
                                                data-id="{{ $user->id }}"
                                                data-name="{{ $user->name }}"
                                                data-username="{{ $user->username }}"
                                                data-email="{{ $user->email }}"
                                                data-role="{{ $user->role }}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#userModal"
                                                title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm btn-delete" 
                                                data-id="{{ $user->id }}"
                                                data-name="{{ $user->name }}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal"
                                                title="Hapus">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada data user.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Pagination --}}
                    {{ $users->links() }}

                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- Form akan diisi oleh JS --}}
            <form id="userForm" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="modal-body">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <hr>
                    <p id="passwordHelp" class="form-text text-muted">Kosongkan password jika tidak ingin mengubahnya (saat edit).</p>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span id="passwordRequired" class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password <span id="passwordConfirmationRequired" class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-save">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus user <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts') {{-- GANTI 'scripts' JIKA NAMA STACK ANDA BERBEDA --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const userModal = document.getElementById('userModal');
        const userForm = document.getElementById('userForm');
        const userModalLabel = document.getElementById('userModalLabel');
        const formMethod = document.getElementById('formMethod');
        
        const nameInput = document.getElementById('name');
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        const roleInput = document.getElementById('role');
        const passwordInput = document.getElementById('password');
        const passwordConfirmationInput = document.getElementById('password_confirmation');
        
        const passwordHelp = document.getElementById('passwordHelp');
        const passwordRequired = document.getElementById('passwordRequired');
        const passwordConfirmationRequired = document.getElementById('passwordConfirmationRequired');
        
        const deleteModal = document.getElementById('deleteModal');
        const deleteForm = document.getElementById('deleteForm');
        const deleteUserName = document.getElementById('deleteUserName');

        // 1. Logika Tombol "Tambah User"
        document.getElementById('btn-add-user').addEventListener('click', function() {
            userModalLabel.textContent = 'Tambah User Baru';
            userForm.reset();
            userForm.action = '{{ route("users.store") }}';
            formMethod.value = 'POST';
            
            // Atur validasi password untuk mode 'create'
            passwordHelp.style.display = 'none';
            passwordRequired.style.display = 'inline';
            passwordConfirmationRequired.style.display = 'inline';
            passwordInput.required = true;
            passwordConfirmationInput.required = true;
        });

        // 2. Logika Tombol "Edit"
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const username = this.dataset.username;
                const email = this.dataset.email;
                const role = this.dataset.role;

                userModalLabel.textContent = 'Edit User: ' + name;
                userForm.action = `/users/${id}`; // URL dinamis
                formMethod.value = 'PUT';
                
                // Isi form
                nameInput.value = name;
                usernameInput.value = username;
                emailInput.value = email;
                roleInput.value = role;
                
                // Atur validasi password untuk mode 'edit'
                passwordHelp.style.display = 'block';
                passwordRequired.style.display = 'none';
                passwordConfirmationRequired.style.display = 'none';
                passwordInput.required = false;
                passwordConfirmationInput.required = false;
            });
        });

        // 3. Logika Tombol "Delete"
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;

                deleteUserName.textContent = name;
                deleteForm.action = `/users/${id}`; // URL dinamis
            });
        });

        // 4. Jika ada error validasi saat submit, modal harus terbuka kembali
        @if ($errors->any())
            const modalToOpen = new bootstrap.Modal(document.getElementById('userModal'));
            modalToOpen.show();
        @endif

    });
</script>
@endpush