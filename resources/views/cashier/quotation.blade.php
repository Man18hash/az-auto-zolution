@extends('layouts.cashier')

@section('title', isset($invoice) ? 'Edit Quotation' : 'New Quotation')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
  body {
    background: #f6f8fa;
    font-family: "Inter", "Helvetica Neue", Arial, sans-serif;
  }

  .card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s;
    background: white;
    margin-bottom: 1.5rem;
  }

  .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
  }

  .card-header {
    background: #4a90e2;
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
    padding: 1rem 1.25rem;
  }

  .form-control,
  .form-select {
    border-radius: 0.5rem;
    padding: 0.65rem 0.85rem;
    font-size: 0.95rem;
    border: 1px solid #ced4da;
    transition: border-color 0.3s, box-shadow 0.3s;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: #4a90e2;
    box-shadow: 0 0 0 0.15rem rgba(74,144,226,0.25);
  }

  .btn-primary {
    border-radius: 0.5rem;
    padding: 0.65rem 1.5rem;
    font-weight: 500;
    font-size: 0.95rem;
    background: linear-gradient(135deg, #4a90e2, #357ab8);
    border: none;
    transition: background 0.3s;
    color: white;
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #357ab8, #4a90e2);
  }

  .btn-success, .btn-info, .btn-warning, .btn-danger {
    border-radius: 0.4rem;
  }

  .table th,
  .table td {
    vertical-align: middle;
    font-size: 0.92rem;
  }

  .table-hover tbody tr:hover {
    background: #f0f7ff;
    cursor: pointer;
  }

  .badge {
    font-size: 0.75rem;
    padding: 0.35em 0.6em;
  }

  .select2-container .select2-selection--single {
    height: 40px;
    border-radius: 0.5rem;
    border: 1px solid #ced4da;
    padding: 0.25rem 0.5rem;
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px;
    right: 10px;
  }
  .form-control[readonly] {
  background-color: #f8f9fa;
  cursor: not-allowed;
}
.btn-add {
  padding: 0.6rem 1.5rem;
  font-size: 0.95rem;
  transition: all 0.3s;
  border-radius: 0.5rem;
}
.btn-add:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.table tbody tr {
  transition: background-color 0.3s;
}

</style>
<div class="container mt-4">
  <h2 class="mb-4 text-center">{{ isset($invoice) ? 'Edit Quotation' : 'Create Quotation' }}</h2>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <form action="{{ isset($invoice) ? route('cashier.quotation.update', $invoice->id) : route('cashier.quotation.store') }}"
      method="POST" id="quoteForm" autocomplete="off">
  @csrf
  @if(isset($invoice)) @method('PUT') @endif

  {{-- -------------------- Customer & Vehicle Details -------------------- --}}
  <div class="card mb-4 shadow-sm">
    <div class="card-header">Customer & Vehicle Details</div>
    <div class="card-body">
      <div class="row g-3">
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
      <div class="row g-3 mt-2">
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
        <div class="col-md-2">
          <label class="form-label">Number</label>
          <input type="number" name="number" class="form-control"
                 value="{{ old('number', $invoice->number ?? '') }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">Address</label>
          <input type="text" name="address" class="form-control"
                 value="{{ old('address', $invoice->address ?? '') }}">
        </div>
      </div>
    </div>
  </div>

  {{-- -------------------- Items Table -------------------- --}}
  <div class="card mb-4 shadow-sm">
    <div class="card-header">Items</div>
    <div class="card-body p-0">
      <table class="table mb-0" id="items-table">
        <thead>
          <tr>
  <th style="min-width:250px;">Item</th>
  <th>Qty</th>
  <th>Acq. ₱</th> <!-- NEW -->
  <th>Price ₱</th>
  <th>Total ₱</th>
  <th></th>
</tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr>
            <td colspan="5" class="text-end p-2">
              <button type="button" id="add-item" class="btn btn-success btn-add shadow-sm">+ Add Item</button>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  {{-- -------------------- Jobs Table -------------------- --}}
  <div class="card mb-4 shadow-sm">
    <div class="card-header">Jobs</div>
    <div class="card-body p-0">
      <table class="table mb-0" id="jobs-table">
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
            <td colspan="4" class="text-end p-2">
              <button type="button" id="add-job" class="btn btn-success btn-add shadow-sm">+ Add Job</button>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  {{-- -------------------- Totals -------------------- --}}
  <div class="card mb-5 shadow-sm">
  <div class="card-header">Totals Summary</div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label fw-bold">Subtotal</label>
        <input type="number" step="0.01" name="subtotal" class="form-control" readonly>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-bold">Total Discount</label>
        <input type="number" step="0.01" name="total_discount" class="form-control">
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
  </div>
</div>


  <div class="text-end">
    <button class="btn btn-primary">{{ isset($invoice) ? 'Update Quotation' : 'Save Quotation' }}</button>
  </div>
</form>
<br>


  {{-- ---------- Recent Quotations (Last 48 Hours) ---------- --}}
<div class="card mb-5 shadow-sm">
  <div class="card-header">Recent Quotations (Last 48 Hours)</div>
  <div class="card-body p-0">
    @php
      $filtered = $history->whereIn('source_type', ['quotation','cancelled'])
                 ->where('created_at', '>=', now()->subHours(48));
    @endphp

    @if($filtered->isEmpty())
      <div class="p-4 text-center text-muted">
        No quotations or cancelled records in the past 48 hours.
      </div>
    @else
      <div class="table-responsive">
        <table class="table mb-0 table-hover align-middle">
          <thead style="background: #4a90e2; color: white;">
            <tr>
              <th>Customer</th>
              <th>Vehicle</th>
              <th>Source Type</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($filtered as $h)
              <tr>
                <td>{{ $h->client->name ?? $h->customer_name }}</td>
                <td>{{ $h->vehicle->plate_number ?? $h->vehicle_name }}</td>
                <td>
                  @php
                    $badgeClass = [
                      'quotation'    => 'bg-warning text-dark',
                      'cancelled'    => 'bg-danger',
                      'appointment'  => 'bg-info text-dark',
                      'service_order'=> 'bg-secondary',
                      'invoicing'    => 'bg-success text-white'
                    ];
                  @endphp
                  <span class="badge {{ $badgeClass[$h->source_type] ?? 'bg-secondary' }}">
                    {{ ucfirst(str_replace('_',' ',$h->source_type)) }}
                  </span>
                </td>
                <td class="d-flex gap-1">
                  <a href="{{ route('cashier.quotation.view', $h->id) }}" class="btn btn-sm btn-outline-info">View</a>
                  <a href="{{ route('cashier.quotation.edit', $h->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                  <form action="{{ route('cashier.quotation.update', $h->id) }}" method="POST"
                        style="display:inline-flex;align-items:center;">
                    @csrf @method('PUT')
                    <select name="source_type" class="form-select form-select-sm btn-source-type"
                            onchange="this.form.submit()">
                      <option value="quotation"    {{ $h->source_type === 'quotation' ? 'selected' : '' }}>Quotation</option>
                      <option value="cancelled"    {{ $h->source_type === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                      <option value="service_order"{{ $h->source_type === 'service_order' ? 'selected' : '' }}>Service Order</option>
                      <option value="invoicing"    {{ $h->source_type === 'invoicing' ? 'selected' : '' }}>Invoicing</option>
                    </select>
                    <input type="hidden" name="quick_update" value="1" />
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>

</div>
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

  
  /// When a client is selected, fetch and populate client Address and Nubmer
  // 1) Track manual overrides
$('[name="number"], [name="address"]')
  .data('manual', false)
  .on('input', function(){
    $(this).data('manual', true);
  });

// 2) Define a helper to fill number & address
function autofillContact() {
  const clientId = $('#client_id').val();
  const client   = clients.find(c => c.id == clientId);
  if (!client) return;

  const $num  = $('[name="number"]');
  const $addr = $('[name="address"]');

  // use client.phone (not phone_number or number)
  if (!$num.data('manual'))  $num.val(client.phone || '');
  if (!$addr.data('manual')) $addr.val(client.address  || '');
}

// 3) Initialize Select2 first...
$('#client_id').select2({
  data: clients.map(c => ({ id: c.id, text: c.name })),
  placeholder: '-- search client --',
  allowClear: true
});

// 4) Then hook up our autofill
$('#client_id')
  .on('select2:select', autofillContact)   // fires when you pick from the dropdown
  .on('change',           autofillContact); // fallback if you ever change via code

// 5) Finally run it once on page-load in case you're editing an existing record
$(document).ready(autofillContact);



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
  function addItemRow(data = null) {
  const idx      = $('#items-table tbody tr').length;
  const partId   = data && data.part_id             ? data.part_id             : '';
  const qty      = data && data.quantity            ? data.quantity            : 1;
  const orig     = data && data.original_price !== undefined ? data.original_price : '';
  const lineTotal= (orig && qty) ? (orig * qty).toFixed(2) : '0.00';

  const row = $(`
<tr>
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
    <td><input name="items[${idx}][acquisition_price]" type="number" step="0.01" class="form-control form-control-sm" value="${data?.acquisition_price || ''}"></td>
  <td><input name="items[${idx}][original_price]" type="number" step="0.01" class="form-control form-control-sm" value="${orig}"></td>
  <td class="col-line-total">${lineTotal}</td>
  <td><button type="button" class="btn btn-sm btn-danger remove-btn">✕</button></td>
</tr>`);

  // select2
  const select2Data = [
    { id:'', text:'-- search part --', price:0, acquisition:0 },
    ...parts.map(p=>({ id:p.id, text:`${p.item_name} - Stock: ${p.quantity}`, price:Number(p.selling), acquisition: Number(p.acquisition_price) }))
  ];
  const $partSelect = row.find('.part-select');
  $partSelect.select2({
    data: select2Data,
    placeholder: '-- search part --',
    allowClear: true,
    width: 'resolve',
    dropdownParent: $('#items-table')
  });

  // preselect
  if(partId) {
    $partSelect.val(partId).trigger('change');
    const sel = select2Data.find(o=>o.id==partId);
    if(sel && sel.price) {
      row.find('[name$="[original_price]"]').val(sel.price.toFixed(2));
    }
  }
// ─── Restore saved manual entry ───
if (data?.manual_part_name) {
  // 1) open the manual popup
  row.find('.manual-toggle').click();

  // 2) populate all four manual fields
  row.find('[name$="[manual_part_name]"]'        ).val(data.manual_part_name);
  row.find('[name$="[manual_serial_number]"]'    ).val(data.manual_serial_number);    // ← new!
  row.find('[name$="[manual_acquisition_price]"]').val(data.manual_acquisition_price);
  row.find('[name$="[manual_selling_price]"]'    ).val(data.manual_selling_price);

  // 3) insert a fake <option> so Select2 shows the label
  const manualOption = new Option(
    data.manual_part_name,
    '',   // empty value so DB gets null
    true, // defaultSelected
    true  // selected
  );
  $partSelect.append(manualOption).trigger('change');

  // 4) trigger your save-manual handler to copy prices & qty
  row.find('.save-manual').click();

  // 5) force-update the Select2 label (optional, but bullet-proof)
  $partSelect
    .next('.select2-container')
    .find('.select2-selection__rendered')
    .text(data.manual_part_name);
}


  // inventory selection → pricing
  $partSelect.on('select2:select', e=>{
    const price = e.params.data.price||0;
    const acq   = e.params.data.acquisition || 0;
    row.find('[name$="[original_price]"]').val(price.toFixed(2));
    row.find('[name$="[acquisition_price]"]').val(acq.toFixed(2));
    row.find('[name$="[quantity]"]').val(1);
    recalc();
  });
  $partSelect.on('select2:clear', ()=>{
    row.find('[name$="[original_price]"]').val('');
    recalc();
  });

  // qty/price changes → recalc
  row.find('[name$="[quantity]"], [name$="[original_price]"]').on('input', recalc);

  // remove row
  row.find('.remove-btn').on('click', ()=>{ row.remove(); recalc(); });

  // manual
  row.find('.manual-toggle').on('click', ()=>{
    row.find('.manual-fields').removeClass('d-none');
    row.find('.input-group').addClass('d-none');
  });
  row.find('.cancel-manual').on('click', ()=>{
    row.find('.manual-fields').addClass('d-none');
    row.find('.input-group').removeClass('d-none');
  });

 // save manual entry POPULATE
 row.find('.save-manual').on('click', ()=>{
  const sell = parseFloat(row.find('[name$="[manual_selling_price]"]').val())||0;
  // 1) write prices & qty back to row
  row.find('[name$="[original_price]"]').val(sell.toFixed(2));
  row.find('[name$="[discounted_price]"]').val(sell.toFixed(2));
  row.find('[name$="[quantity]"]').val(1);

  // 2) pull the manual name
  const manualName = row.find('[name$="[manual_part_name]"]').val() || '';

  // 3) update the Select2 displayed label
  const $select2 = row.find('.part-select').next('.select2-container');
  $select2.find('.select2-selection__rendered').text(manualName);

  // 4) hide manual form and show the “dropdown” again
  recalc();
  row.find('.manual-fields').addClass('d-none');
  row.find('.input-group').removeClass('d-none');
});

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

  // Sum up items
  $('#items-table tbody tr').each(function() {
    const $r = $(this);
    const q  = +$r.find('[name$="[quantity]"]').val() || 0;
    const p  = +$r.find('[name$="[original_price]"]').val() || 0;
    const lineT = q * p;
    itemsTotal += lineT;
    $r.find('.col-line-total').text(lineT.toFixed(2));
  });

  // Sum up jobs
  $('#jobs-table tbody tr').each(function() {
    jobsTotal += +$(this).find('[name$="[total]"]').val() || 0;
  });

  const subtotal = itemsTotal + jobsTotal;
  const discount = parseFloat($('[name="total_discount"]').val()) || 0;
  const grand    = subtotal - discount;
  const vat      = grand * (0.12 / 1.12);

  $('[name=subtotal]').val(subtotal.toFixed(2));
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
  // INIT (unchanged)
  $('#add-item').on('click', () => addItemRow());
  $('#add-job').on('click', () => addJobRow());

  $('[name="total_discount"]').on('input', recalc);
  $(function() {
    @if(!empty($invoice) && $invoice->items && $invoice->items->count())

      @foreach($invoice->items as $item)
  addItemRow({
    part_id: '{{ $item->part_id }}',
    quantity: '{{ $item->quantity }}',
    original_price: '{{ $item->original_price }}',
    acquisition_price: '{{ $item->acquisition_price }}', <!-- important -->
    manual_part_name: '{{ $item->manual_part_name }}',
    manual_serial_number: '{{ $item->manual_serial_number }}',
    manual_acquisition_price: '{{ $item->manual_acquisition_price }}',
    manual_selling_price: '{{ $item->manual_selling_price }}',
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
