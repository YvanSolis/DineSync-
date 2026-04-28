<!DOCTYPE html>
<html>
<head>
    <title>DineSync Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">
    <aside class="w-64 bg-white shadow p-5">
        <h1 class="text-xl font-bold text-orange-500 mb-1">DineSync+</h1>
        <p class="text-xs text-gray-500 mb-8">Chef Oppa Admin System</p>

        <nav class="space-y-2">
            <a href="/admin/dashboard" class="block px-4 py-2 rounded hover:bg-orange-100">Dashboard</a>
            <a href="/admin/menu-items" class="block px-4 py-2 rounded hover:bg-orange-100">Menu Management</a>
            <a href="/admin/ingredients" class="block px-4 py-2 rounded hover:bg-orange-100">Inventory</a>
            <a href="/admin/payments" class="block px-4 py-2 rounded hover:bg-orange-100">Payments</a>
            <a href="/admin/reports" class="block px-4 py-2 rounded hover:bg-orange-100">Reports & Forecast</a>
            <a href="/admin/users" class="block px-4 py-2 rounded hover:bg-orange-100">User Management</a>
        </nav>
    </aside>

    <main class="flex-1">
        <header class="bg-white shadow px-8 py-4 flex justify-between items-center">
            <input class="border rounded px-3 py-2 w-80" placeholder="Search...">
            <span class="text-sm text-gray-600">Owner / Admin</span>
        </header>

        <section class="p-8">
            @yield('content')
        </section>
    </main>
</div>

</body>
</html>