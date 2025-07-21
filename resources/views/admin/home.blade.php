@extends('layouts.admin')
@section('title', 'Admin Dashboard')

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
.card-dashboard .icon { font-size: 2.1rem; margin-bottom: 10px; }
.card-dashboard .count { font-size: 2.4rem; font-weight: bold; color: #222; }
.card-dashboard .label { font-size: 1.08rem; color: #555; margin-bottom: 6px; }
.card-dashboard .view-link { font-size: 0.97rem; color: #1767f2; font-weight: 500; text-decoration: none; }
.card-dashboard.sales      { border-left-color: #007bff; }
.card-dashboard.gross      { border-left-color: #28a745; }
.card-dashboard.income     { border-left-color: #6f42c1; }
.card-dashboard.discount   { border-left-color: #fd7e14; }
.card-dashboard.invoice    { border-left-color: #6c757d; }
.card-dashboard.inventory  { border-left-color: #00bcd4; }
.card-dashboard.material   { border-left-color: #795548; }
.card-dashboard.labor      { border-left-color: #ff9800; }
.card-dashboard.trends     { border-left-color: #17a2b8; }
</style>

<h2>Admin Dashboard Overview</h2>
<div class="dashboard-cards">

    <div class="card-dashboard sales">
        <div class="icon"><i class="fas fa-chart-line text-primary"></i></div>
        <div class="count">&nbsp;</div>
        <div class="label">Sales Report</div>
        <a href="{{ route('admin.sales-report') }}" class="view-link">View All &rarr;</a>
    </div>

    <div class="card-dashboard gross">
        <div class="icon"><i class="fas fa-coins text-success"></i></div>
        <div class="count">&nbsp;</div>
        <div class="label">Gross Sales Report</div>
        <a href="{{ route('admin.gross-sales-report') }}" class="view-link">View All &rarr;</a>
    </div>

    <div class="card-dashboard income">
        <div class="icon"><i class="fas fa-chart-pie" style="color:#6f42c1"></i></div>
        <div class="count">&nbsp;</div>
        <div class="label">Income Analysis</div>
        <a href="{{ route('admin.income-analysis-report') }}" class="view-link">View All &rarr;</a>
    </div>

    <div class="card-dashboard discount">
        <div class="icon"><i class="fas fa-percent" style="color:#fd7e14"></i></div>
        <div class="count">&nbsp;</div>
        <div class="label">Discount Report</div>
        <a href="{{ route('admin.discount-report') }}" class="view-link">View All &rarr;</a>
    </div>

    <!-- New Cards Below -->

    <div class="card-dashboard invoice">
        <div class="icon"><i class="fas fa-file-invoice" style="color:#6c757d"></i></div>
        <div class="count">&nbsp;</div>
        <div class="label">Invoices</div>
        <a href="{{ route('admin.invoices') }}" class="view-link">View All &rarr;</a>
    </div>

    <div class="card-dashboard inventory">
        <div class="icon"><i class="fas fa-warehouse" style="color:#00bcd4"></i></div>
        <div class="count">&nbsp;</div>
        <div class="label">Inventory</div>
        <a href="{{ route('admin.inventory') }}" class="view-link">View All &rarr;</a>
    </div>

    <div class="card-dashboard material">
        <div class="icon"><i class="fas fa-clipboard-list" style="color:#795548"></i></div>
        <div class="count">&nbsp;</div>
        <div class="label">Material Summary</div>
        <a href="{{ route('admin.material-summary') }}" class="view-link">View All &rarr;</a>
    </div>

    <div class="card-dashboard labor">
        <div class="icon"><i class="fas fa-users" style="color:#ff9800"></i></div>
        <div class="count">&nbsp;</div>
        <div class="label">Labor Summary</div>
        <a href="{{ route('admin.labor-summary') }}" class="view-link">View All &rarr;</a>
    </div>

    <div class="card-dashboard trends">
        <div class="icon"><i class="fas fa-chart-line" style="color:#17a2b8"></i></div>
        <div class="count">&nbsp;</div>
        <div class="label">Trends</div>
        <a href="{{ route('admin.trends') }}" class="view-link">View All &rarr;</a>
    </div>

</div>
@endsection
