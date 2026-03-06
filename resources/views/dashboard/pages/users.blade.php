<div class="page-container">
  {{-- HEADER --}}
  <div class="page-header">
    <div class="page-header-left">
      <h2><i class="ph ph-users-three"></i> Manajemen Users</h2>
      <p>Kelola akses pengguna dan administrator sistem</p>
    </div>

    <div class="page-header-right">
      <button class="btn-tambah-data" onclick="openUserForm()">
        <i class="ph-bold ph-plus"></i>
        <span>Tambah User</span>
      </button>
    </div>
  </div>

  {{-- TABLE BOX --}}
  <div class="dashboard-box">
    <div class="table-toolbar">
      <div class="table-search-wrapper">
        <i class="ph ph-magnifying-glass"></i>
        <input type="text" id="searchUsers" class="table-search" placeholder="Cari user..." data-table="tableUsers">
      </div>
    </div>

    <div class="table-container">
      <table id="tableUsers" class="table universal-table">
        <thead>
          <tr>
            <th class="text-center checkbox-col">No</th>
            <th class="text-center sortable">Username</th>
            <th class="text-center sortable">Role</th>
            <th class="action-col">Aksi</th>
          </tr>
        </thead>

        <tbody>
          @foreach($users as $u)
            <tr>
              <td class="text-center">{{ $loop->iteration }}</td>
              <td>{{ $u->username }}</td>
              <td class="text-center">
                <span class="badge {{ $u->role === 'ADMIN' ? 'badge-primary' : 'badge-info' }}">
                  {{ $u->role }}
                </span>
              </td>
              <td>
                <div class="flex justify-center gap-2">
                  <button class="btn-aksi edit" onclick="editUser({{ $u->id }})" title="Edit User">
                    <i class="ph ph-pencil"></i>
                  </button>

                  @if(auth()->id() !== $u->id)
                    <button class="btn-aksi delete" onclick="deleteUser({{ $u->id }})" title="Hapus User">
                      <i class="ph ph-trash"></i>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>