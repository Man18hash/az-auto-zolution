@extends('layouts.cashier')

@section('title', 'Invoice/Quotation History')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 text-center">Invoice & Quotation History</h2>

    {{-- Search Bar --}}
    <form method="GET" action="{{ route('cashier.history') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by customer, plate, status, etc." value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Search</button>
        </div>
    </form>

    @php
        // Group invoices by date (format: Y-m-d)
        $grouped = $history->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->created_at)->format('F d, Y');
        });
        $badgeClass = [
            'quotation'    => 'bg-warning text-dark',
            'cancelled'    => 'bg-danger',
            'appointment'  => 'bg-info text-dark',
            'service_order'=> 'bg-secondary',
            'invoicing'    => 'bg-success text-white'
        ];
        $statusBadge = [
            'unpaid' => 'bg-secondary',
            'paid' => 'bg-success text-white',
            'cancelled' => 'bg-danger',
            'voided' => 'bg-dark text-white'
        ];
    @endphp

    @if($history->isEmpty())
        <p>No records found.</p>
    @else
        @foreach($grouped as $date => $records)
            <h4 class="mt-4">{{ $date }}</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Source Type</th>
                        <th>Payment Type</th>
                        <th>Service Status</th>
                        <th>Status</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $h)
                        <tr>
                            <td>{{ $h->client->name ?? $h->customer_name }}</td>
                            <td>{{ $h->vehicle->plate_number ?? $h->vehicle_name }}</td>
                            <td>
                                <span class="badge {{ $badgeClass[$h->source_type] ?? 'bg-secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $h->source_type)) }}
                                </span>
                            </td>
                            <td>{{ ucfirst(str_replace('_',' ', $h->payment_type)) }}</td>
                            <td>{{ ucfirst(str_replace('_',' ', $h->service_status)) }}</td>
                            <td>
                                <span class="badge {{ $statusBadge[$h->status] ?? 'bg-secondary' }}">
                                    {{ ucfirst($h->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('cashier.history.view', $h->id) }}" class="btn btn-sm btn-info" target="_blank">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endif
</div>
@endsection
