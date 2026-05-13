@extends('layouts.admin')

@section('content')

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-3xl font-bold mb-1">User Management</h1>
            <p class="text-gray-500">Manage admin, service staff, kitchen staff, and customer accounts.</p>
        </div>

        <button onclick="openUserModal()"
            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded font-medium">
            + Add User
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border shadow-sm p-5">
            <p class="text-sm text-gray-500 mb-2">Total Users</p>
            <h2 id="cardTotalUsers" class="text-3xl font-bold">0</h2>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5">
            <p class="text-sm text-gray-500 mb-2">Admins</p>
            <h2 id="cardAdmins" class="text-3xl font-bold text-orange-500">0</h2>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5">
            <p class="text-sm text-gray-500 mb-2">Service Staff</p>
            <h2 id="cardServiceStaff" class="text-3xl font-bold text-blue-500">0</h2>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5">
            <p class="text-sm text-gray-500 mb-2">Kitchen Staff</p>
            <h2 id="cardKitchenStaff" class="text-3xl font-bold text-purple-500">0</h2>
        </div>
    </div>

    <!-- Staff Directory -->
    <div class="bg-white rounded-xl border shadow-sm">
        <div class="p-5 border-b">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="text-lg font-bold">User Directory</h2>
                    <p class="text-sm text-gray-500">View, search, add, edit, and delete system accounts.</p>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <input
                        id="userSearch"
                        type="text"
                        placeholder="Search users..."
                        class="border rounded px-3 py-2 w-64"
                    >

                    <select id="roleFilter" class="border rounded px-3 py-2">
                        <option value="all">All Roles</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold">Name</th>
                        <th class="text-left px-6 py-4 font-semibold">Role</th>
                        <th class="text-left px-6 py-4 font-semibold">Email</th>
                        <th class="text-left px-6 py-4 font-semibold">Created</th>
                        <th class="text-left px-6 py-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                            Loading users...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div id="userModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between p-5 border-b">
            <h3 id="userModalTitle" class="text-lg font-bold">Add User</h3>
            <button onclick="closeUserModal()" class="text-gray-500 hover:text-black text-xl">&times;</button>
        </div>

        <form id="userForm" class="p-5 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Full Name</label>
                <input id="userName" type="text" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input id="userEmail" type="email" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select id="userRole" class="w-full border rounded px-3 py-2" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="staff">Service Staff</option>
                    <option value="kitchen">Kitchen Staff</option>
                    <option value="customer">Customer</option>
                </select>
                <p class="text-xs text-gray-400 mt-1">
                    Public registration is for customers only. Employee accounts should be created here.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Password
                    <span id="passwordNote" class="text-gray-400">(required for new user)</span>
                </label>
                <input id="userPassword" type="password" class="w-full border rounded px-3 py-2">
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeUserModal()" class="px-4 py-2 rounded bg-gray-200 text-gray-700">
                    Cancel
                </button>
                <button id="userSaveBtn" type="submit" class="px-4 py-2 rounded bg-orange-500 text-white">
                    Save User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let users = [];
let filteredUsers = [];
let editingUserId = null;

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function safeText(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function normalizeRole(role) {
    const value = String(role ?? 'customer').toLowerCase();

    if (value === 'service_staff' || value === 'service staff') return 'staff';
    if (value === 'kitchen_staff' || value === 'kitchen staff') return 'kitchen';

    return value;
}

function formatRole(role) {
    const value = normalizeRole(role);

    const labels = {
        admin: 'Admin',
        staff: 'Service Staff',
        kitchen: 'Kitchen Staff',
        customer: 'Customer'
    };

    return labels[value] || safeText(role || 'Customer');
}

function initials(name) {
    const words = String(name || 'User').trim().split(' ');
    const first = words[0]?.charAt(0) || 'U';
    const second = words.length > 1 ? words[1].charAt(0) : '';
    return (first + second).toUpperCase();
}

function roleBadge(role) {
    const value = normalizeRole(role);

    if (value === 'admin') {
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-600">Admin</span>';
    }

    if (value === 'staff') {
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-600">Service Staff</span>';
    }

    if (value === 'kitchen') {
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-600">Kitchen Staff</span>';
    }

    if (value === 'customer') {
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-600">Customer</span>';
    }

    return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">User</span>';
}

function formatDate(dateValue) {
    if (!dateValue) return 'No date';

    const date = new Date(dateValue);

    if (Number.isNaN(date.getTime())) {
        return 'Invalid date';
    }

    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: '2-digit',
        year: 'numeric'
    });
}

async function loadUsers() {
    const tbody = document.getElementById('usersTableBody');

    try {
        const res = await fetch('/admin/users/list', {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!res.ok) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-red-500">
                        Failed to load users. Request returned ${res.status}.
                    </td>
                </tr>
            `;
            return;
        }

        const data = await res.json();

        if (Array.isArray(data)) {
            users = data;
        } else if (Array.isArray(data.data)) {
            users = data.data;
        } else if (Array.isArray(data.users)) {
            users = data.users;
        } else {
            users = [];
        }

        populateRoleFilter();
        renderCards();
        applyFilters();
    } catch (error) {
        console.error('Failed to load users:', error);

        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-red-500">
                    JavaScript error while loading users.
                </td>
            </tr>
        `;
    }
}

function populateRoleFilter() {
    const select = document.getElementById('roleFilter');
    const roles = [...new Set(users.map(user => normalizeRole(user.role)))].filter(Boolean);

    select.innerHTML = '<option value="all">All Roles</option>';

    roles.forEach(role => {
        select.innerHTML += `<option value="${safeText(role)}">${formatRole(role)}</option>`;
    });
}

function renderCards() {
    const totalUsers = users.length;
    const admins = users.filter(user => normalizeRole(user.role) === 'admin').length;
    const serviceStaff = users.filter(user => normalizeRole(user.role) === 'staff').length;
    const kitchenStaff = users.filter(user => normalizeRole(user.role) === 'kitchen').length;

    document.getElementById('cardTotalUsers').textContent = totalUsers;
    document.getElementById('cardAdmins').textContent = admins;
    document.getElementById('cardServiceStaff').textContent = serviceStaff;
    document.getElementById('cardKitchenStaff').textContent = kitchenStaff;
}

function applyFilters() {
    const search = document.getElementById('userSearch').value.toLowerCase().trim();
    const role = document.getElementById('roleFilter').value;

    filteredUsers = users.filter(user => {
        const name = String(user.name ?? '').toLowerCase();
        const email = String(user.email ?? '').toLowerCase();
        const userRole = normalizeRole(user.role);
        const userRoleLabel = formatRole(user.role).toLowerCase();

        const matchesSearch =
            name.includes(search) ||
            email.includes(search) ||
            userRole.includes(search) ||
            userRoleLabel.includes(search);

        const matchesRole =
            role === 'all' ? true : userRole === role;

        return matchesSearch && matchesRole;
    });

    renderUsersTable();
}

function renderUsersTable() {
    const tbody = document.getElementById('usersTableBody');

    if (!filteredUsers.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                    No users found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = filteredUsers.map(user => `
        <tr class="border-t hover:bg-gray-50">
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-xs font-bold">
                        ${safeText(initials(user.name))}
                    </div>
                    <div>
                        <p class="font-medium">${safeText(user.name)}</p>
                        <p class="text-xs text-gray-400">User ID: ${safeText(user.id)}</p>
                    </div>
                </div>
            </td>

            <td class="px-6 py-4">
                ${roleBadge(user.role)}
            </td>

            <td class="px-6 py-4">
                ${safeText(user.email)}
            </td>

            <td class="px-6 py-4 text-gray-500">
                ${safeText(formatDate(user.created_at))}
            </td>

            <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                    <button onclick="openUserModal(${user.id})"
                        class="px-3 py-2 rounded border text-gray-700 hover:bg-gray-50 text-xs">
                        Edit
                    </button>

                    <button onclick="deleteUser(${user.id})"
                        class="px-3 py-2 rounded border border-red-200 text-red-600 hover:bg-red-50 text-xs">
                        Delete
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openUserModal(id = null) {
    editingUserId = id;

    document.getElementById('userForm').reset();

    if (id) {
        const user = users.find(item => Number(item.id) === Number(id));
        if (!user) return;

        document.getElementById('userModalTitle').textContent = 'Edit User';
        document.getElementById('userSaveBtn').textContent = 'Update User';
        document.getElementById('passwordNote').textContent = '(leave blank to keep current password)';

        document.getElementById('userName').value = user.name || '';
        document.getElementById('userEmail').value = user.email || '';
        document.getElementById('userRole').value = normalizeRole(user.role);
        document.getElementById('userPassword').required = false;
    } else {
        document.getElementById('userModalTitle').textContent = 'Add User';
        document.getElementById('userSaveBtn').textContent = 'Save User';
        document.getElementById('passwordNote').textContent = '(required for new user)';
        document.getElementById('userPassword').required = true;
    }

    const modal = document.getElementById('userModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeUserModal() {
    editingUserId = null;

    const modal = document.getElementById('userModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.getElementById('userForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const payload = {
        name: document.getElementById('userName').value,
        email: document.getElementById('userEmail').value,
        role: document.getElementById('userRole').value
    };

    const password = document.getElementById('userPassword').value;

    if (password) {
        payload.password = password;
    }

    const url = editingUserId
        ? `/admin/users/${editingUserId}`
        : '/admin/users';

    const method = editingUserId ? 'PUT' : 'POST';

    const res = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload)
    });

    const data = await res.json();

    if (!res.ok) {
        if (data.errors) {
            const firstError = Object.values(data.errors)[0][0];
            alert(firstError);
        } else {
            alert(data.message || 'Failed to save user.');
        }
        return;
    }

    closeUserModal();
    loadUsers();
});

async function deleteUser(id) {
    if (!confirm('Delete this user?')) return;

    const res = await fetch(`/admin/users/${id}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        }
    });

    const data = await res.json().catch(() => ({}));

    if (!res.ok) {
        alert(data.message || 'Failed to delete user.');
        return;
    }

    loadUsers();
}

document.getElementById('userSearch').addEventListener('input', applyFilters);
document.getElementById('roleFilter').addEventListener('change', applyFilters);

loadUsers();
</script>

@endsection