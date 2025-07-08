@extends('layouts.cashier')

@section('title', isset($invoice) ? 'Edit Invoice' : 'New Invoice')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container { width: 100% !important; }
.select2-dropdown { z-index: 10060; }
.btn-source-type { min-width: 120px; margin-left: 4px;}
</style>

<div class="container mt-4 mb-3">
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#invoiceModal" id="btnCreateInvoice">
    <i class="bi bi-plus"></i> Create Invoice
  </button>
</div>

{{-- Invoice Modal --}}
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
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

        <form action="{{ isset($invoice) ? route('cashier.invoice.update', $invoice->id) : route('cashier.invoice.store') }}"
              method="POST" id="invoiceForm" autocomplete="off">
          @csrf
          @if(isset($invoice)) @method('PUT') @endif

          {{-- Header Details --}}
          <div class="row g-3 mb-4">
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
          <div class="row g-3 mb-4">
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
  <input
    type="number"
    name="number"
    class="form-control"
    value="{{ old('number', $invoice->number ?? '') }}"
  >
</div>
<div class="col-md-4">
  <label class="form-label fw-bold">Address</label>
  <input
    type="text"
    name="address"
    class="form-control"
    value="{{ old('address', $invoice->address ?? '') }}"
  >
</div>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-bold">Invoice No</label>
            <input
            type="text"
            name="invoice_no"
            class="form-control"
            placeholder="INV-2025-001"
            value="{{ old('invoice_no', $invoice->invoice_no ?? '') }}"
            required
            >
          </div>

          {{-- Items --}}
          <h4 class="fw-bold">Items</h4>
          <table class="table table-bordered" id="items-table">
            <thead>
              <tr>
                <th style="min-width:250px;">Item</th>
                <th>Qty</th>
                <th>Orig ₱</th>
                <th>Disc ₱</th>
                <th>Disc Val</th>
                <th>Total ₱</th>
                <th></th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
              <tr>
                <td colspan="7" class="text-end">
                  <button type="button" id="add-item" class="btn btn-sm btn-success">+ Add Item</button>
                </td>
              </tr>
            </tfoot>
          </table>

          {{-- Jobs --}}
          <h4 class="fw-bold">Jobs</h4>
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

          {{-- Totals --}}
          <div class="row g-3 mb-4">
            <div class="col-md-3">
              <label class="form-label fw-bold">Subtotal</label>
              <input type="number" step="0.01" name="subtotal" class="form-control" readonly>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-bold">Total Discount</label>
              <input type="number" step="0.01" name="total_discount" class="form-control" readonly>
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

{{-- Unpaid Invoices --}}
<h3 class="mt-5 fw-bold">Recent Unpaid Invoices</h3>
@php
  $filteredUnpaid = $history->where('source_type', 'invoicing')
    ->where('created_at', '>=', now()->subHours(48))
    ->where('status', 'unpaid');
@endphp

@if($filteredUnpaid->isEmpty())
  <p>No unpaid invoices.</p>
@else
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Customer</th>
        <th>Vehicle</th>
        <th>Payment Type</th>
        <th>Service Status</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach($filteredUnpaid as $h)
        <tr>
          <td>{{ $h->client->name ?? $h->customer_name }}</td>
          <td>{{ $h->vehicle->plate_number ?? $h->vehicle_name }}</td>
          <td>{{ ucfirst(str_replace('_', ' ', $h->payment_type)) }}</td>
          <td>
            <form action="{{ route('cashier.invoice.update', $h->id) }}" method="POST" style="display:inline;">
              @csrf @method('PUT')
              <input type="hidden" name="status" value="unpaid">
              <input type="hidden" name="client_id" value="{{ $h->client_id }}">
              <input type="hidden" name="vehicle_id" value="{{ $h->vehicle_id }}">
              <input type="hidden" name="payment_type" value="{{ $h->payment_type }}">
              <input type="hidden" name="customer_name" value="{{ $h->customer_name }}">
              <input type="hidden" name="vehicle_name" value="{{ $h->vehicle_name }}">
              <input type="hidden" name="plate" value="{{ $h->vehicle->plate_number ?? '' }}">
              <input type="hidden" name="model" value="{{ $h->vehicle->model ?? '' }}">
              <input type="hidden" name="year" value="{{ $h->vehicle->year ?? '' }}">
              <input type="hidden" name="color" value="{{ $h->vehicle->color ?? '' }}">
              <input type="hidden" name="odometer" value="{{ $h->vehicle->odometer ?? '' }}">
              <input type="hidden" name="subtotal" value="{{ $h->subtotal }}">
              <input type="hidden" name="total_discount" value="{{ $h->total_discount }}">
              <input type="hidden" name="vat_amount" value="{{ $h->vat_amount }}">
              <input type="hidden" name="grand_total" value="{{ $h->grand_total }}">
              <select name="service_status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                <option value="pending"      {{ $h->service_status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_progress"  {{ $h->service_status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="done"         {{ $h->service_status == 'done' ? 'selected' : '' }}>Done</option>
              </select>
            </form>
          </td>
          <td>
            <span class="badge bg-secondary">Unpaid</span>
          </td>
          <td class="d-flex gap-1">
            <a href="{{ route('cashier.invoice.view', $h->id) }}" class="btn btn-sm btn-info">Print</a>
            <button 
              class="btn btn-sm btn-primary btn-edit-invoice"
              data-id="{{ $h->id }}"
              data-url="{{ route('cashier.invoice.edit', $h->id) }}"
            >Edit</button>
            <form action="{{ route('cashier.invoice.update', $h->id) }}" method="POST" style="display:inline-flex;align-items:center;">
              @csrf @method('PUT')
              <input type="hidden" name="status" value="paid">
              <input type="hidden" name="service_status" value="{{ $h->service_status }}">
              <input type="hidden" name="client_id" value="{{ $h->client_id }}">
              <input type="hidden" name="vehicle_id" value="{{ $h->vehicle_id }}">
              <input type="hidden" name="payment_type" value="{{ $h->payment_type }}">
              <input type="hidden" name="customer_name" value="{{ $h->customer_name }}">
              <input type="hidden" name="vehicle_name" value="{{ $h->vehicle_name }}">
              <input type="hidden" name="plate" value="{{ $h->vehicle->plate_number ?? '' }}">
              <input type="hidden" name="model" value="{{ $h->vehicle->model ?? '' }}">
              <input type="hidden" name="year" value="{{ $h->vehicle->year ?? '' }}">
              <input type="hidden" name="color" value="{{ $h->vehicle->color ?? '' }}">
              <input type="hidden" name="odometer" value="{{ $h->vehicle->odometer ?? '' }}">
              <input type="hidden" name="subtotal" value="{{ $h->subtotal }}">
              <input type="hidden" name="total_discount" value="{{ $h->total_discount }}">
              <input type="hidden" name="vat_amount" value="{{ $h->vat_amount }}">
              <input type="hidden" name="grand_total" value="{{ $h->grand_total }}">
              <button type="submit" class="btn btn-sm btn-success">Mark as Paid</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endif

{{-- Paid Invoices --}}
<h3 class="mt-5 fw-bold">Recent Paid Invoices</h3>
@php
  $filteredPaid = $history->where('source_type', 'invoicing')
    ->where('created_at', '>=', now()->subHours(48))
    ->where('status', 'paid');
@endphp

@if($filteredPaid->isEmpty())
  <p>No paid invoices in the past 48 hours.</p>
@else
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Customer</th>
        <th>Vehicle</th>
        <th>Payment Type</th>
        <th>Service Status</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach($filteredPaid as $h)
        <tr>
          <td>{{ $h->client->name ?? $h->customer_name }}</td>
          <td>{{ $h->vehicle->plate_number ?? $h->vehicle_name }}</td>
          <td>{{ ucfirst(str_replace('_', ' ', $h->payment_type)) }}</td>
          <td>{{ ucfirst(str_replace('_', ' ', $h->service_status)) }}</td>
          <td>
            <span class="badge bg-success text-white">Paid</span>
          </td>
          <td>
            <a href="{{ route('cashier.invoice.view', $h->id) }}" class="btn btn-sm btn-info">View</a>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
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
    allowClear: true
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
  const idx       = $('#items-table tbody tr').length;
  const partId    = data?.part_id             || '';
  const qty       = data?.quantity            || 1;
  const orig      = (data?.original_price ?? '') + '';
  const disc      = (data?.discounted_price ?? '') + '';
  const discVal   = (orig && disc && qty)
    ? ((orig - disc) * qty).toFixed(2)
    : '0.00';
  const lineTotal = (disc && qty)
    ? (disc * qty).toFixed(2)
    : '0.00';

  const row = $(`<tr>
    <td>
      <div class="input-group">
        <select name="items[${idx}][part_id]"
                class="form-select form-select-sm part-select"
                style="width:100%">
          <option value="">-- search part --</option>
        </select>
        <button type="button" class="btn btn-warning btn-sm manual-toggle">
          Manual
        </button>
      </div>
      <div class="manual-fields mt-2 d-none">
        <input type="text"   name="items[${idx}][manual_part_name]"         class="form-control form-control-sm mb-1" placeholder="Part Name">
        <input type="text"   name="items[${idx}][manual_serial_number]"     class="form-control form-control-sm mb-1" placeholder="Serial #">
        <input type="number" name="items[${idx}][manual_acquisition_price]" class="form-control form-control-sm mb-1" placeholder="Acquisition ₱">
        <input type="number" name="items[${idx}][manual_selling_price]"     class="form-control form-control-sm mb-1" placeholder="Selling ₱">
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-secondary cancel-manual">Cancel</button>
          <button type="button" class="btn btn-sm btn-success   save-manual">Save</button>
        </div>
      </div>
    </td>
    <td><input name="items[${idx}][quantity]"         type="number" class="form-control form-control-sm" value="${qty}"></td>
    <td><input name="items[${idx}][original_price]"   type="number" step="0.01" readonly class="form-control form-control-sm" value="${orig}"></td>
    <td><input name="items[${idx}][discounted_price]" type="number" step="0.01" class="form-control form-control-sm" value="${disc}"></td>
    <td class="col-disc-val">${discVal}</td>
    <td class="col-line-total">${lineTotal}</td>
    <td><button type="button" class="btn btn-sm btn-danger remove-btn">✕</button></td>
  </tr>`);

  // — Select2 setup —
  const select2Data = [
    { id:'', text:'-- search part --', price:0 },
    ...parts.map(p => ({
      id:    p.id,
      text:  `${p.item_name} – Stock: ${p.quantity}`,
      price: Number(p.selling)
    }))
  ];
  const $sel = row.find('.part-select').select2({
    data: select2Data,
    placeholder: '-- search part --',
    allowClear:  true,
    width:       'resolve',
    dropdownParent: $('#items-table')
  });

  // pre-select on edit
  if (partId) {
    $sel.val(partId).trigger('change');
    const pre = select2Data.find(o => o.id == partId);
    if (pre?.price) {
      row.find('[name$="[original_price]"], [name$="[discounted_price]"]').val(pre.price.toFixed(2));
    }
  }

  // inventory selection → pricing
  $sel.on('select2:select', e => {
    const price = e.params.data.price || 0;
    row.find('[name$="[original_price]"], [name$="[discounted_price]"]').val(price.toFixed(2));
    row.find('[name$="[quantity]"]').val(1);
    recalc();
  }).on('select2:clear', () => {
    row.find('[name$="[original_price]"], [name$="[discounted_price]"]').val('');
    recalc();
  });

  // qty/discount inputs → recalc
  row.find('[name$="[quantity]"], [name$="[discounted_price]"]').on('input', recalc);

  // remove row
  row.find('.remove-btn').on('click', () => { row.remove(); recalc(); });

  // ── Manual popup handlers ──
  row.find('.manual-toggle').on('click', () => {
    row.find('.manual-fields').removeClass('d-none');
    row.find('.input-group').addClass('d-none');
  });
  row.find('.cancel-manual').on('click', () => {
    row.find('.manual-fields').addClass('d-none');
    row.find('.input-group').removeClass('d-none');
  });
  row.find('.save-manual').on('click', () => {
    const sell = parseFloat(row.find('[name$="[manual_selling_price]"]').val()) || 0;
    row.find('[name$="[original_price]"], [name$="[discounted_price]"]').val(sell.toFixed(2));
    row.find('[name$="[quantity]"]').val(1);
    recalc();
    row.find('.manual-fields').addClass('d-none');
    row.find('.input-group').removeClass('d-none');
  });

  // append & final recalc
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
  let subtotal = 0, discount = 0;

  $('#items-table tbody tr').each(function() {
    const qty   = +$(this).find('[name$="[quantity]"]').val() || 0;
    const orig  = +$(this).find('[name$="[original_price]"]').val() || 0;
    const discP = +$(this).find('[name$="[discounted_price]"]').val() || orig;
    const discV = qty * (orig - discP);
    const lineT = qty * discP;

    $(this).find('.col-disc-val').text(discV.toFixed(2));
    $(this).find('.col-line-total').text(lineT.toFixed(2));

    subtotal += qty * orig;
    discount += discV;
  });

  $('#jobs-table tbody tr').each(function() {
    subtotal += +$(this).find('[name$="[total]"]').val() || 0;
  });

  const vat   = (subtotal - discount) * 0.12;
  const grand = subtotal - discount + vat;

  $('[name=subtotal]').val(subtotal.toFixed(2));
  $('[name=total_discount]').val(discount.toFixed(2));
  $('[name=vat_amount]').val(vat.toFixed(2));
  $('[name=grand_total]').val(grand.toFixed(2));
}

  /// ERROR CHECK (new—Jobs only)
$('#quoteForm').on('submit', function(e) {
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
        original_price: item.original_price,
        discounted_price: item.discounted_price
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
      populateForm(null);
      $('#invoiceForm')[0].reset();
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
});

</script>
@endsection


