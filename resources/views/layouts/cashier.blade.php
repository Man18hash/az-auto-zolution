<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'Auto Service Dashboard') }}</title>

  <!-- Icons & CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #1e3c72, #2a5298);
      color: #333;
    }
    .sidebar {
      width: 260px; height: 100vh; position: fixed;
      background: #1c1f26; box-shadow: 4px 0 10px rgba(0,0,0,0.2);
      color: #fff; overflow-y: auto;
    }
    .sidebar .logo-container { text-align: center; padding: 25px 0 15px; }
    .sidebar .logo-container img {
      width:90px; height:90px; object-fit:cover;
      border-radius:50%; border:2px solid #ffffff88;
    }
    .sidebar h4 { font-size:20px; font-weight:bold; margin-top:10px; color:#f8f9fa; }
    .sidebar a {
      display:flex; align-items:center;
      padding:14px 24px; font-size:15px;
      color:#dee2e6; text-decoration:none;
      transition: background 0.2s;
    }
    .sidebar a i { margin-right:12px; font-size:18px; }
    .sidebar a:hover { background:#495057; color:#fff; }
    .logout-btn {
      position:absolute; bottom:20px; left:20px;
      width:calc(100% - 40px);
    }
    .content {
      margin-left:260px; padding:40px 30px;
      background:#f1f4f9; min-height:100vh;
      box-shadow: inset 0 0 8px rgba(0,0,0,0.05);
      border-radius:8px;
      animation: fadeIn 0.4s ease-in-out;
      position: relative;
    }
    @keyframes fadeIn {
      from { opacity:0; transform: translateY(10px); }
      to   { opacity:1; transform: translateY(0);  }
    }
    /* Notification and Clock Row */
    .notif-row {
      position: absolute;
      top: 20px;
      right: 32px;
      z-index: 999;
      display: flex;
      align-items: center;
      gap: 20px;
    }
    .header-clock {
      font-size: 1.13rem;
      color: #212529;
      font-weight: bold;
      background: none;
      border: none;
      box-shadow: none;
      padding: 0;
      margin: 0;
      letter-spacing: 0.5px;
      min-width: 175px;
      text-align: right;
      user-select: none;
    }
    .notif-bell-box .btn {
      background: none;
      border: none;
      box-shadow: none;
      padding: 0;
    }
    .notif-bell-box .fa-bell {
      font-size: 28px;
    }
    .notif-bell-box .badge {
      position: absolute;
      top: -7px;
      right: -9px;
      font-size: 0.72em;
      padding: 3px 6px;
      border-radius: 50%;
    }
    .dropdown-menu {
      min-width: 240px;
      max-width: 330px;
      font-size: 1rem;
    }
    .low-stock-item-row {
      padding: 4px 16px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .dropdown-header {
      font-size: 1.05em;
      font-weight: bold;
      background: #f8d7da;
      color: #b30000;
    }
    .dropdown-divider {
      margin: 0;
    }
  </style>
</head>

<body>
  <div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="logo-container">
        <img src="{{ asset('images/logo.png') }}" alt="Logo">
        <h4 class="mt-2">AZ Auto Zolutions</h4>
      </div>
      <a href="{{ route('cashier.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="{{ route('cashier.appointment.index') }}"><i class="fas fa-calendar-check"></i> Appointments</a>
      <a href="{{ route('cashier.quotation.index') }}"><i class="fas fa-file-alt"></i> Quotation</a>
      <a href="{{ route('cashier.service-order') }}"><i class="fas fa-tools"></i> Service Orders</a>
      <a href="{{ route('cashier.invoice.index') }}"><i class="fas fa-file-invoice"></i> Invoicing</a>
      <a href="{{ route('cashier.inventory.index') }}"><i class="fas fa-boxes"></i> Inventory</a>
      <a href="{{ route('cashier.expenses.index') }}"><i class="fas fa-wallet"></i> Expenses</a>
      <a href="{{ route('cashier.ar-cashdeposit.index') }}"><i class="fas fa-hand-holding-usd"></i> A/R & Cash Deposit</a>
      <a href="{{ route('cashier.vehicles.index') }}"><i class="fas fa-users"></i> Clients & Vehicles</a>
      <a href="{{ route('cashier.history') }}"><i class="fas fa-history"></i> History</a>
      <form method="POST" action="{{ route('logout') }}" class="logout-btn">
        @csrf
        <button type="submit" class="btn btn-danger w-100">
          <i class="fas fa-sign-out-alt"></i> Logout
        </button>
      </form>
    </div>

    <!-- Main Content -->
    <div class="content w-100">
      <!-- Time and Notification Bell -->
      <div class="notif-row">
        <div class="header-clock" id="headerClock">
          <!-- JS will set date and time here -->
        </div>
        <div class="notif-bell-box">
          <div class="dropdown">
            <button class="btn position-relative" id="notifBell" data-bs-toggle="dropdown" aria-expanded="false"
              style="color: {{ isset($lowStockItems) && $lowStockItems->count() ? '#c30000' : '#adb5bd' }};">
              <i class="fas fa-bell"></i>
              @if(isset($lowStockItems) && $lowStockItems->count())
                <span class="badge bg-danger">
                  {{ $lowStockItems->count() }}
                </span>
              @endif
            </button>
            @if(isset($lowStockItems) && $lowStockItems->count())
              <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notifBell">
                <li class="dropdown-header">
                  <i class="fas fa-exclamation-triangle me-2"></i>
                  Low Inventory (≤ 5 units)
                </li>
                <li><hr class="dropdown-divider"></li>
                @foreach($lowStockItems as $item)
                  <li class="low-stock-item-row">
                    <b>{{ $item->item_name }}</b>
                    <span class="float-end badge bg-warning text-dark">{{ $item->quantity }}</span>
                  </li>
                @endforeach
              </ul>
            @endif
          </div>
        </div>
      </div>
      @yield('content')
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Simple Clock: 11:53 PM May 21, 2025
    function updateHeaderClock() {
      const clockElem = document.getElementById('headerClock');
      const now = new Date();
      let hour = now.getHours();
      const min = now.getMinutes().toString().padStart(2, '0');
      const ampm = hour >= 12 ? 'PM' : 'AM';
      hour = hour % 12 || 12;
      const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      const dateStr = `${months[now.getMonth()]} ${now.getDate()}, ${now.getFullYear()}`;
      const timeStr = `${hour}:${min} ${ampm} ${dateStr}`;
      clockElem.textContent = timeStr;
    }
    setInterval(updateHeaderClock, 1000);
    window.onload = updateHeaderClock;
  </script>
</body>
</html>
