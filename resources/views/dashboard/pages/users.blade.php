<div class="users-header">
  <h2>👤 Manajemen Users</h2>
  <button class="btn-add-user" onclick="openUserForm()">➕ Tambah User</button>
</div>

<table class="users-table">
  <colgroup>
    <col style="width:60px">
    <col>
    <col style="width:140px">
    <col style="width:160px">
  </colgroup>

  <thead>
    <tr>
      <th>No</th>
      <th>Username</th>
      <th>Role</th>
      <th>Aksi</th>
    </tr>
  </thead>

  <tbody>
  @foreach($users as $u)
  <tr>
    <td class="col-no">{{ $loop->iteration }}</td>
    <td>{{ $u->username }}</td>
    <td>
      <span class="badge-role {{ $u->role === 'ADMIN' ? 'badge-admin' : 'badge-user' }}">
        {{ $u->role }}
      </span>
    </td>
    <td>
      <div class="action-group">
        <button class="btn-action btn-edit"
                onclick="editUser({{ $u->id }})">✏️</button>

        @if(auth()->id() !== $u->id)
        <button class="btn-action btn-delete"
                onclick="deleteUser({{ $u->id }})">🗑️</button>
        @endif
      </div>
    </td>
  </tr>
  @endforeach
</tbody>

</table>