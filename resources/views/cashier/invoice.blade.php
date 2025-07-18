@extends('layouts.cashier')

@section('title', isset($invoice) ? 'Edit Invoice' : 'New Invoice')

@section('content')
@php
  $filteredUnpaid = $history->where('source_type', 'invoicing')
    ->where('created_at', '>=', now()->subHours(48))
    ->where('status', 'unpaid');

  $filteredPaid = $history->where('source_type', 'invoicing')
    ->where('created_at', '>=', now()->subHours(48))
    ->where('status', 'paid');
@endphp

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container { width: 100% !important; }
.select2-dropdown { z-index: 10060; }
.btn-source-type { min-width: 120px; margin-left: 4px;}

.select2-container--default .select2-selection--single {
  width: 300px !important; /* adjust as needed */
}
.select2-container {
  width: 300px !important; /* ensures container matches */
}
.select2-container--open {
  z-index: 100999 !important;
}

.select2-dropdown {
  z-index: 100999 !important;
}
</style>

<div class="container mt-4 mb-3">
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#invoiceModal" id="btnCreateInvoice">
    <i class="bi bi-plus"></i> Create Invoice
  </button>
</div>

{{-- Invoice Modal --}}
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 95vw;">

    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title mx-auto" id="invoiceModalLabel">{{ isset($invoice) ? 'Edit Invoice' : 'Create Invoice' }}</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        @if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif


        <form action="{{ isset($invoice) ? route('cashier.invoice.update', $invoice->id) : route('cashier.invoice.store') }}"
              method="POST" id="invoiceForm" autocomplete="off">
          @csrf
          @if(isset($invoice)) @method('PUT') @endif

          {{-- Header Details --}}
          <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
              Client & Vehicle Details
            </div>
            <div class="card-body p-3">
              <div class="row g-3 mb-3">
                <div class="col-md-3">
                  <label class="form-label fw-bold">Client</label>
                  <select name="client_id" id="client_id" class="form-select">
                    <option value="">— walk‐in or choose —</option>
                    @foreach($clients as $c)
                      <option value="{{ $c->id }}"
                        {{ old('client_id', $invoice->client_id ?? '') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label fw-bold">Manual Customer Name</label>
                  <input type="text" name="customer_name" class="form-control" placeholder="Enter walk-in name"
                         value="{{ old('customer_name', $invoice->customer_name ?? '') }}">
                </div>
                <div class="col-md-3">
                  <label class="form-label fw-bold">Vehicle</label>
                  <select name="vehicle_id" id="vehicle_id" class="form-select">
                    <option value="">— walk-in or choose —</option>
                    @foreach($vehicles as $v)
                      <option value="{{ $v->id }}"
                        data-plate="{{ $v->plate_number }}"
                        data-model="{{ $v->model }}"
                        data-year="{{ $v->year }}"
                        data-color="{{ $v->color }}"
                        data-odometer="{{ $v->odometer }}"
                        {{ old('vehicle_id', $invoice->vehicle_id ?? '') == $v->id ? 'selected' : '' }}>
                        {{ $v->plate_number }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label fw-bold">Manual Vehicle Name</label>
                  <input type="text" name="vehicle_name" class="form-control" placeholder="Enter vehicle details"
                         value="{{ old('vehicle_name', $invoice->vehicle_name ?? '') }}">
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-2">
                  <label class="form-label fw-bold">Plate</label>
                  <input type="text" name="plate" id="plate" class="form-control"
                         value="{{ old('plate', isset($invoice->vehicle) ? $invoice->vehicle->plate_number : '') }}">
                </div>
                <div class="col-md-2">
                  <label class="form-label fw-bold">Model</label>
                  <input type="text" name="model" id="model" class="form-control"
                         value="{{ old('model', isset($invoice->vehicle) ? $invoice->vehicle->model : '') }}">
                </div>
                <div class="col-md-2">
                  <label class="form-label fw-bold">Year</label>
                  <input type="text" name="year" id="year" class="form-control"
                         value="{{ old('year', isset($invoice->vehicle) ? $invoice->vehicle->year : '') }}">
                </div>
                <div class="col-md-2">
                  <label class="form-label fw-bold">Color</label>
                  <input type="text" name="color" id="color" class="form-control"
                         value="{{ old('color', isset($invoice->vehicle) ? $invoice->vehicle->color : '') }}">
                </div>
                <div class="col-md-2">
                  <label class="form-label fw-bold">Odometer</label>
                  <input type="text" name="odometer" id="odometer" class="form-control"
                         value="{{ old('odometer', isset($invoice->vehicle) ? $invoice->vehicle->odometer : '') }}">
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-2">
                  <label class="form-label fw-bold">Payment Type</label>
                  <select name="payment_type" class="form-select" style="background:#e6ffe3">
                    <option value="cash"      @selected(old('payment_type', $invoice->payment_type ?? '')=='cash')>Cash</option>
                    <option value="debit"     @selected(old('payment_type', $invoice->payment_type ?? '')=='debit')>Debit</option>
                    <option value="credit"    @selected(old('payment_type', $invoice->payment_type ?? '')=='credit')>Credit</option>
                    <option value="non_cash"  @selected(old('payment_type', $invoice->payment_type ?? '')=='non_cash')>Non Cash</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label fw-bold">Status</label>
                  <select name="status" class="form-select">
                    <option value="unpaid"   @selected(old('status', $invoice->status ?? '') == 'unpaid')>Unpaid</option>
                    <option value="paid"     @selected(old('status', $invoice->status ?? '') == 'paid')>Paid</option>
                    <option value="cancelled"@selected(old('status', $invoice->status ?? '') == 'cancelled')>Cancelled</option>
                    <option value="voided"   @selected(old('status', $invoice->status ?? '') == 'voided')>Voided</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label fw-bold">Service Status</label>
                  <select name="service_status" class="form-select">
                    <option value="pending"      @selected(old('service_status', $invoice->service_status ?? '') == 'pending')>Pending</option>
                    <option value="in_progress"  @selected(old('service_status', $invoice->service_status ?? '') == 'in_progress')>In Progress</option>
                    <option value="done"         @selected(old('service_status', $invoice->service_status ?? '') == 'done')>Done</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label fw-bold">Source Type</label>
                  <input type="text" class="form-control" value="{{ old('source_type', $invoice->source_type ?? 'invoicing') }}" readonly>
                  <input type="hidden" name="source_type" value="{{ old('source_type', $invoice->source_type ?? 'invoicing') }}">
                </div>
                <div class="col-md-2">
                  <label class="form-label fw-bold">Number</label>
                  <input type="number" name="number" class="form-control"
                         value="{{ old('number', $invoice->number ?? '') }}">
                </div>
                <div class="col-md-2">
                  <label class="form-label fw-bold">Invoice No</label>
                  <input type="text" name="invoice_no" class="form-control" placeholder="INV-2025-001"
                         value="{{ old('invoice_no', $invoice->invoice_no ?? '') }}" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-bold">Address</label>
                  <input type="text" name="address" class="form-control"
                         value="{{ old('address', $invoice->address ?? '') }}">
                </div>
              </div>
            </div> {{-- end card-body --}}
          </div> {{-- end card --}}


          {{-- Items --}}
<div class="card mb-4 shadow-sm">
  <div class="card-header bg-success text-white">
    Items
  </div>
  <div class="card-body p-3">
    <table class="table table-bordered" id="items-table">
      <thead>
  <tr>
    <th style="min-width:250px;">Item</th>
    <th>Qty</th>
    <th>Acq. ₱</th>
    <th>Price ₱</th>
    <th>Discounted ₱</th>
    <th>Total ₱</th>
    <th></th>
  </tr>
</thead>

      <tbody></tbody>
      <tfoot>
        <tr>
          <td colspan="5" class="text-end">
            <button type="button" id="add-item" class="btn btn-sm btn-success">+ Add Item</button>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>


          {{-- Jobs --}}
<div class="card mb-4 shadow-sm">
  <div class="card-header bg-info text-white">
    Jobs
  </div>
  <div class="card-body p-3">
    <table class="table table-bordered" id="jobs-table">
      <thead>
        <tr>
          <th>Description</th>
          <th>Technician</th>
          <th>Total ₱</th>
          <th></th>
        </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
        <tr>
          <td colspan="4" class="text-end">
            <button type="button" id="add-job" class="btn btn-sm btn-success">+ Add Job</button>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>


          {{-- Totals --}}
          <div class="row g-3 mb-4">
            <div class="col-md-3">
              <label class="form-label fw-bold">Subtotal</label>
              <input type="number" step="0.01" name="subtotal" class="form-control" readonly>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold">Total Discount</label>
              
              <input type="number" name="total_discount" class="form-control" value="{{ old('total_discount', $invoice->total_discount ?? 0) }}">


            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold">VAT (12%)</label>
              <input type="number" step="0.01" name="vat_amount" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold">Grand Total</label>
              <input type="number" step="0.01" name="grand_total" class="form-control" readonly>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">{{ isset($invoice) ? 'Update Invoice' : 'Save Invoice' }}</button>
            <a href="{{ route('cashier.invoice.index') }}" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- UNPAID INVOICES --}}
<h3 class="mt-5 fw-bold"><i class="bi bi-exclamation-circle text-warning"></i> Recent Unpaid Invoices</h3>
@if($filteredUnpaid->isEmpty())
  <div class="alert alert-info">No unpaid invoices in the past 48 hours.</div>
@else
  <div class="table-responsive shadow-sm rounded">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>Customer</th>
          <th>Vehicle</th>
          <th>Payment</th>
          <th>Service</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($filteredUnpaid as $h)
          <tr>
            <td>{{ $h->client->name ?? $h->customer_name }}</td>
            <td>{{ $h->vehicle->plate_number ?? $h->vehicle_name }}</td>
            <td>
              <span class="badge bg-{{ $h->payment_type === 'cash' ? 'success' : ($h->payment_type === 'credit' ? 'primary' : 'secondary') }}">
                {{ ucfirst(str_replace('_', ' ', $h->payment_type)) }}
              </span>
            </td>
            <td>
              <form action="{{ route('cashier.invoice.update', $h->id) }}" method="POST" class="d-inline">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="unpaid">
                <select name="service_status" class="form-select form-select-sm"
                        onchange="this.form.submit()" data-bs-toggle="tooltip" title="Change Service Status">
                  <option value="pending"      {{ $h->service_status == 'pending' ? 'selected' : '' }}>Pending</option>
                  <option value="in_progress"  {{ $h->service_status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                  <option value="done"         {{ $h->service_status == 'done' ? 'selected' : '' }}>Done</option>
                </select>
              </form>
            </td>
            <td><span class="badge bg-warning text-dark">Unpaid</span></td>
            <td class="text-end">
              <div class="btn-group">
                <a href="{{ route('cashier.invoice.view', $h->id) }}"
                   class="btn btn-sm btn-outline-info"
                   data-bs-toggle="tooltip" title="Print Invoice">
                  <i class="bi bi-printer"></i>
                </a>
                <button class="btn btn-sm btn-outline-primary btn-edit-invoice"
                        data-id="{{ $h->id }}"
                        data-url="{{ route('cashier.invoice.edit', $h->id) }}"
                        data-bs-toggle="tooltip" title="Edit Invoice">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <form action="{{ route('cashier.invoice.update', $h->id) }}" method="POST" class="d-inline">
                  @csrf @method('PUT')
                  <input type="hidden" name="status" value="paid">
                  <input type="hidden" name="service_status" value="{{ $h->service_status }}">
                  <button type="submit" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Mark as Paid">
                    <i class="bi bi-check2-circle"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif

{{-- PAID INVOICES --}}
<h3 class="mt-5 fw-bold"><i class="bi bi-check-circle text-success"></i> Recent Paid Invoices</h3>
@if($filteredPaid->isEmpty())
  <div class="alert alert-info">No paid invoices in the past 48 hours.</div>
@else
  <div class="table-responsive shadow-sm rounded">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>Customer</th>
          <th>Vehicle</th>
          <th>Payment</th>
          <th>Service</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($filteredPaid as $h)
          <tr>
            <td>{{ $h->client->name ?? $h->customer_name }}</td>
            <td>{{ $h->vehicle->plate_number ?? $h->vehicle_name }}</td>
            <td>
              <span class="badge bg-{{ $h->payment_type === 'cash' ? 'success' : ($h->payment_type === 'credit' ? 'primary' : 'secondary') }}">
                {{ ucfirst(str_replace('_', ' ', $h->payment_type)) }}
              </span>
            </td>
            <td><span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $h->service_status)) }}</span></td>
            <td><span class="badge bg-success">Paid</span></td>
            <td class="text-end">
              <a href="{{ route('cashier.invoice.view', $h->id) }}"
                 class="btn btn-sm btn-outline-info"
                 data-bs-toggle="tooltip" title="View Invoice">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif

{{-- JS Assets --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const parts = @json($parts);
const technicians = @json($technicians);
const clients = @json($clients);  // Assuming clients data is available
  const vehicles = @json($vehicles);  // Assuming vehicles data is available



  // Client and Vehicle Search
  $('#client_id').select2({
  data: clients.map(client => ({ id: client.id, text: client.name })),
  placeholder: '-- search client --',
  allowClear: true,
  dropdownParent: $('#invoiceModal .modal-content')  // 👈 important for modals
});


  $('#vehicle_id').select2({
    placeholder: '-- search vehicle --',
    allowClear: true
  });

  /// When a client is selected, fetch and populate vehicle options with data attributes
$('#client_id').on('change', function() {
  const clientId = $(this).val();
  const filteredVehicles = vehicles.filter(vehicle => vehicle.client_id == clientId);

  // Clear and add default option
  $('#vehicle_id').empty().append(`<option value="">— walk-in or choose —</option>`);

  // Append filtered vehicles as options with data attributes
  filteredVehicles.forEach(vehicle => {
    $('#vehicle_id').append(`
      <option value="${vehicle.id}"
        data-plate="${vehicle.plate_number || ''}"
        data-model="${vehicle.model || ''}"
        data-year="${vehicle.year || ''}"
        data-color="${vehicle.color || ''}"
        data-odometer="${vehicle.odometer || ''}">
        ${vehicle.plate_number}
      </option>
    `);
  });

  // Re-initialize select2 after appending options
  $('#vehicle_id').select2({
    placeholder: '-- search vehicle --',
    allowClear: true
  });

  // ─── New: auto-populate Number & Address ───
  const client = clients.find(c => String(c.id) === clientId);
  $('input[name="number"]').val(client?.number ?? '');
  $('input[name="address"]').val(client?.address ?? '');
});
// VEHICLE DETAILS AUTOFILL
$('#vehicle_id').on('change', function() {
  let selected = $(this).find(':selected');
  $('#plate').val(selected.data('plate') || '');
  $('#model').val(selected.data('model') || '');
  $('#year').val(selected.data('year') || '');
  $('#color').val(selected.data('color') || '');
  $('#odometer').val(selected.data('odometer') || '');
});

// Item row with select2 and correct price autopopulate!
// ─── Item row with Manual‐toggle + select2 autopopulate ───
// ─── Item row with Manual-popup + Select2 autopopulate ───
function addItemRow(data = null) {
  const idx = $('#items-table tbody tr').length;
  const partId = data?.part_id || '';
  const qty = data?.quantity || 1;
  const price = data?.original_price || '';


  const lineTotal = (qty && price) ? (qty * price).toFixed(2) : '0.00';

  const row = $(`<tr>
    <td>
      <div class="input-group">
        <select name="items[${idx}][part_id]"
                class="form-select form-select-sm part-select"
                style="width:100%">
          <option value="">-- search part --</option>
        </select>
        <button type="button" class="btn btn-warning btn-sm manual-toggle">Manual</button>
      </div>
      <div class="manual-fields mt-2 d-none">
  <input type="text" name="items[${idx}][manual_part_name]" class="form-control form-control-sm mb-1" placeholder="Part Name">
  <input type="text" name="items[${idx}][manual_serial_number]" class="form-control form-control-sm mb-1" placeholder="Serial #">
  <input type="number" name="items[${idx}][manual_acquisition_price]" class="form-control form-control-sm mb-1" placeholder="Acquisition ₱">
  <input type="number" name="items[${idx}][manual_selling_price]" class="form-control form-control-sm mb-1" placeholder="Selling ₱">
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-sm btn-secondary cancel-manual">Cancel</button>
    <button type="button" class="btn btn-sm btn-success save-manual">Save</button>
  </div>
</div>

    </td>
    <td><input name="items[${idx}][quantity]" type="number" class="form-control form-control-sm" value="${qty}"></td>
    <td><input name="items[${idx}][acquisition_price]" type="number" step="0.01" class="form-control form-control-sm acquisition-price" readonly></td>
    <td><input name="items[${idx}][price]" type="number" step="0.01" class="form-control form-control-sm" value="${price}"></td>
    <input type="hidden" name="items[${idx}][original_price]" value="${price}">

    <td><input name="items[${idx}][discounted_price]" type="number" step="0.01" class="form-control form-control-sm" value="${data?.discounted_price || ''}"></td>
    <td class="col-line-total">${lineTotal}</td>
    <td><button type="button" class="btn btn-sm btn-danger remove-btn">✕</button></td>
  </tr>`);

  // Setup Select2
const select2Data = [
    { id: '', text: '-- search part --', price: 0, acquisition_price: 0 },
    ...parts.map(p => ({
      id: p.id,
      text: `[${p.part_number}] ${p.item_name} – Stock: ${p.quantity}`,
      price: Number(p.selling),
      acquisition_price: Number(p.acquisition_price),
      disabled: p.quantity <= 0
    }))
  ];

  const $sel = row.find('.part-select').select2({
    data: select2Data,
    placeholder: '-- search part --',
    allowClear: true,
    width: 'resolve',
    dropdownParent: $('#invoiceModal .modal-content'),
    templateResult: function (data) {
      if (!data.id) return data.text;
      const p = parts.find(p => p.id == data.id);
      return $('<span>', {
        text: data.text,
        css: { color: p && p.quantity <= 0 ? 'red' : 'inherit' }
      });
    }
  });


  // pre-select on edit
  if (partId) {
    $sel.val(partId).trigger('change');
    const pre = select2Data.find(o => o.id == partId);
    if (pre) {
      row.find('[name$="[price]"]').val(pre.price.toFixed(2));
      row.find('[name$="[acquisition_price]"]').val(pre.acquisition_price.toFixed(2));
    }
  }

  // inventory selection → pricing
$sel.on('select2:select', e => {
    const price = e.params.data.price || 0;
    const acquisitionPrice = e.params.data.acquisition_price || 0;
    row.find('[name$="[price]"]').val(price.toFixed(2));
    row.find('[name$="[acquisition_price]"]').val(acquisitionPrice.toFixed(2));
    row.find('[name$="[quantity]"]').val(1);
    recalc();
})


  // qty/price inputs → recalc
  row.find('[name$="[quantity]"], [name$="[price]"], [name$="[discounted_price]"]').on('input', recalc);


  // remove row
  row.find('.remove-btn').on('click', () => { row.remove(); recalc(); });

  // Manual fields handlers
  row.find('.manual-toggle').on('click', () => {
    row.find('.manual-fields').removeClass('d-none');
    row.find('.input-group').addClass('d-none');
  });
  row.find('.cancel-manual').on('click', () => {
    row.find('.manual-fields').addClass('d-none');
    row.find('.input-group').removeClass('d-none');
  });
  row.find('.save-manual').on('click', () => {
  const partName = row.find('[name$="[manual_part_name]"]').val() || '';
  const serial = row.find('[name$="[manual_serial_number]"]').val() || '';
  const acq = parseFloat(row.find('[name$="[manual_acquisition_price]"]').val()) || 0;
  const sell = parseFloat(row.find('[name$="[manual_selling_price]"]').val()) || 0;

  row.find('[name$="[price]"]').val(sell.toFixed(2));
  row.find('[name$="[quantity]"]').val(1);
  row.find('[name$="[acquisition_price]"]').val(acq.toFixed(2));

  // Replace the cell with static inputs to prevent re-editing
  row.find('td').first().html(`
    <input type="text" name="items[${idx}][manual_part_name]" class="form-control form-control-sm mb-1" value="${partName}" readonly>
    <input type="text" name="items[${idx}][manual_serial_number]" class="form-control form-control-sm mb-1" value="${serial}" readonly>
    <input type="number" name="items[${idx}][manual_acquisition_price]" class="form-control form-control-sm mb-1" value="${acq}" readonly>
    <input type="number" name="items[${idx}][manual_selling_price]" class="form-control form-control-sm mb-1" value="${sell}" readonly>
  `);

  recalc();
});

if (data?.manual_part_name) {
  row.find('.manual-fields').removeClass('d-none');
  row.find('.input-group').addClass('d-none');

  row.find('[name$="[manual_part_name]"]').val(data.manual_part_name);
  row.find('[name$="[manual_serial_number]"]').val(data.manual_serial_number);
  row.find('[name$="[manual_acquisition_price]"]').val(data.manual_acquisition_price);
  row.find('[name$="[manual_selling_price]"]').val(data.manual_selling_price);

  row.find('[name$="[acquisition_price]"]').val(data.manual_acquisition_price);
  row.find('[name$="[price]"]').val(data.manual_selling_price);

  // Show readonly version immediately
  row.find('td').first().html(`
    <input type="text" name="items[${idx}][manual_part_name]" class="form-control form-control-sm mb-1" value="${data.manual_part_name}" readonly>
    <input type="text" name="items[${idx}][manual_serial_number]" class="form-control form-control-sm mb-1" value="${data.manual_serial_number}" readonly>
    <input type="number" name="items[${idx}][manual_acquisition_price]" class="form-control form-control-sm mb-1" value="${data.manual_acquisition_price}" readonly>
    <input type="number" name="items[${idx}][manual_selling_price]" class="form-control form-control-sm mb-1" value="${data.manual_selling_price}" readonly>
  `);
}

  $('#items-table tbody').append(row);
  recalc();
}



// JOB ROW HANDLING
function addJobRow(data = null) {
  const idx = $('#jobs-table tbody tr').length;
  const desc = data && data.job_description ? data.job_description : '';
  const techId = data && data.technician_id ? data.technician_id : '';
  const total = data && data.total ? data.total : '';
  const row = $(`<tr>
      <td><input name="jobs[${idx}][job_description]" class="form-control form-control-sm" value="${desc}"></td>
      <td>
        <select name="jobs[${idx}][technician_id]" class="form-select form-select-sm">
          <option value="">-- select tech --</option>
          ${technicians.map(t => `<option value="${t.id}" ${techId == t.id ? 'selected' : ''}>${t.name}</option>`).join('')}
        </select>
      </td>
      <td><input name="jobs[${idx}][total]" type="number" step="0.01" class="form-control form-control-sm" value="${total}"></td>
      <td><button type="button" class="btn btn-sm btn-danger remove-btn">✕</button></td>
    </tr>`);
  row.find('[name$="[total]"]').on('input', recalc);
  row.find('.remove-btn').on('click', function () {
    row.remove(); recalc();
  });
  $('#jobs-table tbody').append(row);
  recalc();
}

// TOTALS CALCULATION
function recalc() {
  let itemsTotal = 0;
  let jobsTotal = 0;

  // Calculate items line totals
  $('#items-table tbody tr').each(function() {
    const $r = $(this);
   const qty = +$r.find('[name$="[quantity]"]').val() || 0;
const price = +$r.find('[name$="[price]"]').val() || 0;
const discounted = +$r.find('[name$="[discounted_price]"]').val() || 0;
const finalPrice = price - discounted;
const lineTotal = qty * finalPrice;


    itemsTotal += lineTotal;

    // Update line display
    $r.find('.col-line-total').text(lineTotal.toFixed(2));
  });

  // Calculate jobs total
  $('#jobs-table tbody tr').each(function() {
    jobsTotal += +$(this).find('[name$="[total]"]').val() || 0;
  });

  // Calculate totals
  const subtotal = itemsTotal + jobsTotal;
  const totalDiscount = parseFloat($('[name="total_discount"]').val()) || 0;
  const netAfterDisc = subtotal - totalDiscount;
  const vatAmount = netAfterDisc * (0.12 / 1.12);

  // Set values back
  $('[name="subtotal"]').val(subtotal.toFixed(2));
  $('[name="vat_amount"]').val(vatAmount.toFixed(2));
  $('[name="grand_total"]').val(netAfterDisc.toFixed(2));
}





  /// ERROR CHECK (new—Jobs only)
$('#invoiceForm').on('submit', function(e) {
  let hasBlankJob = false;
  $('#jobs-table tbody tr').each(function() {
    const desc = $(this).find('[name$="[job_description]"]').val();
    if (!desc) { hasBlankJob = true; }
  });
  if (hasBlankJob) {
    e.preventDefault();
    alert('Please remove extra blank rows in Jobs before submitting.');
    return false;
  }
  return true;
});

// INIT: Create and Edit Logic
$('#add-item').on('click', () => addItemRow());
$('#add-job').on('click', () => addJobRow());

// If editing, populate items/jobs; if not, start blank row
function populateForm(invoice) {
  $('#items-table tbody').empty();
  $('#jobs-table tbody').empty();

  if (invoice && invoice.items && invoice.items.length) {
    invoice.items.forEach(item => {
addItemRow({
  part_id: item.part_id,
  quantity: item.quantity,
  original_price: item.original_price ?? 0,
  discounted_price: item.discounted_price ?? 0,
  acquisition_price: item.manual_acquisition_price ?? (item.part?.acquisition_price ?? 0),
  manual_part_name: item.manual_part_name,
  manual_serial_number: item.manual_serial_number,
  manual_acquisition_price: item.manual_acquisition_price,
  manual_selling_price: item.manual_selling_price
});



    });
  } else {
    addItemRow();
  }

  if (invoice && invoice.jobs && invoice.jobs.length) {
    invoice.jobs.forEach(job => {
      addJobRow({
        job_description: job.job_description,
        technician_id: job.technician_id,
        total: job.total
      });
    });
  } else {
    addJobRow();
  }

  recalc();
}

$(function() {
  // Populate items/jobs when the modal opens for Create
@if(!isset($invoice))
$('#invoiceModal').on('show.bs.modal', function () {
  // do nothing — let it keep previous inputs
});
@endif


  // Handle edit buttons
  $('.btn-edit-invoice').on('click', function(e){
    e.preventDefault();
    let url = $(this).data('url');
    $.get(url, function(response){
      window.location.href = url + '?modal=1';
    });
  });

  // If coming from edit (controller passes $invoice), auto open modal
  @if(isset($invoice) && request('modal') == 1)
    $('#invoiceModal').modal('show');
    populateForm(@json($invoice));
  @endif

  recalc();
  // whenever bottom discount changes, re-run recalc()
$('[name="total_discount"]').on('input', recalc);

});

</script>
@if ($errors->any())
  <script>
    $(function () {
      $('#invoiceModal').modal('show');
    });
  </script>
@endif
@endsection


