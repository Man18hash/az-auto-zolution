{{-- resources/views/cashier/inventory.blade.php --}}
@extends('layouts.cashier')

@section('title', 'Inventory Management')

@section('content')
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <div class="container mt-4">
    <h2 class="mb-4">Inventory Management</h2>

    {{-- Success Toast --}}
    <div id="inventory-success" class="alert alert-success d-none"></div>

    {{-- ➤ Add/Update Inventory --}}
    <div class="card mb-5">
    <div class="card-header bg-success text-white" id="form-header">
      Add New Inventory Item
    </div>
    <div class="card-body">
      <form id="inventoryForm" action="{{ route('cashier.inventory.store') }}" method="POST">
      @csrf
      <input type="hidden" id="inventory_id" name="inventory_id">
      <div class="row row-cols-lg-auto g-2 align-items-end">
        <div class="col">
        <input type="text" id="item_name" name="item_name" class="form-control" placeholder="Item Name" required>
        </div>
        <div class="col">
        <input type="text" id="part_number" name="part_number" class="form-control" placeholder="Part Number"
          required>
        </div>
        <div class="col">
        <input type="number" id="quantity" name="quantity" class="form-control" placeholder="Quantity" required>
        </div>
        <div class="col">
        <input type="number" step="0.01" id="selling" name="selling" class="form-control"
          placeholder="Selling Price" required>
        </div>
        <div class="col">
        <input type="number" step="0.01" id="acquisition_price" name="acquisition_price" class="form-control"
          placeholder="Acquisition Price">
        </div>
        <div class="col">
        <input type="text" id="supplier" name="supplier" class="form-control" placeholder="Supplier">
        </div>
        <div class="col">
        <button type="submit" class="btn btn-light" id="submitBtn">Add</button>

        </div>
      </div>
      </form>
    </div>
    </div>

    {{-- ➤ Inventory List --}}
    <div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <span>Inventory List</span>
      <div class="d-flex">
      <input id="searchInput" type="text" class="form-control form-control-sm me-2" placeholder="Search inventory...">
      <button id="searchBtn" class="btn btn-light btn-sm">Search</button>
      </div>
    </div>
    <div class="card-body p-0">
      <table id="inventoryTable" class="table mb-0">
      <thead class="table-light">
        <tr>
        <th>#</th>
        <th>Item Name</th>
        <th>Part #</th>
        <th>Qty</th>
        <th>Selling (₱)</th>
        <th>Acquisition (₱)</th>
        <th>Supplier</th>
        <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($inventories as $inv)
        <tr data-id="{{ $inv->id }}">
        <td>{{ $loop->iteration }}</td>
        <td>{{ $inv->item_name }}</td>
        <td>{{ $inv->part_number }}</td>
        <td>{{ $inv->quantity }}</td>
        <td>{{ number_format($inv->selling, 2) }}</td>
        <td>
        {{ $inv->acquisition_price
      ? number_format($inv->acquisition_price, 2)
      : '-' }}
        </td>
        <td>{{ $inv->supplier ?? '-' }}</td>
        <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-info edit-btn" data-id="{{ $inv->id }}"
        data-item_name="{{ $inv->item_name }}" data-part_number="{{ $inv->part_number }}"
        data-quantity="{{ $inv->quantity }}" data-selling="{{ $inv->selling }}"
        data-acquisition_price="{{ $inv->acquisition_price }}" data-supplier="{{ $inv->supplier }}">
        Edit
        </button>
        <form class="d-inline" action="{{ route('cashier.inventory.destroy', $inv) }}" method="POST"
        onsubmit="return confirm('Delete this item?')">
        @csrf @method('DELETE')
        </form>
        </td>
        </tr>
      @endforeach
        @if($inventories->isEmpty())
      <tr>
      <td colspan="8" class="text-center text-muted py-3">
        No inventory items yet.
      </td>
      </tr>
      @endif
      </tbody>
      </table>
    </div>
    </div>
  </div>

  <!-- Edit Inventory Modal -->
  <div class="modal fade" id="editInventoryModal" tabindex="-1" aria-labelledby="editInventoryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
      <h5 class="modal-title" id="editInventoryModalLabel">Edit Inventory Item</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <form id="editInventoryForm">
        <input type="hidden" id="edit_inventory_id">
        <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Item Name</label>
          <input type="text" id="edit_item_name" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Part Number</label>
          <input type="text" id="edit_part_number" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Quantity</label>
          <input type="number" id="edit_quantity" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Selling Price</label>
          <input type="number" step="0.01" id="edit_selling" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Acquisition Price</label>
          <input type="number" step="0.01" id="edit_acquisition_price" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Supplier</label>
          <input type="text" id="edit_supplier" class="form-control">
        </div>
        </div>
      </form>
      </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      <button type="button" id="saveEditBtn" class="btn btn-primary">Save Changes</button>
      </div>
    </div>
    </div>
  </div>


  {{-- AJAX + Edit + Search Scripts --}}
  <script>
    const token = document.querySelector('meta[name="csrf-token"]').content;
    let editingId = null;

    // --- ADD/UPDATE Inventory (AJAX) ---
    document.getElementById('inventoryForm').addEventListener('submit', async e => {
    e.preventDefault();
    const form = e.target;
    const url = form.action;
    const method = 'POST';

    const data = Object.fromEntries(new FormData(form));
    form.querySelectorAll('.is-invalid').forEach(i => i.classList.remove('is-invalid'));

    try {
      const res = await fetch(url, {
      method,
      headers: {
        'X-CSRF-TOKEN': token,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(data)
      });

      if (res.ok) {
      const inv = await res.json();
      const alert = document.getElementById('inventory-success');
      alert.textContent = '✔ Inventory item added!';
      alert.classList.remove('d-none');
      setTimeout(() => alert.classList.add('d-none'), 2500);

      window.location.reload();
      form.reset();
      } else if (res.status === 422) {
      const errs = (await res.json()).errors;
      Object.entries(errs).forEach(([field, msgs]) => {
        const inp = form.querySelector(`[name="${field}"]`);
        if (inp) {
        inp.classList.add('is-invalid');
        const fb = document.createElement('div');
        fb.className = 'invalid-feedback';
        fb.textContent = msgs.join(' ');
        inp.parentNode.appendChild(fb);
        }
      });
      } else {
      alert('Server error.');
      }
    } catch {
      alert('Network error.');
    }
    });


    document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.getElementById('edit_inventory_id').value = this.dataset.id;
      document.getElementById('edit_item_name').value = this.dataset.item_name;
      document.getElementById('edit_part_number').value = this.dataset.part_number;
      document.getElementById('edit_quantity').value = this.dataset.quantity;
      document.getElementById('edit_selling').value = this.dataset.selling;
      document.getElementById('edit_acquisition_price').value = this.dataset.acquisition_price;
      document.getElementById('edit_supplier').value = this.dataset.supplier;

      let modal = new bootstrap.Modal(document.getElementById('editInventoryModal'));
      modal.show();
    });
    });




    // In-page search
    document.getElementById('searchBtn').addEventListener('click', () => {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    document.querySelectorAll('#inventoryTable tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
    });

    document.getElementById('saveEditBtn').addEventListener('click', async () => {
    const id = document.getElementById('edit_inventory_id').value;
    const data = {
      item_name: document.getElementById('edit_item_name').value,
      part_number: document.getElementById('edit_part_number').value,
      quantity: document.getElementById('edit_quantity').value,
      selling: document.getElementById('edit_selling').value,
      acquisition_price: document.getElementById('edit_acquisition_price').value,
      supplier: document.getElementById('edit_supplier').value,
    };

    try {
      const res = await fetch(`/cashier/inventory/${id}`, {
      method: 'PUT',
      headers: {
        'X-CSRF-TOKEN': token,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(data)
      });
      if (res.ok) {
      bootstrap.Modal.getInstance(document.getElementById('editInventoryModal')).hide();
      alert('✔ Inventory updated!');
      window.location.reload();
      } else if (res.status === 422) {
      alert('Validation failed. Check fields.');
      } else {
      alert('Server error.');
      }
    } catch {
      alert('Network error.');
    }
    });

  </script>
@endsection