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
    <h4 class="mt-4">Customer Information</h4>
    <div class="row g-3 mb-3">
      <div class="col-md-3" id="client-dropdown-wrap">
      <select name="client_id" id="client_id" class="form-select" placeholder="Client">
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
    </div>

    <h4 class="mt-4">Vehicle Information</h4>
    <div class="row g-3 mb-3">
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
    </div>
    <div class="row g-3 mb-3">
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
      <input type="date" name="appointment_date" class="form-control"
        value="{{ old('appointment_date', isset($invoice->appointment_date) ? $invoice->appointment_date->format('Y-m-d') : '') }}">
      </div>
    </div>

   


      <div class="row g-3 mb-3">
      <div class="col-md-6">
        <textarea name="note" class="form-control"
        placeholder="Appointment note">{{ old('note', $invoice->note ?? '') }}</textarea>
      </div>
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
      <th>Note</th>
      <th>Source Type</th>
      <th>Appointment Date</th>
      <th>Created</th>
      <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach($filtered as $h)
      <tr>
      <td>{{ $h->client->name ?? $h->customer_name }}</td>
      <td>{{ $h->vehicle->plate_number ?? $h->vehicle_name }}</td>
      <td>{{ $h->note }}</td>
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
      <td>{{ $h->appointment_date ? \Carbon\Carbon::parse($h->appointment_date)->format('Y-m-d') : '-' }}</td>
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



    // JOB ROW HANDLING (unchanged)


    // INIT (unchanged)





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