@extends('layouts.admin')

@section('content')

<div class="space-y-5 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold mb-1 text-gray-900">User Management</h1>
            <p class="text-sm sm:text-base text-gray-500">
                Manage admin, staff, customer, and table tablet accounts.
            </p>
        </div>

        <button onclick="openUserModal()"
            class="w-full sm:w-auto bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-xl font-semibold shadow-sm">
            + Add User
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <p class="text-sm text-gray-500 mb-2">Total Users</p>
            <h2 id="cardTotalUsers" class="text-2xl sm:text-3xl font-bold">0</h2>
            <p class="text-xs text-gray-400 mt-2">All saved accounts</p>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <p class="text-sm text-gray-500 mb-2">Staff Accounts</p>
            <h2 id="cardStaffAccounts" class="text-2xl sm:text-3xl font-bold text-orange-500">0</h2>
            <p class="text-xs text-gray-400 mt-2">Admin, service, kitchen</p>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <p class="text-sm text-gray-500 mb-2">Customers</p>
            <h2 id="cardCustomerAccounts" class="text-2xl sm:text-3xl font-bold text-green-500">0</h2>
            <p class="text-xs text-gray-400 mt-2">Registered customers</p>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
            <p class="text-sm text-gray-500 mb-2">Table Accounts</p>
            <h2 id="cardTableAccounts" class="text-2xl sm:text-3xl font-bold text-blue-500">0</h2>
            <p class="text-xs text-gray-400 mt-2">Tablet ordering accounts</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl border shadow-sm p-4 sm:p-5">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
            <div class="min-w-0">
                <h2 class="text-lg font-bold">Account Directory</h2>
                <p class="text-sm text-gray-500">Search and manage accounts by account type.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full xl:w-auto">
                <input
                    id="userSearch"
                    type="text"
                    placeholder="Search name, email, or role..."
                    class="border rounded-xl px-4 py-2.5 w-full xl:w-80"
                >

                <select id="roleFilter" class="border rounded-xl px-4 py-2.5 w-full xl:w-52">
                    <option value="all">All Accounts</option>
                    <option value="staff">Staff Accounts</option>
                    <option value="customer">Customer Accounts</option>
                    <option value="table">Table Accounts</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Staff Accounts -->
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b bg-orange-50/40">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-gray-900">Staff Accounts</h2>
                    <p class="text-sm text-gray-500">Admin, service staff, and kitchen staff accounts.</p>
                </div>

                <span id="staffCountBadge" class="px-3 py-1 rounded-full bg-orange-100 text-orange-600 text-xs font-bold w-fit">
                    0 accounts
                </span>
            </div>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full min-w-[860px] text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold">Name</th>
                        <th class="text-left px-6 py-4 font-semibold">Role</th>
                        <th class="text-left px-6 py-4 font-semibold">Email</th>
                        <th class="text-left px-6 py-4 font-semibold">Created</th>
                        <th class="text-right px-6 py-4 font-semibold">Actions</th>
                    </tr>
                </thead>

                <tbody id="staffUsersTableBody">
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                            Loading staff accounts...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div id="staffUsersMobileList" class="md:hidden p-4 space-y-3">
            <div class="px-4 py-8 text-center text-gray-400">
                Loading staff accounts...
            </div>
        </div>
    </div>

    <!-- Customer Accounts -->
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b bg-green-50/40">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-gray-900">Customer Accounts</h2>
                    <p class="text-sm text-gray-500">Registered customer accounts are view-only for privacy and security.</p>
                </div>

                <span id="customerCountBadge" class="px-3 py-1 rounded-full bg-green-100 text-green-600 text-xs font-bold w-fit">
                    0 accounts
                </span>
            </div>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full min-w-[860px] text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold">Name</th>
                        <th class="text-left px-6 py-4 font-semibold">Role</th>
                        <th class="text-left px-6 py-4 font-semibold">Email</th>
                        <th class="text-left px-6 py-4 font-semibold">Created</th>
                        <th class="text-right px-6 py-4 font-semibold">Access</th>
                    </tr>
                </thead>

                <tbody id="customerUsersTableBody">
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                            Loading customer accounts...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div id="customerUsersMobileList" class="md:hidden p-4 space-y-3">
            <div class="px-4 py-8 text-center text-gray-400">
                Loading customer accounts...
            </div>
        </div>
    </div>

    <!-- Table / Tablet Accounts -->
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b bg-blue-50/40">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-gray-900">Table / Tablet Accounts</h2>
                    <p class="text-sm text-gray-500">Dedicated accounts used by restaurant table tablets.</p>
                </div>

                <span id="tableCountBadge" class="px-3 py-1 rounded-full bg-blue-100 text-blue-600 text-xs font-bold w-fit">
                    0 accounts
                </span>
            </div>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full min-w-[860px] text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold">Table Account</th>
                        <th class="text-left px-6 py-4 font-semibold">Role</th>
                        <th class="text-left px-6 py-4 font-semibold">Email</th>
                        <th class="text-left px-6 py-4 font-semibold">Created</th>
                        <th class="text-right px-6 py-4 font-semibold">Actions</th>
                    </tr>
                </thead>

                <tbody id="tableUsersTableBody">
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                            Loading table accounts...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div id="tableUsersMobileList" class="md:hidden p-4 space-y-3">
            <div class="px-4 py-8 text-center text-gray-400">
                Loading table accounts...
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div id="userModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-3 sm:p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[92vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between gap-3 p-4 sm:p-5 border-b shrink-0">
            <div class="min-w-0">
                <h3 id="userModalTitle" class="text-lg font-bold">Add User</h3>
                <p class="text-xs text-gray-500">Create or update admin and staff accounts.</p>
            </div>

            <button onclick="closeUserModal()"
                class="w-9 h-9 rounded-full hover:bg-gray-100 text-gray-500 hover:text-black text-xl shrink-0">
                &times;
            </button>
        </div>

        <form id="userForm" class="p-4 sm:p-5 space-y-4 overflow-y-auto">
            <div>
                <label class="block text-sm font-semibold mb-1">Full Name</label>
                <input id="userName" type="text" class="w-full border rounded-xl px-3 py-2.5" required>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Email</label>
                <input id="userEmail" type="email" class="w-full border rounded-xl px-3 py-2.5" required>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Role</label>
                <select id="userRole" class="w-full border rounded-xl px-3 py-2.5" required>
                    <option value="admin">Admin</option>
                    <option value="service_staff">Service Staff</option>
                    <option value="kitchen_staff">Kitchen Staff</option>
                </select>
                <p class="text-xs text-gray-400 mt-1">
                    Customer accounts are created from customer registration and are view-only here.
                </p>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">
                    Password <span id="passwordOptionalText" class="text-gray-400 font-normal hidden">(leave blank to keep current)</span>
                </label>
                <input id="userPassword" type="password" class="w-full border rounded-xl px-3 py-2.5" required>
            </div>

            <div class="border-t pt-4 flex flex-col sm:flex-row sm:justify-end gap-2">
                <button type="button" onclick="closeUserModal()"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium">
                    Cancel
                </button>

                <button id="userSaveBtn" type="submit"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold disabled:opacity-70 disabled:cursor-not-allowed">
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

function safeText(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function formatDate(value) {
    if (!value) return '-';

    let year;
    let month;
    let day;

    // Prevent timezone conversion issue.
    // Example: 2026-06-23T16:00:00.000000Z should still display Jun 23, not Jun 24.
    const dateString = String(value);

    if (dateString.includes('T')) {
        const dateOnly = dateString.split('T')[0];
        [year, month, day] = dateOnly.split('-').map(Number);
    } else if (dateString.includes(' ')) {
        const dateOnly = dateString.split(' ')[0];
        [year, month, day] = dateOnly.split('-').map(Number);
    } else {
        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return '-';
        }

        year = date.getFullYear();
        month = date.getMonth() + 1;
        day = date.getDate();
    }

    if (!year || !month || !day) {
        return '-';
    }

    const monthNames = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];

    return `${monthNames[month - 1]} ${String(day).padStart(2, '0')}, ${year}`;
}

function formatRole(role) {
    const normalized = normalizeRole(role);

    if (normalized === 'admin') return 'Admin';
    if (normalized === 'staff') return 'Service Staff';
    if (normalized === 'kitchen') return 'Kitchen Staff';
    if (normalized === 'customer') return 'Customer';
    if (normalized === 'table') return 'Table Account';

    return String(role || 'User')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, char => char.toUpperCase());
}

function normalizeRole(role) {
    const value = String(role || '').toLowerCase();

    if (value === 'admin') return 'admin';
    if (value === 'staff' || value === 'service_staff') return 'staff';
    if (value === 'kitchen' || value === 'kitchen_staff') return 'kitchen';
    if (value === 'customer') return 'customer';
    if (value === 'table_customer' || value === 'tablet' || value === 'table') return 'table';

    return value;
}

function isTableAccount(user) {
    const role = normalizeRole(user.role);
    const name = String(user.name || '').toLowerCase();
    const email = String(user.email || '').toLowerCase();

    return role === 'table'
        || name.includes('table')
        || email.startsWith('table')
        || email.includes('@dinesync.com') && email.includes('table');
}

function getAccountGroup(user) {
    if (isTableAccount(user)) {
        return 'table';
    }

    const role = normalizeRole(user.role);

    if (role === 'customer') {
        return 'customer';
    }

    return 'staff';
}

function roleBadge(user) {
    const group = getAccountGroup(user);
    const label = formatRole(user.role);

    if (group === 'customer') {
        return `<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-600">${safeText(label)}</span>`;
    }

    if (group === 'table') {
        return `<span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-600">Table Account</span>`;
    }

    return `<span class="px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-600">${safeText(label)}</span>`;
}

function setButtonLoading(button, isLoading, loadingText = 'Saving...') {
    if (!button) return;

    if (isLoading) {
        button.dataset.originalText = button.textContent;
        button.textContent = loadingText;
        button.disabled = true;
        button.classList.add('opacity-70', 'cursor-not-allowed');
    } else {
        button.textContent = button.dataset.originalText || button.textContent;
        button.disabled = false;
        button.classList.remove('opacity-70', 'cursor-not-allowed');
    }
}

function updateSummaryCards() {
    const staff = users.filter(user => getAccountGroup(user) === 'staff').length;
    const customer = users.filter(user => getAccountGroup(user) === 'customer').length;
    const table = users.filter(user => getAccountGroup(user) === 'table').length;

    document.getElementById('cardTotalUsers').textContent = users.length.toLocaleString();
    document.getElementById('cardStaffAccounts').textContent = staff.toLocaleString();
    document.getElementById('cardCustomerAccounts').textContent = customer.toLocaleString();
    document.getElementById('cardTableAccounts').textContent = table.toLocaleString();

    document.getElementById('staffCountBadge').textContent = `${staff} account${staff === 1 ? '' : 's'}`;
    document.getElementById('customerCountBadge').textContent = `${customer} account${customer === 1 ? '' : 's'}`;
    document.getElementById('tableCountBadge').textContent = `${table} account${table === 1 ? '' : 's'}`;
}

async function loadUsers() {
    setLoadingState();

    try {
        const res = await fetch('/api/admin/users', {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!res.ok) {
            setErrorState(`Failed to load users. API returned ${res.status}.`);
            return;
        }

        const data = await res.json();
        users = Array.isArray(data) ? data : (data.users || data.data || []);

        updateSummaryCards();
        applyFilters();
    } catch (error) {
        console.error('Load users failed:', error);
        setErrorState('Failed to load users. Please check your connection.');
    }
}

function setLoadingState() {
    const loadingRow5 = `
        <tr>
            <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                Loading accounts...
            </td>
        </tr>
    `;

    document.getElementById('staffUsersTableBody').innerHTML = loadingRow5;
    document.getElementById('customerUsersTableBody').innerHTML = loadingRow5;
    document.getElementById('tableUsersTableBody').innerHTML = loadingRow5;

    document.getElementById('staffUsersMobileList').innerHTML = '<div class="px-4 py-8 text-center text-gray-400">Loading staff accounts...</div>';
    document.getElementById('customerUsersMobileList').innerHTML = '<div class="px-4 py-8 text-center text-gray-400">Loading customer accounts...</div>';
    document.getElementById('tableUsersMobileList').innerHTML = '<div class="px-4 py-8 text-center text-gray-400">Loading table accounts...</div>';
}

function setErrorState(message) {
    const errorRow = `
        <tr>
            <td colspan="5" class="px-6 py-8 text-center text-red-500">
                ${safeText(message)}
            </td>
        </tr>
    `;

    document.getElementById('staffUsersTableBody').innerHTML = errorRow;
    document.getElementById('customerUsersTableBody').innerHTML = errorRow;
    document.getElementById('tableUsersTableBody').innerHTML = errorRow;

    document.getElementById('staffUsersMobileList').innerHTML = `<div class="px-4 py-8 text-center text-red-500">${safeText(message)}</div>`;
    document.getElementById('customerUsersMobileList').innerHTML = `<div class="px-4 py-8 text-center text-red-500">${safeText(message)}</div>`;
    document.getElementById('tableUsersMobileList').innerHTML = `<div class="px-4 py-8 text-center text-red-500">${safeText(message)}</div>`;
}

function applyFilters() {
    const search = document.getElementById('userSearch').value.toLowerCase().trim();
    const roleFilter = document.getElementById('roleFilter').value;

    filteredUsers = users.filter(user => {
        const group = getAccountGroup(user);

        const matchesSearch =
            String(user.name || '').toLowerCase().includes(search) ||
            String(user.email || '').toLowerCase().includes(search) ||
            String(user.role || '').toLowerCase().includes(search) ||
            formatRole(user.role).toLowerCase().includes(search);

        const matchesRole = roleFilter === 'all' ? true : group === roleFilter;

        return matchesSearch && matchesRole;
    });

    renderAllSections();
}

function renderAllSections() {
    const staffUsers = filteredUsers.filter(user => getAccountGroup(user) === 'staff');
    const customerUsers = filteredUsers.filter(user => getAccountGroup(user) === 'customer');
    const tableUsers = filteredUsers.filter(user => getAccountGroup(user) === 'table');

    renderUserTable('staffUsersTableBody', staffUsers, 'staff');
    renderUserTable('customerUsersTableBody', customerUsers, 'customer');
    renderUserTable('tableUsersTableBody', tableUsers, 'table');

    renderUserCards('staffUsersMobileList', staffUsers, 'staff');
    renderUserCards('customerUsersMobileList', customerUsers, 'customer');
    renderUserCards('tableUsersMobileList', tableUsers, 'table');
}

function renderUserTable(tbodyId, list, group) {
    const tbody = document.getElementById(tbodyId);

    if (!list.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                    No ${group} accounts found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = list.map(user => {
        const isCustomer = getAccountGroup(user) === 'customer';

        return `
            <tr class="border-t hover:bg-gray-50">
                <td class="px-6 py-4">
                    <p class="font-semibold text-gray-900">${safeText(user.name)}</p>
                </td>

                <td class="px-6 py-4">
                    ${roleBadge(user)}
                </td>

                <td class="px-6 py-4 break-words max-w-[280px]">
                    ${safeText(user.email)}
                </td>

                <td class="px-6 py-4">
                    ${safeText(formatDate(user.created_at))}
                </td>

                <td class="px-6 py-4 text-right">
                    ${
                        isCustomer
                            ? `
                                <span class="inline-flex px-3 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold">
                                    View Only
                                </span>
                            `
                            : `
                                <div class="flex justify-end gap-2">
                                    <button onclick="openUserModal(${Number(user.id)})"
                                        class="px-3 py-2 rounded-xl border text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                                        Edit
                                    </button>

                                    <button onclick="deleteUser(${Number(user.id)}, this)"
                                        class="px-3 py-2 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 text-xs font-semibold">
                                        Delete
                                    </button>
                                </div>
                            `
                    }
                </td>
            </tr>
        `;
    }).join('');
}

function renderUserCards(containerId, list, group) {
    const container = document.getElementById(containerId);

    if (!list.length) {
        container.innerHTML = `
            <div class="px-4 py-8 text-center text-gray-400">
                No ${group} accounts found.
            </div>
        `;
        return;
    }

    container.innerHTML = list.map(user => {
        const isCustomer = getAccountGroup(user) === 'customer';

        return `
            <div class="rounded-2xl border bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="font-bold text-gray-900 leading-snug">${safeText(user.name)}</h3>
                        <p class="text-xs text-gray-500 mt-1 break-words">${safeText(user.email)}</p>
                    </div>

                    <div class="shrink-0">
                        ${roleBadge(user)}
                    </div>
                </div>

                <div class="mt-3 rounded-xl bg-gray-50 border px-3 py-2">
                    <p class="text-xs text-gray-600">
                        <span class="font-semibold">Created:</span>
                        ${safeText(formatDate(user.created_at))}
                    </p>
                </div>

                ${
                    isCustomer
                        ? `
                            <div class="mt-3">
                                <span class="inline-flex px-3 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold">
                                    View Only
                                </span>
                            </div>
                        `
                        : `
                            <div class="grid grid-cols-2 gap-2 mt-4">
                                <button onclick="openUserModal(${Number(user.id)})"
                                    class="px-3 py-2.5 rounded-xl border text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                                    Edit
                                </button>

                                <button onclick="deleteUser(${Number(user.id)}, this)"
                                    class="px-3 py-2.5 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 text-xs font-semibold">
                                    Delete
                                </button>
                            </div>
                        `
                }
            </div>
        `;
    }).join('');
}

function openUserModal(id = null) {
    editingUserId = id;

    const form = document.getElementById('userForm');
    const passwordInput = document.getElementById('userPassword');
    const passwordOptionalText = document.getElementById('passwordOptionalText');
    const saveBtn = document.getElementById('userSaveBtn');

    form.reset();

    if (id) {
        const user = users.find(item => Number(item.id) === Number(id));

        if (!user) return;

        if (getAccountGroup(user) === 'customer') {
            alert('Customer accounts are view-only for privacy and security.');
            return;
        }

        document.getElementById('userModalTitle').textContent = 'Edit User';
        saveBtn.textContent = 'Update User';

        document.getElementById('userName').value = user.name || '';
        document.getElementById('userEmail').value = user.email || '';

        const role = normalizeRole(user.role);

        if (role === 'admin') {
            document.getElementById('userRole').value = 'admin';
        } else if (role === 'kitchen') {
            document.getElementById('userRole').value = 'kitchen_staff';
        } else {
            document.getElementById('userRole').value = 'service_staff';
        }

        passwordInput.required = false;
        passwordOptionalText.classList.remove('hidden');
    } else {
        document.getElementById('userModalTitle').textContent = 'Add User';
        saveBtn.textContent = 'Save User';
        document.getElementById('userRole').value = 'service_staff';
        passwordInput.required = true;
        passwordOptionalText.classList.add('hidden');
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

    const saveBtn = document.getElementById('userSaveBtn');

    const payload = {
        name: document.getElementById('userName').value,
        email: document.getElementById('userEmail').value,
        role: document.getElementById('userRole').value,
    };

    const password = document.getElementById('userPassword').value;

    if (password) {
        payload.password = password;
    }

    let url = '/api/admin/users';
    let method = 'POST';

    if (editingUserId) {
        url = `/api/admin/users/${editingUserId}`;
        method = 'PUT';
    }

    setButtonLoading(saveBtn, true, editingUserId ? 'Updating...' : 'Saving...');

    try {
        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
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

        saveBtn.textContent = editingUserId ? 'Updated!' : 'Saved!';

        closeUserModal();
        await loadUsers();
    } catch (error) {
        console.error('Save user failed:', error);
        alert('Failed to save user. Please check your connection.');
    } finally {
        setButtonLoading(saveBtn, false);
    }
});

async function deleteUser(id, button) {
    const user = users.find(item => Number(item.id) === Number(id));

    if (!user) return;

    if (getAccountGroup(user) === 'customer') {
        alert('Customer accounts are view-only for privacy and security.');
        return;
    }

    if (!confirm(`Delete ${user.name}?`)) return;

    setButtonLoading(button, true, 'Deleting...');

    try {
        const res = await fetch(`/api/admin/users/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
            }
        });

        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            alert(data.message || 'Failed to delete user.');
            return;
        }

        users = users.filter(item => Number(item.id) !== Number(id));

        updateSummaryCards();
        applyFilters();
    } catch (error) {
        console.error('Delete user failed:', error);
        alert('Failed to delete user. Please check your connection.');
    } finally {
        setButtonLoading(button, false);
    }
}

document.getElementById('userSearch').addEventListener('input', applyFilters);
document.getElementById('roleFilter').addEventListener('change', applyFilters);

loadUsers();
</script>

@endsection