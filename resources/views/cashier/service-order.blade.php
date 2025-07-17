@extends('layouts.cashier')

@section('title', isset($invoice) ? 'Edit Service Order' : 'New Service Order')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background: #f6f8fa;
    font-family: "Inter", "Helvetica Neue", Arial, sans-serif;
}
.card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    transition: transform 0.2s;
    background: white;
    margin-bottom: 1.5rem;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
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
.form-control, .form-select {
    border-radius: 0.5rem;
    padding: 0.65rem 0.85rem;
    font-size: 0.95rem;
}
.btn-primary {
    border-radius: 0.5rem;
    padding: 0.65rem 1.5rem;
    font-size: 0.95rem;
    background: linear-gradient(135deg, #4a90e2, #357ab8);
    color: white;
    border: none;
}
.btn-primary:hover {
    background: linear-gradient(135deg, #357ab8, #4a90e2);
}
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
        method="POST" autocomplete="off">
    @csrf
    @if(isset($invoice)) @method('PUT') @endif

    <input type="hidden" name="subtotal" value="0">
<input type="hidden" name="total_discount" value="0">
<input type="hidden" name="vat_amount" value="0">
<input type="hidden" name="grand_total" value="0">


    {{-- Customer & Vehicle --}}
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
            <input type="text" name="customer_name" class="form-control"
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
            <input type="text" name="vehicle_name" class="form-control"
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
              <option value="cash" @selected(old('payment_type', $invoice->payment_type ?? '')=='cash')>Cash</option>
              <option value="debit" @selected(old('payment_type', $invoice->payment_type ?? '')=='debit')>Debit</option>
              <option value="credit" @selected(old('payment_type', $invoice->payment_type ?? '')=='credit')>Credit</option>
              <option value="non_cash" @selected(old('payment_type', $invoice->payment_type ?? '')=='non_cash')>Non Cash</option>
            </select>
          </div>
        </div>
        <div class="row g-3 mt-2">
          <div class="col-md-2">
            <label class="form-label fw-bold">Number</label>
            <input type="number" name="number" class="form-control"
                   value="{{ old('number', $invoice->number ?? '') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Address</label>
            <input type="text" name="address" class="form-control"
                   value="{{ old('address', $invoice->address ?? '') }}">
          </div>
        </div>
      </div>
    </div>

    <div class="text-end">
      <button class="btn btn-primary">{{ isset($invoice) ? 'Update Service Order' : 'Save Service Order' }}</button>
    </div>
  </form>

  {{-- Recent Service Orders --}}
  <div class="card mt-5 shadow-sm">
    <div class="card-header">Recent Service Orders (Last 48 Hours)</div>
    <div class="card-body p-0">
      @if($history->isEmpty())
        <div class="p-4 text-center text-muted">No service orders in the past 48 hours.</div>
      @else
        <table class="table mb-0 table-hover align-middle">
          <thead>
            <tr>
              <th>Customer</th>
              <th>Vehicle</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($history as $h)
              <tr>
                <td>{{ $h->customer_display }}</td>
<td>{{ $h->vehicle_display }}</td>

                <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_',' ', $h->source_type)) }}</span></td>
                <td class="d-flex gap-2">
  <form action="{{ route('cashier.serviceorder.destroy', $h->id) }}" method="POST" onsubmit="return confirm('Delete this Service Order?')">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
      <i class="bi bi-trash"></i>
    </button>
  </form>

  <button type="button" class="btn btn-sm btn-outline-primary" title="Edit"
    onclick="openEditModal(
      {{ $h->id }},
      '{{ e($h->customer_name ?? $h->client->name) }}',
      '{{ e($h->vehicle_name ?? $h->vehicle->plate_number) }}',
      '{{ e($h->number ?? '') }}',
      '{{ e($h->address ?? $h->client->address ?? '') }}',
      '{{ e($h->plate ?? $h->vehicle->plate_number ?? '') }}',
      '{{ e($h->model ?? $h->vehicle->model ?? '') }}',
      '{{ e($h->year ?? $h->vehicle->year ?? '') }}',
      '{{ e($h->color ?? $h->vehicle->color ?? '') }}',
      '{{ e($h->odometer ?? $h->vehicle->odometer ?? '') }}',
      '{{ e($h->payment_type ?? '') }}'
    )">
    <i class="bi bi-pencil-square"></i>
  </button>

  <form method="POST" action="{{ route('cashier.serviceorder.update', $h->id) }}">
    @csrf
    @method('PUT')
    <input type="hidden" name="quick_update" value="1">
    <select name="source_type" class="form-select form-select-sm" style="width: auto;"
 onchange="quickUpdateStatus(this, {{ $h->id }})">

      <option value="service_order" {{ $h->source_type == 'service_order' ? 'selected' : '' }}>Service Order</option>
      <option value="invoicing" {{ $h->source_type == 'invoicing' ? 'selected' : '' }}>Invoicing</option>
    </select>
  </form>
</td>



              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="editForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Edit Service Order</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
  <input type="hidden" name="id" id="edit-id">
  <div class="mb-3">
    <label class="form-label">Customer</label>
    <input type="text" class="form-control" name="customer_name" id="edit-customer">
  </div>
  <div class="mb-3">
    <label class="form-label">Vehicle</label>
    <input type="text" class="form-control" name="vehicle_name" id="edit-vehicle">
  </div>
  <div class="row g-2">
    <div class="col-md-4">
      <label class="form-label">Plate</label>
      <input type="text" class="form-control" name="plate" id="edit-plate">
    </div>
    <div class="col-md-4">
      <label class="form-label">Model</label>
      <input type="text" class="form-control" name="model" id="edit-model">
    </div>
    <div class="col-md-4">
      <label class="form-label">Year</label>
      <input type="text" class="form-control" name="year" id="edit-year">
    </div>
  </div>
  <div class="row g-2 mt-2">
    <div class="col-md-4">
      <label class="form-label">Color</label>
      <input type="text" class="form-control" name="color" id="edit-color">
    </div>
    <div class="col-md-4">
      <label class="form-label">Odometer</label>
      <input type="text" class="form-control" name="odometer" id="edit-odometer">
    </div>
    <div class="col-md-4">
      <label class="form-label">Payment Type</label>
      <select class="form-select" name="payment_type" id="edit-payment">
        <option value="">— Select —</option>
        <option value="cash">Cash</option>
        <option value="debit">Debit</option>
        <option value="credit">Credit</option>
        <option value="non_cash">Non Cash</option>
      </select>
    </div>
  </div>
  <div class="row g-2 mt-2">
    <div class="col-md-6">
      <label class="form-label">Number</label>
      <input type="number" class="form-control" name="number" id="edit-number">
    </div>
    <div class="col-md-6">
      <label class="form-label">Address</label>
      <input type="text" class="form-control" name="address" id="edit-address">
    </div>
  </div>
</div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
function openEditModal(id, customer, vehicle, number, address, plate, model, year, color, odometer, payment) {
    $('#edit-id').val(id);
    $('#edit-customer').val(customer);
    $('#edit-vehicle').val(vehicle);
    $('#edit-number').val(number);
    $('#edit-address').val(address);
    $('#edit-plate').val(plate);
    $('#edit-model').val(model);
    $('#edit-year').val(year);
    $('#edit-color').val(color);
    $('#edit-odometer').val(odometer);
    $('#edit-payment').val(payment);

    let formAction = "{{ url('/cashier/serviceorder') }}/" + id;
    $('#editForm').attr('action', formAction);

    var editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editModal.show();
}

</script>

<script>
const vehicles = @json($vehicles);
$('#client_id').on('change', function() {
  const clientId = $(this).val();
  const filtered = vehicles.filter(v => v.client_id == clientId);
  $('#vehicle_id').empty().append('<option value="">— walk-in or choose —</option>');
  filtered.forEach(v => {
    $('#vehicle_id').append(`<option value="${v.id}"
      data-plate="${v.plate_number}" data-model="${v.model}" data-year="${v.year}"
      data-color="${v.color}" data-odometer="${v.odometer}">${v.plate_number}</option>`);
  });
  $('#vehicle_id').select2({ placeholder: '— walk-in or choose —', allowClear: true });
});
$('#vehicle_id').on('change', function() {
  let s = $(this).find(':selected');
  $('#plate').val(s.data('plate')||''); $('#model').val(s.data('model')||'');
  $('#year').val(s.data('year')||''); $('#color').val(s.data('color')||''); $('#odometer').val(s.data('odometer')||'');
});
</script>
<script>
$(document).ready(function() {
    // Run checks on load
    toggleFields();

    // Watch inputs
    $('input[name="customer_name"], input[name="vehicle_name"]').on('input', function() {
        toggleFields();
    });

    $('#client_id, #vehicle_id').on('change', function() {
        toggleFields();
    });

    function toggleFields() {
        let manualCustomer = $('input[name="customer_name"]').val().trim();
        let manualVehicle = $('input[name="vehicle_name"]').val().trim();
        let clientSelected = $('#client_id').val();
        let vehicleSelected = $('#vehicle_id').val();

        if (manualCustomer !== '' || manualVehicle !== '') {
            // If manual typing, hide both dropdowns
            $('#client_id').closest('.col-md-3').hide();
            $('#vehicle_id').closest('.col-md-3').hide();
            // Show manual inputs
            $('input[name="customer_name"]').closest('.col-md-3').show();
            $('input[name="vehicle_name"]').closest('.col-md-3').show();
        } else if (clientSelected || vehicleSelected) {
            // If dropdown used, hide both manual inputs
            $('input[name="customer_name"]').closest('.col-md-3').hide();
            $('input[name="vehicle_name"]').closest('.col-md-3').hide();
            // Show dropdowns
            $('#client_id').closest('.col-md-3').show();
            $('#vehicle_id').closest('.col-md-3').show();
        } else {
            // Nothing filled yet, show all
            $('#client_id').closest('.col-md-3').show();
            $('#vehicle_id').closest('.col-md-3').show();
            $('input[name="customer_name"]').closest('.col-md-3').show();
            $('input[name="vehicle_name"]').closest('.col-md-3').show();
        }
    }
});
</script>
<script>
function quickUpdateStatus(selectEl, id) {
    const form = selectEl.closest('form');
    form.submit();
    // optionally remove row immediately
    $(selectEl).closest('tr').fadeOut(300, function() { $(this).remove(); });
}
</script>


@endsection
