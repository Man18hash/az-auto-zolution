@extends('layouts.cashier')



@section('title', isset($invoice) ? 'Edit Appointment' : 'New Appointment')

@section('content')
  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

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
    }

    .form-control,
    .form-select,
    textarea {
    border-radius: 0.5rem;
    padding: 0.65rem 0.85rem;
    font-size: 0.95rem;
    box-shadow: none;
    border: 1px solid #ced4da;
    transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-control:focus,
    .form-select:focus,
    textarea:focus {
    border-color: #4a90e2;
    box-shadow: 0 0 0 0.15rem rgba(74, 144, 226, 0.25);
    }

    button.btn-primary {
    border-radius: 0.5rem;
    padding: 0.65rem 1.5rem;
    font-weight: 500;
    font-size: 0.95rem;
    background: linear-gradient(135deg, #4a90e2, #357ab8);
    border: none;
    transition: background 0.3s;
    color: white;
    }

    button.btn-primary:hover {
    background: linear-gradient(135deg, #357ab8, #4a90e2);
    }

    .btn-outline-secondary {
    border-color: #4a90e2;
    color: #4a90e2;
    }

    .btn-outline-secondary:hover {
    background: #4a90e2;
    color: white;
    }

    textarea.form-control {
    min-height: 100px;
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

    #calendar .fc-toolbar-title {
    font-size: 1.25rem;
    font-weight: 600;
    }

    #calendar .fc-daygrid-event {
    font-size: 0.85rem;
    padding: 2px 4px;
    }
  </style>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
  <div class="container mt-4">
    <h2 class="mb-4 text-center">{{ isset($invoice) ? 'Edit Appointment' : 'Create Appointment' }}</h2>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif


    <button class="btn btn-outline-secondary shadow-sm mb-3" data-bs-toggle="modal" data-bs-target="#calendarModal">
    Show Calendar
    </button>



    <script>
    let calendarInitialized = false;

    document.addEventListener('DOMContentLoaded', function () {
      const calendarModal = document.getElementById('calendarModal');

      calendarModal.addEventListener('shown.bs.modal', function () {
      const calendarEl = document.getElementById('calendar');

      if (!calendarInitialized) {
        window.calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        themeSystem: 'bootstrap',
        height: "auto",
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: @json($events),
        eventClick: function (info) {
          info.jsEvent.preventDefault();
          if (info.event.url) {
          window.location.href = info.event.url;
          }
        }
        });
        window.calendar.render();
        calendarInitialized = true;
      } else {
        window.calendar.render();
      }
      });
    });
    </script>





    <form
    action="{{ isset($invoice) ? route('cashier.appointment.update', $invoice->id) : route('cashier.appointment.store') }}"
    method="POST" id="quoteForm" autocomplete="off">
    @csrf
    @if(isset($invoice)) @method('PUT') @endif

    {{-- Header Details --}}
    <div class="card mb-4 shadow-sm">
      <div class="card-header fw-bold">Customer Information</div>
      <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3" id="client-dropdown-wrap">
        <label for="client_id" class="form-label">Select Client</label>
        <select name="client_id" id="client_id" class="form-select">
          <option value="">Select client</option>
          @foreach($clients as $c)
        <option value="{{ $c->id }}" data-phone="{{ $c->phone }}" data-email="{{ $c->email }}"
        data-address="{{ $c->address }}" {{ old('client_id', $invoice->client_id ?? '') == $c->id ? 'selected' : '' }}>
        {{ $c->name }}
        </option>
      @endforeach
        </select>
        </div>

        <div class="col-md-3" id="manual-customer-wrap">
        <label for="customer_name" class="form-label">Manual Customer</label>
        <input type="text" name="customer_name" id="customer_name" class="form-control"
          value="{{ old('customer_name', $invoice->customer_name ?? '') }}">
        </div>

        <div class="col-md-3">
        <label for="phone" class="form-label">Phone</label>
        <input type="text" name="phone" id="phone" class="form-control"
          value="{{ old('phone', $invoice->client->phone ?? '') }}">
        </div>

        <div class="col-md-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control"
          value="{{ old('email', $invoice->client->email ?? '') }}">
        </div>

        <div class="col-md-6">
        <label for="address" class="form-label">Address</label>
        <input type="text" name="address" id="address" class="form-control"
          value="{{ old('address', $invoice->client->address ?? '') }}">
        </div>
      </div>
      </div>
    </div>


    <div class="card mb-4 shadow-sm">
      <div class="card-header fw-bold">Vehicle Information</div>
      <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-3" id="vehicle-dropdown-wrap">
        <label for="vehicle_id" class="form-label">Select Vehicle</label>
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
        <label for="vehicle_name" class="form-label">Manual Vehicle</label>
        <input type="text" name="vehicle_name" id="vehicle_name" class="form-control"
          value="{{ old('vehicle_name', $invoice->vehicle_name ?? '') }}">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-2">
        <label for="plate" class="form-label">Plate</label>
        <input type="text" name="plate" id="plate" class="form-control"
          value="{{ old('plate', isset($invoice->vehicle) ? $invoice->vehicle->plate_number : '') }}">
        </div>
        <div class="col-md-2">
        <label for="model" class="form-label">Model</label>
        <input type="text" name="model" id="model" class="form-control"
          value="{{ old('model', isset($invoice->vehicle) ? $invoice->vehicle->model : '') }}">
        </div>
        <div class="col-md-2">
        <label for="year" class="form-label">Year</label>
        <input type="text" name="year" id="year" class="form-control"
          value="{{ old('year', isset($invoice->vehicle) ? $invoice->vehicle->year : '') }}">
        </div>
        <div class="col-md-2">
        <label for="color" class="form-label">Color</label>
        <input type="text" name="color" id="color" class="form-control"
          value="{{ old('color', isset($invoice->vehicle) ? $invoice->vehicle->color : '') }}">
        </div>
        <div class="col-md-2">
        <label for="odometer" class="form-label">Odometer</label>
        <input type="text" name="odometer" id="odometer" class="form-control"
          value="{{ old('odometer', isset($invoice->vehicle) ? $invoice->vehicle->odometer : '') }}">
        </div>
        <div class="col-md-2">
        <label for="appointment_date" class="form-label">Appointment Date</label>
        <input type="date" name="appointment_date" id="appointment_date" class="form-control"
          value="{{ old('appointment_date', isset($invoice->appointment_date) ? $invoice->appointment_date->format('Y-m-d') : '') }}">
        </div>
        @if ($errors->has('appointment_date'))
      <div class="text-danger small">
      {{ $errors->first('appointment_date') }}
      </div>
      @endif

      </div>
      </div>
    </div>

    <div class="card mb-4 shadow-sm">
      <div class="card-header fw-bold">Appointment Notes</div>
      <div class="card-body">
      <div class="mb-3">
        <label for="note" class="form-label">Notes</label>
        <textarea name="note" id="note" class="form-control"
        placeholder="Appointment note">{{ old('note', $invoice->note ?? '') }}</textarea>
      </div>
      <button
        class="btn btn-primary shadow-sm">{{ isset($invoice) ? 'Update Appointment' : 'Save Appointment' }}</button>
      </div>
    </div>

    </form>
    @php
    $filtered = $history->whereIn('source_type', ['appointment', 'cancelled']);
    @endphp
    {{-- ---------- Recent Appointments ---------- --}}
    <div class="card mb-5 shadow-sm">
    <div class="card-header">Recent Appointments</div>
    <div class="card-body p-0">
      @if($filtered->isEmpty())
      <div class="p-4 text-center text-muted">
      No Appointment or cancelled records found.
      </div>
    @else
      <div class="table-responsive">
      <table class="table mb-0 table-hover align-middle">
      <thead style="background: #4a90e2; color: white;">
      <tr>
        <th>Customer</th>
        <th>Vehicle</th>
        <th>Note</th>
        <th>Source Type</th>
        <th>Appointment Date</th>
        <th>Phone</th>
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
      <td>{{ $h->client->phone ?? '-' }}</td>

      <td class="d-flex gap-1">

      <a href="{{ route('cashier.appointment.edit', $h->id) }}"
        class="btn btn-sm btn-outline-primary">Edit</a>
      <form action="{{ route('cashier.appointment.update', $h->id) }}" method="POST"
        style="display:inline-flex;align-items:center;">
        @csrf @method('PUT')
        <select name="source_type" class="form-select form-select-sm btn-source-type"
        onchange="this.form.submit()">
        <option value="appointment" {{ $h->source_type === 'appointment' ? 'selected' : '' }}>Appointment
        </option>
        <option value="cancelled" {{ $h->source_type === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        <option value="service_order" {{ $h->source_type === 'service_order' ? 'selected' : '' }}>Service
        Order</option>
        <option value="invoicing" {{ $h->source_type === 'invoicing' ? 'selected' : '' }}>Invoicing</option>
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

    <!-- Calendar Modal -->
    <!-- Calendar Modal -->
    <div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="calendarModalLabel">📅 Appointment Calendar</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div id="calendar" style="min-height: 600px;"></div>
      </div>
      </div>
    </div>
    </div>

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
    const selectedClient = $(this).find(':selected');

    // Autofill client contact info
    $('#phone').val(selectedClient.data('phone') || '');
    $('#email').val(selectedClient.data('email') || '');
    $('#address').val(selectedClient.data('address') || '');

    // Filter and populate vehicle dropdown
    const filteredVehicles = vehicles.filter(vehicle => vehicle.client_id == clientId);

    $('#vehicle_id').empty().append(`<option value="">— walk-in or choose —</option>`);
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