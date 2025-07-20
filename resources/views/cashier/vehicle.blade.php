{{-- resources/views/cashier/vehicle.blade.php --}}
@extends('layouts.cashier')

@section('content')
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    .card {
    border-radius: 1rem;
    }

    .modal-content {
    border-radius: .8rem;
    }

    .alert {
    border-radius: .6rem;
    }

    #clientsTable tbody tr:hover,
    #vehiclesTable tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
    cursor: pointer;
    }

    .alert {
    transition: all 0.5s ease;
    }

    .fixed-section {
    position: sticky;
    top: 0;
    z-index: 1030;
    background: #f6f8fa;
    padding-bottom: 1rem;
    }

    .scrollable-tables {
    max-height: calc(100vh - 380px);
    /* Adjust based on your header size */
    overflow-y: auto;
    padding-top: 1rem;
    }
  </style>

  <div class="container">
    <h2 class="mb-4 fw-bold text-primary"><i class="bi bi-person-vcard"></i> Clients & Vehicle Management</h2>


    {{-- Success toasts --}}
    <div id="client-success" class="alert alert-success d-none shadow-sm">
    <i class="bi bi-check-circle-fill me-2"></i> Client added!
    </div>

    <div id="vehicle-success" class="alert alert-success d-none">✔ Vehicle added!</div>

    {{-- Add Client --}}
    <div class="card mb-4">
    <div class="card-header bg-success text-white d-flex align-items-center">
      <i class="bi bi-person-plus me-2"></i> <span class="fw-semibold">Add New Client</span>
    </div>

    <div class="card-body">
      <form id="clientForm" method="POST" action="{{ route('cashier.clients.store') }}">
      @csrf
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
        <input type="text" name="name" class="form-control shadow-sm rounded" placeholder="Name" required>
        </div>
        <div class="col-md-3">
        <input type="text" name="address" class="form-control shadow-sm rounded" placeholder="Address">
        </div>
        <div class="col-md-2">
        <input type="text" name="phone" class="form-control shadow-sm rounded" placeholder="Phone">
        </div>
        <div class="col-md-3">
        <input type="email" name="email" class="form-control shadow-sm rounded" placeholder="Email">
        </div>
        <div class="col-md-1">
        <button type="submit" class="btn btn-light w-100">Add</button>
        </div>
      </div>
      </form>
    </div>
    </div>

    {{-- Add Vehicle --}}
    <div class="card mb-4">
    <div class="card-header bg-primary text-white d-flex align-items-center">
      <i class="bi bi-truck me-2"></i> <span class="fw-semibold">Add New Vehicle</span>
    </div>

    <div class="card-body">
      <form id="vehicleForm" method="POST" action="{{ route('cashier.vehicles.store') }}">
      @csrf
      <div class="row g-2 align-items-end">
        <div class="col-md-2">
        <select name="client_id" class="form-select shadow-sm rounded">
          <option value="">Select Client</option>
          @foreach($clients as $c)
        <option value="{{ $c->id }}">{{ $c->name }}</option>
      @endforeach
        </select>
        </div>
        <div class="col-md-2">
        <input type="text" name="plate_number" class="form-control shadow-sm rounded" placeholder="Plate #"
          required>
        </div>
        <div class="col-md-2">
        <input type="text" name="model" class="form-control shadow-sm rounded" placeholder="Model">
        </div>
        <div class="col-md-2">
        <input type="text" name="vin_chasis" class="form-control shadow-sm rounded" placeholder="VIN/Chasis">
        </div>
        <div class="col-md-2">
        <input type="text" name="manufacturer" class="form-control shadow-sm rounded" placeholder="Manufacturer">
        </div>
        <div class="col-md-1">
        <input type="text" name="year" class="form-control shadow-sm rounded" placeholder="Year">
        </div>
        <div class="col-md-1">
        <input type="text" name="color" class="form-control shadow-sm rounded" placeholder="Color">
        </div>
        <div class="col-md-2 mt-2 mt-md-0">
        <input type="text" name="odometer" class="form-control shadow-sm rounded" placeholder="Odometer">
        </div>
        <div class="col-md-1">
        <button type="submit" class="btn btn-light w-100">Add</button>
        </div>
      </div>
      </form>
    </div>
    </div>

    {{-- Clients Table --}}
    <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center sticky-top bg-white"
      style="top: 0; z-index: 1020;">
      <span>Clients List</span>
      <input id="clientSearch" type="text" class="form-control form-control-sm shadow-sm rounded"
      placeholder="🔍 Search client..." style="width: 220px;">
    </div>
    <div class="scrollable-tables card-body p-0">
      <table id="clientsTable" class="table mb-0 table-hover">
      <thead class="table-light">
        <tr>
        <th>Name</th>
        <th>Address</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($clients as $c)
      <tr class="client-row" data-id="{{ $c->id }}">
      <td>{{ $c->name }}</td>
      <td>{{ $c->address }}</td>
      <td>{{ $c->phone }}</td>
      <td>{{ $c->email }}</td>
      <td>
        <button class="btn btn-sm btn-warning edit-client" data-id="{{ $c->id }}" data-name="{{ $c->name }}"
        data-address="{{ $c->address }}" data-phone="{{ $c->phone }}" data-email="{{ $c->email }}">
        <i class="bi bi-pencil-square"></i>
        </button>
        <button class="btn btn-sm btn-danger delete-client" data-id="{{ $c->id }}">
        <i class="bi bi-trash"></i>
        </button>
      </td>
      </tr>
      @endforeach
      </tbody>
      </table>
    </div>
    </div>


    <br>
    {{-- Vehicles Table --}}
    <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center sticky-top bg-white"
      style="top: 0; z-index: 1020;">
      <span>Vehicles List</span>
      <input id="vehicleSearch" type="text" class="form-control form-control-sm" placeholder="Search vehicle..."
      style="width: 200px;">
    </div>
    <div class="scrollable-tables card-body p-0">
      <table id="vehiclesTable" class="table mb-0 table-hover">
      <thead class="table-light">
        <tr>
        <th>Client</th>
        <th>Plate #</th>
        <th>Model</th>
        <th>VIN/Chasis</th>
        <th>Manufacturer</th>
        <th>Year</th>
        <th>Color</th>
        <th>Odometer</th>
        <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($vehicles as $v)
      <tr>
      <td>{{ optional($v->client)->name ?? '-' }}</td>
      <td>{{ $v->plate_number }}</td>
      <td>{{ $v->model }}</td>
      <td>{{ $v->vin_chasis }}</td>
      <td>{{ $v->manufacturer ?? '-' }}</td>
      <td>{{ $v->year ?? '-' }}</td>
      <td>{{ $v->color ?? '-' }}</td>
      <td>{{ $v->odometer }}</td>
      <td>
        <button class="btn btn-sm btn-warning edit-vehicle" data-id="{{ $v->id }}"
        data-client_id="{{ $v->client_id }}" data-plate_number="{{ $v->plate_number }}"
        data-model="{{ $v->model }}" data-vin_chasis="{{ $v->vin_chasis }}"
        data-manufacturer="{{ $v->manufacturer }}" data-year="{{ $v->year }}" data-color="{{ $v->color }}"
        data-odometer="{{ $v->odometer }}">
        <i class="bi bi-pencil-square"></i>
        </button>
        <button class="btn btn-sm btn-danger delete-vehicle" data-id="{{ $v->id }}">
        <i class="bi bi-trash"></i>
        </button>
      </td>
      </tr>
      @endforeach
      </tbody>
      </table>
    </div>
    </div>


    <!-- View Client Details Modal -->
    <div class="modal fade" id="viewClientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-person-lines-fill me-2"></i> Client Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="clientInfo"></div>
        <h6 class="mt-4">Vehicles:</h6>
        <ul id="clientVehicles" class="list-group list-group-flush"></ul>
      </div>
      </div>
    </div>
    </div>

    <!-- Edit Client Modal -->
    <div class="modal fade" id="editClientModal" tabindex="-1">
    <div class="modal-dialog">
      <form id="editClientForm" class="modal-content">
      @csrf
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i> Edit Client</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id">
        <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
        <input type="text" name="address" class="form-control mb-2" placeholder="Address">
        <input type="text" name="phone" class="form-control mb-2" placeholder="Phone">
        <input type="email" name="email" class="form-control mb-2" placeholder="Email">
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
      </form>
    </div>
    </div>

    <!-- Edit Vehicle Modal -->
    <div class="modal fade" id="editVehicleModal" tabindex="-1">
    <div class="modal-dialog">
      <form id="editVehicleForm" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Edit Vehicle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id">
        <select name="client_id" class="form-select shadow-sm rounded">
        <option value="">Select Client</option>
        @foreach($clients as $c)
      <option value="{{ $c->id }}">{{ $c->name }}</option>
      @endforeach
        </select>
        <input type="text" name="plate_number" class="form-control mb-2" placeholder="Plate #" required>
        <input type="text" name="model" class="form-control mb-2" placeholder="Model">
        <input type="text" name="vin_chasis" class="form-control mb-2" placeholder="VIN/Chasis">
        <input type="text" name="manufacturer" class="form-control mb-2" placeholder="Manufacturer">
        <input type="text" name="year" class="form-control mb-2" placeholder="Year">
        <input type="text" name="color" class="form-control mb-2" placeholder="Color">
        <input type="text" name="odometer" class="form-control mb-2" placeholder="Odometer">
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
      </form>
    </div>
    </div>

  </div>
  </div>
  </div>

  <script>
    const token = document.querySelector('meta[name="csrf-token"]').content;

    async function ajaxForm(formId, tableId, successAlertId, rowBuilder) {
    const form = document.getElementById(formId);
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const data = Object.fromEntries(new FormData(form).entries());
      form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
      try {
      const res = await fetch(form.action, {
        method: 'POST',
        headers: {
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      });
      if (res.ok) {
        const obj = await res.json();
        const tbody = document.querySelector(`#${tableId} tbody`);
        const tr = document.createElement('tr');
        tr.innerHTML = rowBuilder(obj);
        tbody.prepend(tr);
        const alert = document.getElementById(successAlertId);
        alert.classList.remove('d-none');
        setTimeout(() => alert.classList.add('d-none'), 3000);
        form.reset();
      } else if (res.status === 422) {
        const errors = (await res.json()).errors;
        for (let [field, msgs] of Object.entries(errors)) {
        const input = form.querySelector(`[name="${field}"]`);
        if (input) {
          input.classList.add('is-invalid');
          const fb = document.createElement('small');
          fb.className = 'text-danger';
          fb.textContent = msgs.join(' ');
          input.parentNode.appendChild(fb);
        }
        }
      } else {
        alert('Server error.');
      }
      } catch {
      alert('Network error.');
      }
    });
    }

    ajaxForm(
    'clientForm',
    'clientsTable',
    'client-success',
    client => {
      const vehicleSelect = document.querySelector('#vehicleForm select[name="client_id"]');
      if (vehicleSelect) {
      const opt = document.createElement('option');
      opt.value = client.id;
      opt.textContent = client.name;
      vehicleSelect.appendChild(opt);
      }
      return `
      <td>${client.name}</td>
      <td>${client.address || ''}</td>
      <td>${client.phone || ''}</td>
      <td>${client.email || ''}</td>`;
    }
    );

    ajaxForm(
    'vehicleForm',
    'vehiclesTable',
    'vehicle-success',
    v => `
      <td>${v.client ? v.client.name : '-'}</td>
      <td>${v.plate_number}</td>
      <td>${v.model || ''}</td>
      <td>${v.vin_chasis || ''}</td>
      <td>${v.manufacturer || '-'}</td>
      <td>${v.year || '-'}</td>
      <td>${v.color || '-'}</td>
      <td>${v.odometer || ''}</td>`
    );

    document.getElementById('clientSearch').addEventListener('input', function () {
    let value = this.value.toLowerCase();
    document.querySelectorAll('#clientsTable tbody tr').forEach(tr => {
      tr.style.display = [...tr.children].some(td => td.textContent.toLowerCase().includes(value)) ? '' : 'none';
    });
    });

    document.getElementById('vehicleSearch').addEventListener('input', function () {
    let value = this.value.toLowerCase();
    document.querySelectorAll('#vehiclesTable tbody tr').forEach(tr => {
      tr.style.display = [...tr.children].some(td => td.textContent.toLowerCase().includes(value)) ? '' : 'none';
    });
    });

    // Show client info on row click
    document.querySelectorAll('.client-row').forEach(row => {
    row.addEventListener('click', async () => {
      const clientId = row.dataset.id;

      try {
      let res = await fetch(`/cashier/clients/${clientId}/vehicles`, {
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
      });

      if (res.ok) {
        let data = await res.json();
        // Update modal content
        document.getElementById('clientInfo').innerHTML = `
      <div><strong>Name:</strong> ${data.client.name}</div>
      <div><strong>Address:</strong> ${data.client.address || '-'}</div>
      <div><strong>Phone:</strong> ${data.client.phone || '-'}</div>
      <div><strong>Email:</strong> ${data.client.email || '-'}</div>
      `;

        let list = document.getElementById('clientVehicles');
        list.innerHTML = data.vehicles.length
        ? data.vehicles.map(v => `
      <li class="list-group-item">
      <strong>${v.plate_number}</strong> - ${v.model || ''} (${v.year || ''})
      </li>
      `).join('')
        : '<li class="list-group-item text-muted">No vehicles</li>';

        new bootstrap.Modal(document.getElementById('viewClientModal')).show();
      } else {
        alert('Could not load client data.');
      }
      } catch {
      alert('Network error.');
      }
    });
    });


    // ✅ Delete Client
    document.querySelectorAll('.delete-client').forEach(btn => {
    btn.addEventListener('click', async () => {
      e.stopPropagation();
      if (confirm('Are you sure you want to delete this client?')) {
      let res = await fetch(`/cashier/clients/${btn.dataset.id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
      });

      if (res.ok) {
        btn.closest('tr').remove();
      } else {
        alert('Failed to delete client.');
      }
      }
    });
    });

    // ✅ Delete Vehicle
    document.querySelectorAll('.delete-vehicle').forEach(btn => {
    btn.addEventListener('click', async () => {
      if (confirm('Are you sure you want to delete this vehicle?')) {
      let res = await fetch(`/cashier/vehicles/${btn.dataset.id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
      });

      if (res.ok) {
        btn.closest('tr').remove();
      } else {
        alert('Failed to delete vehicle.');
      }
      }
    });
    });


    // ✅ Edit Client
    document.querySelectorAll('.edit-client').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation(); // 🚀 prevents the row click from firing
      const modal = document.getElementById('editClientModal');
      modal.querySelector('[name=id]').value = btn.dataset.id;
      modal.querySelector('[name=name]').value = btn.dataset.name;
      modal.querySelector('[name=address]').value = btn.dataset.address;
      modal.querySelector('[name=phone]').value = btn.dataset.phone;
      modal.querySelector('[name=email]').value = btn.dataset.email;
      new bootstrap.Modal(modal).show();
    });
    });


    document.getElementById('editClientForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    let form = this;
    let id = form.querySelector('[name=id]').value;
    let data = Object.fromEntries(new FormData(form).entries());
    let res = await fetch(`/cashier/clients/${id}`, {
      method: 'PUT',
      headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    if (res.ok) {
      location.reload();
    }
    });

    // ✅ Edit Vehicle
    document.querySelectorAll('.edit-vehicle').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = document.getElementById('editVehicleModal');
      modal.querySelector('[name=id]').value = btn.dataset.id;
      modal.querySelector('[name=client_id]').value = btn.dataset.client_id;
      modal.querySelector('[name=plate_number]').value = btn.dataset.plate_number;
      modal.querySelector('[name=model]').value = btn.dataset.model;
      modal.querySelector('[name=vin_chasis]').value = btn.dataset.vin_chasis;
      modal.querySelector('[name=manufacturer]').value = btn.dataset.manufacturer;
      modal.querySelector('[name=year]').value = btn.dataset.year;
      modal.querySelector('[name=color]').value = btn.dataset.color;
      modal.querySelector('[name=odometer]').value = btn.dataset.odometer;
      new bootstrap.Modal(modal).show();
    });
    });

    document.getElementById('editVehicleForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    let form = this;
    let id = form.querySelector('[name=id]').value;
    let data = Object.fromEntries(new FormData(form).entries());
    let res = await fetch(`/cashier/vehicles/${id}`, {
      method: 'PUT',
      headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    if (res.ok) {
      location.reload();
    }
    });
  </script>

@endsection