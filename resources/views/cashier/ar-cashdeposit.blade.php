@extends('layouts.cashier')
@section('title', 'A/R Collection & Cash Deposit')

@section('content')
<div class="container-fluid">
    <h2 class="fw-bold mt-2 mb-4">A/R Collection & Cash Deposit</h2>
    <div class="row g-4">
        <!-- A/R Collection Section -->
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-money-bill-wave me-2"></i> A/R Collection</span>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addARModal">
                        <i class="fas fa-plus"></i> Add A/R
                    </button>
                </div>
                <div class="card-body">
                    <!-- Add AR Modal -->
                    <div class="modal fade" id="addARModal" tabindex="-1" aria-labelledby="addARModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('cashier.ar-cashdeposit.storeAR') }}">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="addARModalLabel">Add A/R Collection</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Date</label>
                                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <input type="text" name="description" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Amount (₱)</label>
                                            <input type="number" name="amount" class="form-control" min="0" step="0.01" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-primary" type="submit">Save</button>
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @php
                        $arGrouped = $arCollections->groupBy(fn($ar) => \Carbon\Carbon::parse($ar->date)->format('F d, Y'));
                    @endphp
                    @forelse($arGrouped as $date => $arList)
                        <h6 class="mt-4 text-primary">{{ $date }}</h6>
                        <table class="table table-bordered align-middle mb-3">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th style="width:115px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($arList as $ar)
                                    <tr>
                                        <td>{{ $ar->description }}</td>
                                        <td>₱{{ number_format($ar->amount, 2) }}</td>
                                        <td>
                                            <!-- Edit Button -->
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editARModal{{ $ar->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <!-- Delete -->
                                            <form method="POST" action="{{ route('cashier.ar-cashdeposit.destroyAR', $ar->id) }}"
                                                  style="display:inline-block;"
                                                  onsubmit="return confirm('Delete this A/R?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                            </form>
                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editARModal{{ $ar->id }}" tabindex="-1" aria-labelledby="editARModalLabel{{ $ar->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form method="POST" action="{{ route('cashier.ar-cashdeposit.updateAR', $ar->id) }}">
                                                        @csrf @method('PUT')
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-warning">
                                                                <h5 class="modal-title" id="editARModalLabel{{ $ar->id }}">Edit A/R</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Date</label>
                                                                    <input type="date" name="date" class="form-control" value="{{ $ar->date }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Description</label>
                                                                    <input type="text" name="description" class="form-control" value="{{ $ar->description }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Amount (₱)</label>
                                                                    <input type="number" name="amount" class="form-control" value="{{ $ar->amount }}" min="0" step="0.01" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button class="btn btn-warning" type="submit">Update</button>
                                                                <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @empty
                        <div class="alert alert-info mt-4">No A/R collections recorded.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <!-- Cash Deposit Section -->
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-piggy-bank me-2"></i> Cash Deposit</span>
                    <button class="btn btn-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addCashDepositModal">
                        <i class="fas fa-plus"></i> Add Deposit
                    </button>
                </div>
                <div class="card-body">
                    <!-- Add Cash Deposit Modal -->
                    <div class="modal fade" id="addCashDepositModal" tabindex="-1" aria-labelledby="addCashDepositModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('cashier.ar-cashdeposit.storeCashDeposit') }}">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title" id="addCashDepositModalLabel">Add Cash Deposit</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Date</label>
                                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <input type="text" name="description" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Amount (₱)</label>
                                            <input type="number" name="amount" class="form-control" min="0" step="0.01" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-success" type="submit">Save</button>
                                        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @php
                        $cdGrouped = $cashDeposits->groupBy(fn($cd) => \Carbon\Carbon::parse($cd->date)->format('F d, Y'));
                    @endphp
                    @forelse($cdGrouped as $date => $cdList)
                        <h6 class="mt-4 text-success">{{ $date }}</h6>
                        <table class="table table-bordered align-middle mb-3">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th style="width:115px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cdList as $cd)
                                    <tr>
                                        <td>{{ $cd->description }}</td>
                                        <td>₱{{ number_format($cd->amount, 2) }}</td>
                                        <td>
                                            <!-- Edit Button -->
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editCashDepositModal{{ $cd->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <!-- Delete -->
                                            <form method="POST" action="{{ route('cashier.ar-cashdeposit.destroyCashDeposit', $cd->id) }}"
                                                  style="display:inline-block;"
                                                  onsubmit="return confirm('Delete this deposit?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                            </form>
                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editCashDepositModal{{ $cd->id }}" tabindex="-1" aria-labelledby="editCashDepositModalLabel{{ $cd->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form method="POST" action="{{ route('cashier.ar-cashdeposit.updateCashDeposit', $cd->id) }}">
                                                        @csrf @method('PUT')
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-warning">
                                                                <h5 class="modal-title" id="editCashDepositModalLabel{{ $cd->id }}">Edit Cash Deposit</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Date</label>
                                                                    <input type="date" name="date" class="form-control" value="{{ $cd->date }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Description</label>
                                                                    <input type="text" name="description" class="form-control" value="{{ $cd->description }}" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Amount (₱)</label>
                                                                    <input type="number" name="amount" class="form-control" value="{{ $cd->amount }}" min="0" step="0.01" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button class="btn btn-warning" type="submit">Update</button>
                                                                <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @empty
                        <div class="alert alert-info mt-4">No cash deposits recorded.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
