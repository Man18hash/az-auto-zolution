@if($results->isEmpty())
  <div class="alert alert-info">No invoices found.</div>
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
      @foreach($results as $h)
      <tr>
      <td>{{ $h->client->name ?? $h->customer_name }}</td>
      <td>{{ $h->vehicle->plate_number ?? $h->vehicle_name }}</td>
      <td>
      <span
      class="badge bg-{{ $h->payment_type === 'cash' ? 'success' : ($h->payment_type === 'credit' ? 'primary' : 'secondary') }}">
      {{ ucfirst(str_replace('_', ' ', $h->payment_type)) }}
      </span>
      </td>
      <td><span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $h->service_status)) }}</span></td>
      <td>
      <span
      class="badge bg-{{ $h->status === 'paid' ? 'success' : ($h->status === 'unpaid' ? 'warning text-dark' : 'secondary') }}">
      {{ ucfirst($h->status) }}
      </span>
      </td>
      <td class="text-=end">
      <a href="{{ route('cashier.invoice.view', $h->id) }}" class="btn btn-sm btn-outline-info"
      title="View Invoice">
      <i class="bi bi-eye"></i>
      </a>
      <a href="{{ route('cashier.invoice.edit', $h->id) }}?modal=1" class="btn btn-sm btn-outline-primary"
      data-bs-toggle="tooltip" title="Edit Invoice">
      <i class="bi bi-pencil-square"></i>
      </a>
      </td>
      </tr>
    @endforeach
    </tbody>
    </table>
    <div class="d-flex justify-content-center my-4">
    {{ $results->links() }}
    </div>
  </div>
@endif