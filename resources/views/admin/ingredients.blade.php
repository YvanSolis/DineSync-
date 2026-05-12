@extends('layouts.admin')

@section('content')

<div class="space-y-6">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-3xl font-bold mb-1">Inventory Management</h1>
            <p class="text-gray-500">Track and manage ingredient stock levels.</p>
        </div>

        <button onclick="openIngredientModal()"
            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded font-medium">
            + Add Ingredient
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="border border-red-200 bg-red-50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 font-bold">
                    !
                </div>
                <div>
                    <p class="font-semibold text-red-700">Critical Stock Alert</p>
                    <p class="text-sm text-gray-600" id="criticalSummaryText">0 items are critically low and need immediate restocking</p>
                </div>
            </div>
        </div>

        <div class="border border-yellow-200 bg-yellow-50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-700 font-bold">
                    !
                </div>
                <div>
                    <p class="font-semibold text-yellow-700">Low Stock Warning</p>
                    <p class="text-sm text-gray-600" id="warningSummaryText">0 items are running low and should be restocked soon</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-lg shadow border">
        <div class="p-5 border-b">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h2 class="text-lg font-bold">Ingredient Inventory</h2>
                    <p class="text-sm text-gray-500">Overview of current stock levels and status.</p>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <input
                        id="inventorySearch"
                        type="text"
                        placeholder="Search ingredients..."
                        class="border rounded px-3 py-2 w-64"
                    >

                    <select id="statusFilter" class="border rounded px-3 py-2">
                        <option value="all">All Status</option>
                        <option value="active">Normal</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="reorder_soon">Reorder Soon</option>
                        <option value="near_expiry">Near Expiry</option>
                        <option value="out_of_stock">Out of Stock</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold">Ingredient</th>
                        <th class="text-left px-6 py-4 font-semibold">Current Stock</th>
                        <th class="text-left px-6 py-4 font-semibold">Unit</th>
                        <th class="text-left px-6 py-4 font-semibold">Threshold</th>
                        <th class="text-left px-6 py-4 font-semibold">Status</th>
                        <th class="text-left px-6 py-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody id="inventoryTableBody">
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">Loading inventory...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add / Edit Ingredient Modal -->
<div id="ingredientModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between p-5 border-b">
            <h3 id="ingredientModalTitle" class="text-lg font-bold">Add Ingredient</h3>
            <button onclick="closeIngredientModal()" class="text-gray-500 hover:text-black text-xl">&times;</button>
        </div>

        <form id="ingredientForm" class="p-5 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Ingredient Name</label>
                <input id="ingredientName" type="text" class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Alert When Stock Reaches</label>
                <input id="ingredientThreshold" type="number" step="0.01" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeIngredientModal()" class="px-4 py-2 rounded bg-gray-200 text-gray-700">
                    Cancel
                </button>
                <button id="ingredientSaveBtn" type="submit" class="px-4 py-2 rounded bg-orange-500 text-white">
                    Save Ingredient
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Manage Stock Modal -->
<div id="stockModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-6xl max-h-[92vh] overflow-y-auto">
        <div class="flex items-start justify-between p-5 border-b">
            <div>
                <h3 id="stockModalTitle" class="text-xl font-bold">Manage Stock</h3>
                <p id="stockModalSubtitle" class="text-sm text-gray-500 mt-1"></p>
            </div>

            <button onclick="closeStockModal()" class="text-gray-500 hover:text-black text-xl">&times;</button>
        </div>

        <div class="p-5 space-y-6">
            <!-- Ingredient Info Actions -->
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                    <div class="bg-gray-50 rounded p-3">
                        <p class="text-gray-500">Current Stock</p>
                        <p id="stockInfoCurrent" class="font-semibold">0</p>
                    </div>
                    <div class="bg-gray-50 rounded p-3">
                        <p class="text-gray-500">Threshold</p>
                        <p id="stockInfoThreshold" class="font-semibold">0</p>
                    </div>
                    <div class="bg-gray-50 rounded p-3">
                        <p class="text-gray-500">Stock Value</p>
                        <p id="stockInfoValue" class="font-semibold">₱0.00</p>
                    </div>
                    <div class="bg-gray-50 rounded p-3">
                        <p class="text-gray-500">Nearest Expiry</p>
                        <p id="stockInfoExpiry" class="font-semibold">N/A</p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="button" onclick="editCurrentIngredient()" class="px-4 py-2 rounded bg-blue-500 text-white text-sm">
                        Edit Ingredient
                    </button>
                    <button type="button" onclick="deleteCurrentIngredient()" class="px-4 py-2 rounded bg-red-500 text-white text-sm">
                        Delete Ingredient
                    </button>
                </div>
            </div>

            <!-- Batch Form -->
            <div class="border rounded-lg p-4">
                <h4 id="batchFormTitle" class="font-bold mb-4">Add Stock Batch</h4>

                <form id="batchForm" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <input id="batchQuantity" type="number" step="0.01" placeholder="Batch Quantity" class="border rounded px-3 py-2" required>

                    <select id="batchUnit" class="border rounded px-3 py-2" required>
                        <option value="">Select Unit</option>
                        <option value="kg">kg</option>
                        <option value="g">g</option>
                        <option value="pcs">pcs</option>
                        <option value="pack">pack</option>
                        <option value="bottle">bottle</option>
                        <option value="liter">liter</option>
                        <option value="ml">ml</option>
                    </select>

                    <input id="batchUnitCost" type="number" step="0.01" placeholder="Unit Cost" class="border rounded px-3 py-2" required>

                    <input id="batchExpiryDate" type="date" class="border rounded px-3 py-2" required>

                    <input id="batchSupplier" type="text" placeholder="Supplier (optional)" class="border rounded px-3 py-2">
                </form>

                <div class="flex gap-2 mt-4">
                    <button id="batchSaveBtn" onclick="saveBatch()" class="px-4 py-2 rounded bg-orange-500 text-white">
                        Save Batch
                    </button>

                    <button id="batchCancelBtn" onclick="cancelBatchEdit()" class="px-4 py-2 rounded bg-gray-200 text-gray-700 hidden">
                        Cancel Edit
                    </button>

                    <button id="batchDeleteBtn" onclick="deleteSelectedBatch()" class="px-4 py-2 rounded bg-red-500 text-white hidden">
                        Delete Selected Batch
                    </button>
                </div>
            </div>

            <!-- Batch History -->
            <div class="border rounded-lg">
                <div class="p-4 border-b">
                    <h4 class="font-bold">Stock Batch History</h4>
                    <p class="text-sm text-gray-500">Click a row to edit that batch.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-4 py-3">Remaining</th>
                                <th class="text-left px-4 py-3">Batch Quantity</th>
                                <th class="text-left px-4 py-3">Unit Cost</th>
                                <th class="text-left px-4 py-3">Expiry Date</th>
                                <th class="text-left px-4 py-3">Supplier</th>
                                <th class="text-left px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody id="batchHistoryBody">
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-400">No stock batches yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let ingredients = [];
let filteredIngredients = [];

let editingIngredientId = null;
let currentIngredientId = null;
let editingBatchId = null;

function safeText(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function formatMoney(value) {
    return `₱${Number(value || 0).toFixed(2)}`;
}

function formatNumber(value) {
    return Number(value || 0).toFixed(2);
}

function formatDate(value) {
    if (!value) return 'N/A';
    return String(value).substring(0, 10);
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

function findIngredientFromResponse(data) {
    if (!data) return null;

    if (data.ingredient && data.ingredient.id) return data.ingredient;
    if (data.data && data.data.id) return data.data;
    if (data.id && data.name) return data;

    return null;
}

function replaceIngredientInMemory(updatedIngredient) {
    if (!updatedIngredient || !updatedIngredient.id) return false;

    const index = ingredients.findIndex(item => Number(item.id) === Number(updatedIngredient.id));

    if (index >= 0) {
        ingredients[index] = updatedIngredient;
    } else {
        ingredients.push(updatedIngredient);
    }

    applyFilters();
    renderSummary();
    return true;
}

async function silentReloadIngredients() {
    try {
        const res = await fetch('/api/admin/ingredients', {
            headers: {
                'Accept': 'application/json',
            }
        });

        if (!res.ok) return;

        const data = await res.json();

        ingredients = data;
        applyFilters();
        renderSummary();

        if (currentIngredientId) {
            const item = ingredients.find(i => Number(i.id) === Number(currentIngredientId));
            if (item) {
                refreshStockModalInfo(item);
            }
        }
    } catch (error) {
        console.error('Silent inventory reload failed:', error);
    }
}

function refreshStockModalInfo(item) {
    if (!item) return;

    document.getElementById('stockModalTitle').textContent = `Manage Stock - ${item.name}`;
    document.getElementById('stockModalSubtitle').textContent = `Monitor batches, prices, suppliers, and expiry dates for ${item.name}.`;

    document.getElementById('stockInfoCurrent').textContent = `${formatNumber(item.total_stock)} ${item.unit || 'unit'}`;
    document.getElementById('stockInfoThreshold').textContent = `${formatNumber(item.threshold)} ${item.unit || 'unit'}`;
    document.getElementById('stockInfoValue').textContent = formatMoney(item.stock_value);
    document.getElementById('stockInfoExpiry').textContent = formatDate(item.nearest_expiry_date);

    const batchUnit = document.getElementById('batchUnit');
    batchUnit.value = item.unit || '';

    renderBatchHistory(item.batches || []);
}

function getStatusLabel(status) {
    switch (status) {
        case 'out_of_stock': return 'Critical';
        case 'expired': return 'Critical';
        case 'low_stock': return 'Low';
        case 'reorder_soon': return 'Low';
        case 'near_expiry': return 'Low';
        default: return 'Normal';
    }
}

function getStatusClass(status) {
    switch (status) {
        case 'out_of_stock':
        case 'expired':
            return 'bg-red-100 text-red-600';
        case 'low_stock':
        case 'reorder_soon':
        case 'near_expiry':
            return 'bg-yellow-100 text-yellow-700';
        default:
            return 'bg-green-100 text-green-600';
    }
}

function getBatchStatusClass(batch) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const expiry = new Date(batch.expiry_date);
    expiry.setHours(0, 0, 0, 0);

    const diffDays = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));

    if (Number(batch.quantity_remaining) <= 0) {
        return { label: 'Used Up', class: 'bg-gray-100 text-gray-600' };
    }

    if (diffDays < 0) {
        return { label: 'Expired', class: 'bg-red-100 text-red-600' };
    }

    if (diffDays <= 3) {
        return { label: 'Near Expiry', class: 'bg-yellow-100 text-yellow-700' };
    }

    return { label: 'Active', class: 'bg-green-100 text-green-600' };
}

async function loadIngredients() {
    const tbody = document.getElementById('inventoryTableBody');

    try {
        const res = await fetch('/api/admin/ingredients', {
            headers: {
                'Accept': 'application/json',
            }
        });

        if (!res.ok) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-red-500">
                        Failed to load inventory. API returned ${res.status}.
                    </td>
                </tr>
            `;
            return;
        }

        const data = await res.json();

        ingredients = data;
        applyFilters();
        renderSummary();
    } catch (error) {
        console.error('Load ingredients failed:', error);

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-red-500">
                    Failed to load inventory. Please check your connection.
                </td>
            </tr>
        `;
    }
}

function applyFilters() {
    const search = document.getElementById('inventorySearch').value.toLowerCase().trim();
    const statusFilter = document.getElementById('statusFilter').value;

    filteredIngredients = ingredients.filter(item => {
        const matchesSearch =
            item.name.toLowerCase().includes(search);

        const matchesStatus =
            statusFilter === 'all' ? true : item.stock_status === statusFilter;

        return matchesSearch && matchesStatus;
    });

    renderTable();
}

function renderSummary() {
    const criticalCount = ingredients.filter(item =>
        ['out_of_stock', 'expired'].includes(item.stock_status)
    ).length;

    const warningCount = ingredients.filter(item =>
        ['low_stock', 'reorder_soon', 'near_expiry'].includes(item.stock_status)
    ).length;

    document.getElementById('criticalSummaryText').textContent =
        `${criticalCount} item${criticalCount !== 1 ? 's are' : ' is'} critically low and need immediate restocking`;

    document.getElementById('warningSummaryText').textContent =
        `${warningCount} item${warningCount !== 1 ? 's are' : ' is'} running low and should be restocked soon`;
}

function renderTable() {
    const tbody = document.getElementById('inventoryTableBody');

    if (!filteredIngredients.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-400">No ingredients found.</td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = filteredIngredients.map(item => `
        <tr class="border-t hover:bg-gray-50">
            <td class="px-6 py-4 font-medium">${safeText(item.name)}</td>
            <td class="px-6 py-4">${formatNumber(item.total_stock)} ${safeText(item.unit || 'unit')}</td>
            <td class="px-6 py-4">${safeText(item.unit || 'unit')}</td>
            <td class="px-6 py-4">${formatNumber(item.threshold)}</td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getStatusClass(item.stock_status)}">
                    ${getStatusLabel(item.stock_status)}
                </span>
            </td>
            <td class="px-6 py-4">
                <button onclick="openStockModal(${item.id})"
                    class="px-3 py-2 rounded border text-gray-700 hover:bg-gray-50">
                    Update Stock
                </button>
            </td>
        </tr>
    `).join('');
}

function openIngredientModal(id = null) {
    editingIngredientId = id;

    if (id) {
        const item = ingredients.find(i => Number(i.id) === Number(id));
        if (!item) return;

        document.getElementById('ingredientModalTitle').textContent = 'Edit Ingredient';
        document.getElementById('ingredientSaveBtn').textContent = 'Update Ingredient';
        document.getElementById('ingredientName').value = item.name;
        document.getElementById('ingredientThreshold').value = item.threshold;
    } else {
        document.getElementById('ingredientModalTitle').textContent = 'Add Ingredient';
        document.getElementById('ingredientSaveBtn').textContent = 'Save Ingredient';
        document.getElementById('ingredientForm').reset();
    }

    const modal = document.getElementById('ingredientModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeIngredientModal() {
    editingIngredientId = null;
    const modal = document.getElementById('ingredientModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.getElementById('ingredientForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const saveBtn = document.getElementById('ingredientSaveBtn');

    const payload = {
        name: document.getElementById('ingredientName').value,
        threshold: document.getElementById('ingredientThreshold').value
    };

    const url = editingIngredientId
        ? `/api/admin/ingredients/${editingIngredientId}`
        : '/api/admin/ingredients';

    const method = editingIngredientId ? 'PUT' : 'POST';

    setButtonLoading(saveBtn, true, editingIngredientId ? 'Updating...' : 'Saving...');

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
            alert(data.message || 'Failed to save ingredient.');
            return;
        }

        const updatedIngredient = findIngredientFromResponse(data);

        if (updatedIngredient) {
            replaceIngredientInMemory(updatedIngredient);
        } else {
            silentReloadIngredients();
        }

        closeIngredientModal();
    } catch (error) {
        console.error('Save ingredient failed:', error);
        alert('Failed to save ingredient. Please check your connection.');
    } finally {
        setButtonLoading(saveBtn, false);
    }
});

function openStockModal(id) {
    currentIngredientId = id;
    editingBatchId = null;

    const item = ingredients.find(i => Number(i.id) === Number(id));
    if (!item) return;

    refreshStockModalInfo(item);
    resetBatchForm();

    const modal = document.getElementById('stockModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeStockModal() {
    currentIngredientId = null;
    editingBatchId = null;

    const modal = document.getElementById('stockModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function renderBatchHistory(batches) {
    const tbody = document.getElementById('batchHistoryBody');

    if (!batches.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-6 text-center text-gray-400">No stock batches yet.</td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = batches.map(batch => {
        const status = getBatchStatusClass(batch);

        return `
            <tr class="border-t hover:bg-gray-50 cursor-pointer" onclick="selectBatchForEdit(${batch.id})">
                <td class="px-4 py-3">${formatNumber(batch.quantity_remaining)}</td>
                <td class="px-4 py-3">${formatNumber(batch.quantity_received)}</td>
                <td class="px-4 py-3">${formatMoney(batch.unit_cost)}</td>
                <td class="px-4 py-3">${formatDate(batch.expiry_date)}</td>
                <td class="px-4 py-3">${safeText(batch.supplier || 'N/A')}</td>
                <td class="px-4 py-3">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold ${status.class}">
                        ${status.label}
                    </span>
                </td>
            </tr>
        `;
    }).join('');
}

function resetBatchForm() {
    editingBatchId = null;

    document.getElementById('batchFormTitle').textContent = 'Add Stock Batch';
    document.getElementById('batchQuantity').value = '';
    document.getElementById('batchUnitCost').value = '';
    document.getElementById('batchExpiryDate').value = '';
    document.getElementById('batchSupplier').value = '';

    document.getElementById('batchSaveBtn').textContent = 'Save Batch';
    document.getElementById('batchCancelBtn').classList.add('hidden');
    document.getElementById('batchDeleteBtn').classList.add('hidden');
}

function selectBatchForEdit(batchId) {
    const item = ingredients.find(i => Number(i.id) === Number(currentIngredientId));
    if (!item) return;

    const batch = (item.batches || []).find(b => Number(b.id) === Number(batchId));
    if (!batch) return;

    editingBatchId = batch.id;

    document.getElementById('batchFormTitle').textContent = 'Edit Stock Batch';
    document.getElementById('batchQuantity').value = batch.quantity_received;
    document.getElementById('batchUnit').value = item.unit || '';
    document.getElementById('batchUnitCost').value = batch.unit_cost;
    document.getElementById('batchExpiryDate').value = formatDate(batch.expiry_date);
    document.getElementById('batchSupplier').value = batch.supplier || '';

    document.getElementById('batchSaveBtn').textContent = 'Update Batch';
    document.getElementById('batchCancelBtn').classList.remove('hidden');
    document.getElementById('batchDeleteBtn').classList.remove('hidden');
}

function cancelBatchEdit() {
    resetBatchForm();
}

async function saveBatch() {
    if (!currentIngredientId) return;

    const saveBtn = document.getElementById('batchSaveBtn');

    const payload = {
        quantity_received: document.getElementById('batchQuantity').value,
        unit: document.getElementById('batchUnit').value,
        unit_cost: document.getElementById('batchUnitCost').value,
        expiry_date: document.getElementById('batchExpiryDate').value,
        supplier: document.getElementById('batchSupplier').value
    };

    const url = editingBatchId
        ? `/api/admin/ingredients/${currentIngredientId}/batches/${editingBatchId}`
        : `/api/admin/ingredients/${currentIngredientId}/stock`;

    const method = editingBatchId ? 'PUT' : 'POST';

    setButtonLoading(saveBtn, true, editingBatchId ? 'Updating...' : 'Saving...');

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
            alert(data.message || 'Failed to save batch.');
            return;
        }

        const updatedIngredient = findIngredientFromResponse(data);

        if (updatedIngredient) {
            replaceIngredientInMemory(updatedIngredient);
            refreshStockModalInfo(updatedIngredient);
            resetBatchForm();
        } else {
            resetBatchForm();
            silentReloadIngredients();
        }
    } catch (error) {
        console.error('Save batch failed:', error);
        alert('Failed to save batch. Please check your connection.');
    } finally {
        setButtonLoading(saveBtn, false);
    }
}

async function deleteSelectedBatch() {
    if (!currentIngredientId || !editingBatchId) return;

    if (!confirm('Delete this stock batch?')) return;

    const deleteBtn = document.getElementById('batchDeleteBtn');
    setButtonLoading(deleteBtn, true, 'Deleting...');

    try {
        const res = await fetch(`/api/admin/ingredients/${currentIngredientId}/batches/${editingBatchId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
            }
        });

        const data = await res.json();

        if (!res.ok) {
            alert(data.message || 'Failed to delete batch.');
            return;
        }

        const updatedIngredient = findIngredientFromResponse(data);

        if (updatedIngredient) {
            replaceIngredientInMemory(updatedIngredient);
            refreshStockModalInfo(updatedIngredient);
            resetBatchForm();
        } else {
            resetBatchForm();
            silentReloadIngredients();
        }
    } catch (error) {
        console.error('Delete batch failed:', error);
        alert('Failed to delete batch. Please check your connection.');
    } finally {
        setButtonLoading(deleteBtn, false);
    }
}

function editCurrentIngredient() {
    if (!currentIngredientId) return;

    const ingredientId = currentIngredientId;

    closeStockModal();

    setTimeout(() => {
        openIngredientModal(ingredientId);
    }, 100);
}

async function deleteCurrentIngredient() {
    if (!currentIngredientId) return;

    if (!confirm('Delete this ingredient? This will also delete its stock history.')) return;

    try {
        const res = await fetch(`/api/admin/ingredients/${currentIngredientId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
            }
        });

        const data = await res.json();

        if (!res.ok) {
            alert(data.message || 'Failed to delete ingredient.');
            return;
        }

        ingredients = ingredients.filter(item => Number(item.id) !== Number(currentIngredientId));
        applyFilters();
        renderSummary();

        closeStockModal();
        silentReloadIngredients();
    } catch (error) {
        console.error('Delete ingredient failed:', error);
        alert('Failed to delete ingredient. Please check your connection.');
    }
}

document.getElementById('inventorySearch').addEventListener('input', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);

loadIngredients();
</script>

@endsection