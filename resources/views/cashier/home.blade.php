@extends('layouts.cashier')
@section('title', 'Cashier Dashboard')

@section('content')
<style>
.dashboard-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 22px;
    margin-top: 20px;
}
.card-dashboard {
    flex: 1 1 230px;
    background: #fff;
    border-radius: 12px;
    padding: 26px 22px 18px 22px;
    box-shadow: 0 4px 16px 0 rgba(0,0,0,0.07);
    min-width: 210px;
    max-width: 250px;
    min-height: 125px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border-left: 7px solid #ffc107;
    margin-bottom: 14px;
}
.card-dashboard .icon { font-size: 2.1rem; margin-bottom: 10px;}
.card-dashboard .count { font-size: 2.4rem; font-weight: bold; color: #222;}
.card-dashboard .label { font-size: 1.08rem; color: #555; margin-bottom: 6px;}
.card-dashboard .view-link { font-size: 0.97rem; color: #1767f2; font-weight: 500; text-decoration: none;}
.card-dashboard.invoicing  { border-left-color: #007bff; }
.card-dashboard.quotation  { border-left-color: #ffc107; }
.card-dashboard.appointment{ border-left-color: #28a745; }
.card-dashboard.history    { border-left-color: #6f42c1; }
.card-dashboard.inventory  { border-left-color: #343a40; }
.card-dashboard.service    { border-left-color: #fd7e14; }
</style>

<h2>Dashboard Overview</h2>
<div class="dashboard-cards">

    <div class="card-dashboard quotation">
        <div class="icon"><i class="fas fa-file-alt text-warning"></i></div>
        <div class="count">{{ $quotationCount ?? 0 }}</div>
        <div class="label">Quotation</div>
        <a href="{{ route('cashier.quotation.index') }}" class="view-link">View All &rarr;</a>
    </div>

    <div class="card-dashboard invoicing">
        <div class="icon"><i class="fas fa-file-invoice-dollar text-primary"></i></div>
        <div class="count">{{ $invoicingCount ?? 0 }}</div>
        <div class="label">Invoicing</div>
        <a href="{{ route('cashier.invoice.index') }}" class="view-link">View All &rarr;</a>
    </div>

    <div class="card-dashboard appointment">
        <div class="icon"><i class="fas fa-calendar-check text-success"></i></div>
        <div class="count">{{ $appointmentCount ?? 0 }}</div>
        <div class="label">Appointment</div>
        <a href="{{ route('cashier.appointment.index') }}" class="view-link">View All &rarr;</a>
    </div>

    <div class="card-dashboard history">
        <div class="icon"><i class="fas fa-history" style="color:#6f42c1"></i></div>
        <div class="count">{{ $historyCount ?? 0 }}</div>
        <div class="label">History</div>
        <a href="{{ route('cashier.history') }}" class="view-link">View All &rarr;</a>
    </div>

    <div class="card-dashboard inventory">
        <div class="icon"><i class="fas fa-boxes text-dark"></i></div>
        <div class="count">{{ $inventoryCount ?? 0 }}</div>
        <div class="label">Inventory</div>
        <a href="{{ route('cashier.inventory.index') }}" class="view-link">View All &rarr;</a>
    </div>

    <div class="card-dashboard service">
        <div class="icon"><i class="fas fa-tools" style="color:#fd7e14"></i></div>
        <div class="count">{{ $serviceOrderCount ?? 0 }}</div>
        <div class="label">Service Orders</div>
        <a href="{{ route('cashier.service-order') }}" class="view-link">View All &rarr;</a>
    </div>
</div>
@endsection
