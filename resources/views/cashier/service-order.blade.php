@extends('layouts.cashier')

@section('title', isset($invoice) ? 'Edit Service Order' : 'New Service Order')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container { width: 100% !important; }
.select2-dropdown { z-index: 10060; }
.btn-source-type { min-width: 120px; margin-left: 4px;}
</style>
<div class="container mt-4">
  <h2 class="mb-4 text-center">{{ isset($invoice) ? 'Edit Service Order' : 'Create Service Order' }}</h2>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <form action="{{ isset($invoice) ? route('cashier.serviceorder.update', $invoice->id) : route('cashier.serviceorder.store') }}"
        method="POST" id="quoteForm" autocomplete="off">
    @csrf
    @if(isset($invoice)) @method('PUT') @endif

    {{-- Header Details --}}
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <label class="form-label">Client</label>
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
        <label class="form-label">Manual Customer Name</label>
        <input type="text" name="customer_name" class="form-control" placeholder="Enter walk-in name"
               value="{{ old('customer_name', $invoice->customer_name ?? '') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">Vehicle</label>
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
        <label class="form-label">Manual Vehicle Name</label>
        <input type="text" name="vehicle_name" class="form-control" placeholder="Enter vehicle details"
               value="{{ old('vehicle_name', $invoice->vehicle_name ?? '') }}">
      </div>
    </div>
    <div class="row g-3 mb-4">
      <div class="col-md-2">
        <label class="form-label">Plate</label>
        <input type="text" name="plate" id="plate" class="form-control"
               value="{{ old('plate', isset($invoice->vehicle) ? $invoice->vehicle->plate_number : '') }}">
      </div>
      <div class="col-md-2">
        <label class="form-label">Model</label>
        <input type="text" name="model" id="model" class="form-control"
               value="{{ old('model', isset($invoice->vehicle) ? $invoice->vehicle->model : '') }}">
      </div>
      <div class="col-md-2">
        <label class="form-label">Year</label>
        <input type="text" name="year" id="year" class="form-control"
               value="{{ old('year', isset($invoice->vehicle) ? $invoice->vehicle->year : '') }}">
      </div>
      <div class="col-md-2">
        <label class="form-label">Color</label>
        <input type="text" name="color" id="color" class="form-control"
               value="{{ old('color', isset($invoice->vehicle) ? $invoice->vehicle->color : '') }}">
      </div>
      <div class="col-md-2">
        <label class="form-label">Odometer</label>
        <input type="text" name="odometer" id="odometer" class="form-control"
               value="{{ old('odometer', isset($invoice->vehicle) ? $invoice->vehicle->odometer : '') }}">
      </div>
      <div class="col-md-2">
        <label class="form-label">Payment Type</label>
        <select name="payment_type" class="form-select" style="background:#e6ffe3">
          <option value="cash"      @selected(old('payment_type', $invoice->payment_type ?? '')=='cash')>Cash</option>
          <option value="debit"     @selected(old('payment_type', $invoice->payment_type ?? '')=='debit')>Debit</option>
          <option value="credit"    @selected(old('payment_type', $invoice->payment_type ?? '')=='credit')>Credit</option>
          <option value="non_cash"  @selected(old('payment_type', $invoice->payment_type ?? '')=='non_cash')>Non Cash</option>
        </select>
      </div>
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
    {{-- Items --}}
    <h4>Items</h4>
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
    <h4>Jobs</h4>
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
        <label class="form-label">Subtotal</label>
        <input type="number" step="0.01" name="subtotal" class="form-control" readonly>
      </div>
      <div class="col-md-3">
        <label class="form-label">Total Discount</label>
        <input type="number" step="0.01" name="total_discount" class="form-control" readonly>
      </div>
      <div class="col-md-3">
        <label class="form-label">VAT (12%)</label>
        <input type="number" step="0.01" name="vat_amount" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">Grand Total</label>
        <input type="number" step="0.01" name="grand_total" class="form-control" readonly>
      </div>
    </div>

    <button class="btn btn-primary">{{ isset($invoice) ? 'Update Service Order' : 'Save Service Order' }}</button>
  </form>

  {{-- ---------- Recent Service Orders (Last 48 Hours) ---------- --}}
  <h3 class="mt-5">Recent Service Orders</h3>

  @php
    $filtered = $history->where('source_type', 'service_order')
      ->where('created_at', '>=', now()->subHours(48));
  @endphp

  @if($filtered->isEmpty())
    <p>No service orders in the past 48 hours.</p>
  @else
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Vehicle</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($filtered as $h)
          <tr>
            <td>{{ $h->client->name ?? $h->customer_name }}</td>
            <td>{{ $h->vehicle->plate_number ?? $h->vehicle_name }}</td>
            <td>
              <span class="badge bg-secondary">
                {{ ucfirst(str_replace('_',' ',$h->source_type)) }}
              </span>
            </td>
            <td class="d-flex gap-1">
              <a href="{{ route('cashier.serviceorder.view', $h->id) }}" class="btn btn-sm btn-info">View</a>
              <a href="{{ route('cashier.serviceorder.edit', $h->id) }}" class="btn btn-sm btn-primary">Edit</a>
              <form action="{{ route('cashier.serviceorder.update', $h->id) }}" method="POST" style="display:inline-flex;align-items:center;">
                @csrf @method('PUT')
                <select name="source_type" class="form-select form-select-sm btn-source-type" onchange="this.form.submit()">
                  <option value="service_order" {{ $h->source_type === 'service_order' ? 'selected' : '' }}>Service Order</option>
                  <option value="cancelled"    {{ $h->source_type === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                  <option value="invoicing"    {{ $h->source_type === 'invoicing' ? 'selected' : '' }}>Invoicing</option>
                </select>
                <input type="hidden" name="quick_update" value="1" />
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const parts = @json($parts);
const technicians = @json($technicians);

// VEHICLE DETAILS AUTOFILL
$('#vehicle_id').on('change', function() {
  let selected = $(this).find(':selected');
  $('#plate').val(selected.data('plate') || '');
  $('#model').val(selected.data('model') || '');
  $('#year').val(selected.data('year') || '');
  $('#color').val(selected.data('color') || '');
  $('#odometer').val(selected.data('odometer') || '');
});

function addItemRow(data = null) {
  const idx      = $('#items-table tbody tr').length;
  const partId   = data && data.part_id             ? data.part_id             : '';
  const qty      = data && data.quantity            ? data.quantity            : 1;
  const orig     = data && data.original_price !== undefined ? data.original_price : '';
  const disc     = data && data.discounted_price!==undefined ? data.discounted_price: '';
  const discVal  = (orig && disc && qty) ? ((orig - disc)*qty).toFixed(2) : '0.00';
  const lineTotal= (disc && qty)         ? (disc * qty).toFixed(2)    : '0.00';

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
          <button type="button" class="btn btn-sm btn-success save-manual">Save</button>
        </div>
      </div>
    </td>
    <td><input name="items[${idx}][quantity]"        type="number" class="form-control form-control-sm" value="${qty}"></td>
    <td><input name="items[${idx}][original_price]"  type="number" step="0.01" readonly class="form-control form-control-sm" value="${orig}"></td>
    <td><input name="items[${idx}][discounted_price]"type="number" step="0.01" class="form-control form-control-sm" value="${disc}"></td>
    <td class="col-disc-val">${discVal}</td>
    <td class="col-line-total">${lineTotal}</td>
    <td><button type="button" class="btn btn-sm btn-danger remove-btn">✕</button></td>
  </tr>`);

  // ─── select2 setup ───
  const select2Data = [
    { id:'', text:'-- search part --', price:0 },
    ...parts.map(p=>({
      id: p.id,
      text:`${p.item_name} - Stock: ${p.quantity}`,
      price: Number(p.selling)
    }))
  ];
  const $partSelect = row.find('.part-select');
  $partSelect.select2({
    data: select2Data,
    placeholder: '-- search part --',
    allowClear: true,
    width: 'resolve',
    dropdownParent: $('#items-table')
  });

  // preselect on edit
  if(partId) {
    $partSelect.val(partId).trigger('change');
    const sel = select2Data.find(o=>o.id==partId);
    if(sel && sel.price) {
      row.find('[name$="[original_price]"]').val(sel.price.toFixed(2));
      row.find('[name$="[discounted_price]"]').val(sel.price.toFixed(2));
    }
  }

  // inventory selection → pricing
  $partSelect.on('select2:select', e=>{
    const price = e.params.data.price||0;
    row.find('[name$="[original_price]"]').val(price.toFixed(2));
    row.find('[name$="[discounted_price]"]').val(price.toFixed(2));
    row.find('[name$="[quantity]"]').val(1);
    recalc();
  });
  $partSelect.on('select2:clear', ()=>{
    row.find('[name$="[original_price]"], [name$="[discounted_price]"]').val('');
    recalc();
  });

  // qty/discount inputs → recalc
  row.find('[name$="[quantity]"], [name$="[discounted_price]"]').on('input', recalc);

  // remove row
  row.find('.remove-btn').on('click', ()=>{ row.remove(); recalc(); });

  // ─── Manual popup handlers ───

  // show manual form
  row.find('.manual-toggle').on('click', ()=>{
    row.find('.manual-fields').removeClass('d-none');
    row.find('.input-group').addClass('d-none');
  });

  // cancel manual
  row.find('.cancel-manual').on('click', ()=>{
    row.find('.manual-fields').addClass('d-none');
    row.find('.input-group').removeClass('d-none');
  });

  // save manual entry
  row.find('.save-manual').on('click', ()=>{
    const sell = parseFloat(row.find('[name$="[manual_selling_price]"]').val())||0;
    row.find('[name$="[original_price]"]').val(sell.toFixed(2));
    row.find('[name$="[discounted_price]"]').val(sell.toFixed(2));
    row.find('[name$="[quantity]"]').val(1);
    recalc();
    row.find('.manual-fields').addClass('d-none');
    row.find('.input-group').removeClass('d-none');
  });

  // append row & final recalc
  $('#items-table tbody').append(row);
  recalc();
}
// JOB ROW HANDLING (unchanged)
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

// TOTALS CALCULATION (unchanged)
function recalc() {
  let itemsTotal = 0;
  let jobsTotal  = 0;
  let discount   = 0;

  // 1) Sum up items & discounts
  $('#items-table tbody tr').each(function() {
    const $r    = $(this);
    const q     = +$r.find('[name$="[quantity]"]').val() || 0;
    const o     = +$r.find('[name$="[original_price]"]').val() || 0;
    const dP    = +$r.find('[name$="[discounted_price]"]').val() || o;
    const discV = q * (o - dP);
    const lineT = q * dP;

    discount   += discV;
    itemsTotal += lineT;

    $r.find('.col-disc-val').text(discV.toFixed(2));
    $r.find('.col-line-total').text(lineT.toFixed(2));
  });

  // 2) Sum up jobs
  $('#jobs-table tbody tr').each(function() {
    jobsTotal += +$(this).find('[name$="[total]"]').val() || 0;
  });

  // 3) Combined total before VAT
  const grand = itemsTotal + jobsTotal;

  // 4) Reverse‐calc 12% VAT on the full amount:
  //    net = grand ÷ 1.12  →  VAT = grand − net  →  VAT = grand * (0.12/1.12)
  const vat = grand * (0.12 / 1.12);

  // 5) Populate your fields
  $('[name=subtotal]').val(grand.toFixed(2));        // items + jobs
  $('[name=total_discount]').val(discount.toFixed(2));
  $('[name=vat_amount]').val(vat.toFixed(2));        // now on entire invoice
  $('[name=grand_total]').val(grand.toFixed(2));     // same as subtotal
}

// INIT (unchanged)
$('#add-item').on('click', () => addItemRow());
$('#add-job').on('click', () => addJobRow());
$(function() {
  @if(isset($invoice) && $invoice->items && $invoice->items->count())
    @foreach($invoice->items as $item)
      addItemRow({
        part_id: '{{ $item->part_id }}',
        quantity: '{{ $item->quantity }}',
        original_price: '{{ $item->original_price }}',
        discounted_price: '{{ $item->discounted_price }}'
      });
    @endforeach
  @else
    addItemRow();
  @endif

  @if(isset($invoice) && $invoice->jobs && $invoice->jobs->count())
    @foreach($invoice->jobs as $job)
      addJobRow({
        job_description: '{{ $job->job_description }}',
        technician_id: '{{ $job->technician_id }}',
        total: '{{ $job->total }}'
      });
    @endforeach
  @else
    addJobRow();
  @endif

  recalc();
});
</script>

@endsection
