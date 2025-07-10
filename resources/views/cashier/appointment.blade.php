@extends('layouts.cashier')



@section('title', isset($invoice) ? 'Edit Appointment' : 'New Appointment')

@section('content')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    .select2-container {
    width: 100% !important;
    }

    .select2-dropdown {
    z-index: 10060;
    }

    .btn-source-type {
    min-width: 120px;
    margin-left: 4px;
    }
  </style>
  <div class="container mt-4">
    <h2 class="mb-4 text-center">{{ isset($invoice) ? 'Edit Appointment' : 'Create Appointment' }}</h2>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form
    action="{{ isset($invoice) ? route('cashier.appointment.update', $invoice->id) : route('cashier.appointment.store') }}"
    method="POST" id="quoteForm" autocomplete="off">
    @csrf
    @if(isset($invoice)) @method('PUT') @endif

    {{-- Header Details --}}
    <div class="row g-3 mb-4">
      <div class="col-md-3" id="client-dropdown-wrap">
      <select name="client_id" id="client_id" class="form-select">
        <option value="">Select client</option>
        @foreach($clients as $c)
      <option value="{{ $c->id }}" {{ old('client_id', $invoice->client_id ?? '') == $c->id ? 'selected' : '' }}>
      {{ $c->name }}
      </option>
      @endforeach
      </select>
      </div>

      <div class="col-md-3" id="manual-customer-wrap">
      <input type="text" name="customer_name" id="customer_name" class="form-control" placeholder="Manual customer"
        value="{{ old('customer_name', $invoice->customer_name ?? '') }}">
      </div>

      <div class="col-md-3" id="vehicle-dropdown-wrap">
      <select name="vehicle_id" id="vehicle_id" class="form-select">
        <option value="">Select vehicle</option>
        @foreach($vehicles as $v)
      <option value="{{ $v->id }}" {{ old('vehicle_id', $invoice->vehicle_id ?? '') == $v->id ? 'selected' : '' }}>
      {{ $v->plate_number }}
      </option>
      @endforeach
      </select>
      </div>

      <div class="col-md-3" id="manual-vehicle-wrap">
      <input type="text" name="vehicle_name" id="vehicle_name" class="form-control" placeholder="Manual vehicle"
        value="{{ old('vehicle_name', $invoice->vehicle_name ?? '') }}">
      </div>


      <div class="row g-3 mb-4">
      <div class="col-md-2">
        <input type="text" name="plate" id="plate" class="form-control" placeholder="Plate"
        value="{{ old('plate', isset($invoice->vehicle) ? $invoice->vehicle->plate_number : '') }}">
      </div>
      <div class="col-md-2">
        <input type="text" name="model" id="model" class="form-control" placeholder="Model"
        value="{{ old('model', isset($invoice->vehicle) ? $invoice->vehicle->model : '') }}">
      </div>

      <div class="col-md-2">
        <input type="text" name="year" id="year" class="form-control" placeholder="Year"
        value="{{ old('year', isset($invoice->vehicle) ? $invoice->vehicle->year : '') }}">
      </div>
      <div class="col-md-2">
        <input type="text" name="color" id="color" class="form-control" placeholder="Color"
        value="{{ old('color', isset($invoice->vehicle) ? $invoice->vehicle->color : '') }}">
      </div>
      <div class="col-md-2">
        <input type="text" name="odometer" id="odometer" class="form-control" placeholder="Odometer"
        value="{{ old('odometer', isset($invoice->vehicle) ? $invoice->vehicle->odometer : '') }}">
      </div>
      <div class="col-md-2">
        <select name="payment_type" class="form-select" style="background:#e6ffe3">
        <option value="">Payment Type</option>
        <option value="cash" @selected(old('payment_type', $invoice->payment_type ?? '') == 'cash')>Cash</option>
        <option value="debit" @selected(old('payment_type', $invoice->payment_type ?? '') == 'debit')>Debit</option>
        <option value="credit" @selected(old('payment_type', $invoice->payment_type ?? '') == 'credit')>Credit
        </option>
        <option value="non_cash" @selected(old('payment_type', $invoice->payment_type ?? '') == 'non_cash')>Non Cash
        </option>
        </select>
      </div>
      <div class="col-md-2">
        <input type="number" name="number" class="form-control" placeholder="Number"
        value="{{ old('number', $invoice->number ?? '') }}">
      </div>
      <div class="col-md-4">
        <input type="text" name="address" class="form-control" placeholder="Address"
        value="{{ old('address', $invoice->address ?? '') }}">
      </div>

      </div>
      {{-- Items --}}
      <h4>Items</h4>
      <table class="table table-bordered" id="items-table">
      <thead>
        <tr>
        <th style="min-width:250px;">Item</th>
        <th>Qty</th>
        <th>Price ₱</th>
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
        <input type="number" step="0.01" name="total_discount" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">VAT (12%)</label>
        <input type="number" step="0.01" name="vat_amount" class="form-control" readonly>
      </div>
      <div class="col-md-3">
        <label class="form-label">Grand Total</label>
        <input type="number" step="0.01" name="grand_total" class="form-control" readonly>
      </div>
      </div>


      <button class="btn btn-primary">{{ isset($invoice) ? 'Update Appointment' : 'Save Appointment' }}</button>
    </form>

    {{-- ---------- Recent Appointments ---------- --}}
    <h3 class="mt-5">Recent Appointments</h3>

    @php
    // Get all appointments and cancelled regardless of date for testing
    $filtered = $history->whereIn('source_type', ['appointment', 'cancelled']);

  @endphp

    @if($filtered->isEmpty())
    <p>No Appointment or cancelled records found.</p>
    @else
    <table class="table table-striped">
    <thead>
      <tr>
      <th>Customer</th>
      <th>Vehicle</th>
      <th>Source Type</th>
      <th>Created</th>
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
      $badgeClass = match ($h->source_type) {
      'cancelled' => 'bg-danger',
      'service_order' => 'bg-secondary',
      'invoicing' => 'bg-success',
      default => 'bg-info'
      };
      @endphp
      <span class="badge {{ $badgeClass }}">
      {{ ucfirst($h->source_type) }}
      </span>
      </td>

      <td>{{ $h->created_at->format('Y-m-d H:i') }}</td>
      <td class="d-flex gap-1">
      <a href="{{ route('cashier.appointment.view', $h->id) }}" class="btn btn-sm btn-info">View</a>
      <a href="{{ route('cashier.appointment.edit', $h->id) }}" class="btn btn-sm btn-primary">Edit</a>
      <form action="{{ route('cashier.appointment.update', $h->id) }}" method="POST"
      style="display:inline-flex;align-items:center;">
      @csrf @method('PUT')
      <select name="source_type" class="form-select form-select-sm btn-source-type" onchange="this.form.submit()">
      <option value="appointment" {{ $h->source_type === 'appointment' ? 'selected' : '' }}>Appointment</option>
      <option value="cancelled" {{ $h->source_type === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
      <option value="service_order" {{ $h->source_type === 'service_order' ? 'selected' : '' }}>Service Order
      </option>
      <option value="invoicing" {{ $h->source_type === 'invoicing' ? 'selected' : '' }}>Invoicing</option>
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
    const clients = @json($clients);  // Assuming clients data is available
    const vehicles = @json($vehicles);  // Assuming vehicles data is available

    // Client and Vehicle Search
    $('#client_id').select2({
    placeholder: 'Select client',
    allowClear: true
    });

    $('#vehicle_id').select2({
    placeholder: 'Select vehicle',
    allowClear: true
    });

    /// When a client is selected, fetch and populate vehicle options with data attributes
    $('#client_id').on('change', function () {
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
    $('#vehicle_id').on('change', function () {
    let selected = $(this).find(':selected');
    $('#plate').val(selected.data('plate') || '');
    $('#model').val(selected.data('model') || '');
    $('#year').val(selected.data('year') || '');
    $('#color').val(selected.data('color') || '');
    $('#odometer').val(selected.data('odometer') || '');
    });

    function addItemRow(data = null) {
    const idx = $('#items-table tbody tr').length;
    const partId = data?.part_id || '';
    const qty = data?.quantity || 1;
    const price = data?.original_price || '';
    const lineTotal = (qty * price).toFixed(2);

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
    <td><input name="items[${idx}][quantity]" type="number" class="form-control form-control-sm" value="${qty}"></td>
    <td><input name="items[${idx}][original_price]" type="number" step="0.01" class="form-control form-control-sm" value="${price}"></td>
    <td class="col-line-total">${lineTotal}</td>
    <td><button type="button" class="btn btn-sm btn-danger remove-btn">✕</button></td>
    </tr>`);

    // ─── select2 setup ───
    const select2Data = [
      { id: '', text: '-- search part --', price: 0 },
      ...parts.map(p => ({
      id: p.id,
      text: `${p.item_name} - Stock: ${p.quantity}`,
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
    if (partId) {
      $partSelect.val(partId).trigger('change');
      const sel = select2Data.find(o => o.id == partId);
      if (sel && sel.price) {
      row.find('[name$="[original_price]"]').val(sel.price.toFixed(2));
      }
    }

    // inventory selection → pricing
    $partSelect.on('select2:select', e => {
      const price = e.params.data.price || 0;
      row.find('[name$="[original_price]"]').val(price.toFixed(2));
      row.find('[name$="[quantity]"]').val(1);
      recalc();
    });
    $partSelect.on('select2:clear', () => {
      row.find('[name$="[original_price]"]').val('');
      recalc();
    });

    // qty/price inputs → recalc
    row.find('[name$="[quantity]"], [name$="[original_price]"]').on('input', recalc);

    // remove row
    row.find('.remove-btn').on('click', () => { row.remove(); recalc(); });

    // manual handlers
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
      row.find('[name$="[original_price]"]').val(sell.toFixed(2));
      row.find('[name$="[quantity]"]').val(1);
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
    let jobsTotal = 0;

    $('#items-table tbody tr').each(function () {
      const $r = $(this);
      const qty = +$r.find('[name$="[quantity]"]').val() || 0;
      const price = +$r.find('[name$="[original_price]"]').val() || 0;
      const lineTotal = qty * price;

      itemsTotal += lineTotal;

      $r.find('.col-line-total').text(lineTotal.toFixed(2));
    });

    $('#jobs-table tbody tr').each(function () {
      jobsTotal += +$(this).find('[name$="[total]"]').val() || 0;
    });

    const subtotal = itemsTotal + jobsTotal;
    const totalDiscount = parseFloat($('[name="total_discount"]').val()) || 0;
    const netAfterDisc = subtotal - totalDiscount;
    const vatAmount = netAfterDisc * (0.12 / 1.12);

    $('[name="subtotal"]').val(subtotal.toFixed(2));
    $('[name="vat_amount"]').val(vatAmount.toFixed(2));
    $('[name="grand_total"]').val(netAfterDisc.toFixed(2));
    }




    /// ERROR CHECK (new—Jobs only)
    $('#quoteForm').on('submit', function (e) {
    let hasBlankJob = false;
    $('#jobs-table tbody tr').each(function () {
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
    $(function () {
    @if(isset($invoice) && $invoice->items && $invoice->items->count())
      @foreach($invoice->items as $item)
      addItemRow({
      part_id: '{{ $item->part_id }}',
      quantity: '{{ $item->quantity }}',
      price: '{{ $item->discounted_price ?? $item->original_price }}'
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

    function toggleMutualFields() {
    const hasManualCustomer = $('#customer_name').val().trim().length > 0;
    const hasManualVehicle = $('#vehicle_name').val().trim().length > 0;
    const hasManualInput = hasManualCustomer || hasManualVehicle;

    const hasDropdownSelected = $('#client_id').val() || $('#vehicle_id').val();

    if (hasManualInput) {
      // Hide both dropdowns
      $('#client-dropdown-wrap').hide();
      $('#vehicle-dropdown-wrap').hide();
      // Show manual inputs
      $('#manual-customer-wrap').show();
      $('#manual-vehicle-wrap').show();
    } else if (hasDropdownSelected) {
      // Hide manual inputs
      $('#manual-customer-wrap').hide();
      $('#manual-vehicle-wrap').hide();
      // Show dropdowns
      $('#client-dropdown-wrap').show();
      $('#vehicle-dropdown-wrap').show();
    } else {
      // Show all if nothing filled
      $('#client-dropdown-wrap').show();
      $('#vehicle-dropdown-wrap').show();
      $('#manual-customer-wrap').show();
      $('#manual-vehicle-wrap').show();
    }
    }

    // Bind events
    $('#client_id, #vehicle_id').on('change', toggleMutualFields);
    $('#customer_name, #vehicle_name').on('input', toggleMutualFields);

    // Initial check on page load
    $(toggleMutualFields);

  </script>

@endsection