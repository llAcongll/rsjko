<div class="dashboard">

  {{-- HEADER --}}
  <div class="dashboard-header">
    <div class="dashboard-header-left">
      <h2 style="display: flex; align-items: center; gap: 10px;">
        <img src="https://lh3.googleusercontent.com/d/1L_r51MzZ9qlSFW1WKVvJM40DKtrA-6hx=w200"
          style="height: 36px; width: auto; object-fit: contain;" alt="Logo Prov Kepri">
        Manajemen Users
      </h2>
      <p>Kelola akses pengguna dan administrator sistem</p>
    </div>

    <div class="dashboard-header-right">
      <button class="btn-tambah-data" onclick="openUserForm()">
        <i class="ph-bold ph-plus"></i>
        <span>Tambah User</span>
      </button>
    </div>
  </div>

  {{-- TABLE BOX --}}
  <div class="dashboard-box">
    <div class="table-container">
      <table class="users-table">
        <colgroup>
          <col style="width:60px">
          <col>
          <col style="width:140px">
          <col style="width:160px">
        </colgroup>

        <thead>
          <tr>
            <th class="text-center">No</th>
            <th class="text-center">Username</th>
            <th class="text-center">Role</th>
            <th class="text-center">Aksi</th>
          </tr>
        </thead>

        <tbody>
          @foreach($users as $u)
            <tr>
              <td class="text-center">{{ $loop->iteration }}</td>
              <td>{{ $u->username }}</td>
              <td>
                <span class="badge-role {{ $u->role === 'ADMIN' ? 'badge-admin' : 'badge-user' }}">
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