@extends('layouts.admin')

@section('content')

<h1 class="text-3xl font-bold mb-1">User Management</h1>
<p class="text-gray-500 mb-6">Manage admin, service staff, and kitchen staff accounts.</p>

<div class="bg-white p-5 rounded shadow mb-6">
    <form id="form" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <input class="border p-2 rounded" placeholder="Full Name" id="name" required>
        <input class="border p-2 rounded" placeholder="Email" id="email" type="email" required>
        <input class="border p-2 rounded" placeholder="Password" id="password" type="password">
        <select class="border p-2 rounded" id="role" required>
            <option value="admin">Admin</option>
            <option value="service_staff">Service Staff</option>
            <option value="kitchen_staff">Kitchen Staff</option>
        </select>
        <button id="submitBtn" class="bg-orange-500 text-white rounded px-4 py-2">Add User</button>
    </form>
</div>

<div class="bg-white p-5 rounded shadow">
    <h2 class="text-lg font-bold mb-3">User Accounts</h2>
    <div id="list"></div>
</div>

<script>
let editingId = null;

async function loadUsers() {
    const res = await fetch('/api/admin/users');
    const data = await res.json();

    let html = '';

    data.forEach(user => {
        const roleLabel = user.role.replace('_', ' ').toUpperCase();

        html += `
            <div class="flex justify-between items-center border-b py-3">
                <div>
                    <p class="font-semibold">${user.name}</p>
                    <p class="text-sm text-gray-500">${user.email}</p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-bold">
                        ${roleLabel}
                    </span>

                    <button onclick="editUser(${user.id}, '${user.name}', '${user.email}', '${user.role}')"
                        class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                        Edit
                    </button>

                    <button onclick="deleteUser(${user.id})"
                        class="bg-red-500 text-white px-3 py-1 rounded text-sm">
                        Delete
                    </button>
                </div>
            </div>
        `;
    });

    document.getElementById('list').innerHTML = html || '<p class="text-gray-500">No users yet.</p>';
}

document.getElementById('form').onsubmit = async (e) => {
    e.preventDefault();

    const payload = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        role: document.getElementById('role').value,
    };

    const password = document.getElementById('password').value;

    if (!editingId || password) {
        payload.password = password;
    }

    const url = editingId
        ? `/api/admin/users/${editingId}`
        : '/api/admin/users';

    const method = editingId ? 'PUT' : 'POST';

    await fetch(url, {
        method: method,
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    });

    editingId = null;
    document.getElementById('submitBtn').textContent = 'Add User';
    document.getElementById('password').required = false;
    document.getElementById('form').reset();

    loadUsers();
};

function editUser(id, name, email, role) {
    editingId = id;

    document.getElementById('name').value = name;
    document.getElementById('email').value = email;
    document.getElementById('role').value = role;
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;

    document.getElementById('submitBtn').textContent = 'Update User';
}

async function deleteUser(id) {
    if (!confirm('Delete this user?')) return;

    await fetch(`/api/admin/users/${id}`, {
        method: 'DELETE'
    });

    loadUsers();
}

loadUsers();
</script>

@endsection