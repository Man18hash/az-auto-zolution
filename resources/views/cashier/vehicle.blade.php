{{-- resources/views/cashier/vehicle.blade.php --}}
@extends('layouts.cashier')

@section('content')
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <div class="container">
    <h2 class="mb-4">Clients & Vehicle Management</h2>

    {{-- Success toasts --}}
    <div id="client-success" class="alert alert-success d-none">✔ Client added!</div>
    <div id="vehicle-success" class="alert alert-success d-none">✔ Vehicle added!</div>

    {{-- Add Client --}}
    <div class="card mb-4">
    <div class="card-header bg-success text-white">Add New Client</div>
    <div class="card-body">
      <form id="clientForm" method="POST" action="{{ route('cashier.clients.store') }}">
      @csrf
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
        <input type="text" name="name" class="form-control" placeholder="Name" required>
        </div>
        <div class="col-md-3">
        <input type="text" name="address" class="form-control" placeholder="Address">
        </div>
        <div class="col-md-2">
        <input type="text" name="phone" class="form-control" placeholder="Phone">
        </div>
        <div class="col-md-3">
        <input type="email" name="email" class="form-control" placeholder="Email">
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
    <div class="card-header bg-primary text-white">Add New Vehicle</div>
    <div class="card-body">
      <form id="vehicleForm" method="POST" action="{{ route('cashier.vehicles.store') }}">
      @csrf
      <div class="row g-2 align-items-end">
        <div class="col-md-2">
        <select name="client_id" class="form-select">
          <option value="">Select Client</option>
          @foreach($clients as $c)
        <option value="{{ $c->id }}">{{ $c->name }}</option>
      @endforeach
        </select>
        </div>
        <div class="col-md-2">
        <input type="text" name="plate_number" class="form-control" placeholder="Plate #" required>
        </div>
        <div class="col-md-2">
        <input type="text" name="model" class="form-control" placeholder="Model">
        </div>
        <div class="col-md-2">
        <input type="text" name="vin_chasis" class="form-control" placeholder="VIN/Chasis">
        </div>
        <div class="col-md-2">
        <input type="text" name="manufacturer" class="form-control" placeholder="Manufacturer">
        </div>
        <div class="col-md-1">
        <input type="text" name="year" class="form-control" placeholder="Year">
        </div>
        <div class="col-md-1">
        <input type="text" name="color" class="form-control" placeholder="Color">
        </div>
        <div class="col-md-2 mt-2 mt-md-0">
        <input type="text" name="odometer" class="form-control" placeholder="Odometer">
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
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Clients List</span>
      <input id="clientSearch" type="text" class="form-control form-control-sm" placeholder="Search client..."
      style="width: 200px;">
    </div>
    <div class="card-body p-0">
      <table id="clientsTable" class="table mb-0 table-hover">
      <thead>
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
      <tr>
      <td>{{ $c->name }}</td>
      <td>{{ $c->address }}</td>
      <td>{{ $c->phone }}</td>
      <td>{{ $c->email }}</td>
      <td>
        <button class="btn btn-sm btn-warning edit-client" data-id="{{ $c->id }}" data-name="{{ $c->name }}"
        data-address="{{ $c->address }}" data-phone="{{ $c->phone }}" data-email="{{ $c->email }}">
        Edit
        </button>
      </td>

      </tr>
      @endforeach
      </tbody>
      </table>
    </div>
    </div>

    {{-- Vehicles Table --}}
    <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Vehicles List</span>
      <input id="vehicleSearch" type="text" class="form-control form-control-sm" placeholder="Search vehicle..."
      style="width: 200px;">
    </div>
    <div class="card-body p-0">
      <table id="vehiclesTable" class="table mb-0 table-hover">
      <thead>
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
        Edit
        </button>
      </td>


      </tr>
      @endforeach
      </tbody>
      </table>
      <!-- Edit Client Modal -->
      <div class="modal fade" id="editClientModal" tabindex="-1">
      <div class="modal-dialog">
        <form id="editClientForm" class="modal-content">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Edit Client</h5>
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
          <select name="client_id" class="form-select mb-2">
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

    // ✅ Edit Client
    document.querySelectorAll('.edit-client').forEach(btn => {
    btn.addEventListener('click', () => {
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
    let res = await fetch(`/clients/${id}`, {
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
    let res = await fetch(`/vehicles/${id}`, {
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