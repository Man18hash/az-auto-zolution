@extends('layouts.cashier')
@section('title', 'Expenses')

@section('content')
<div class="container">
    <h2 class="fw-bold mt-2 mb-4">Expenses</h2>

    <!-- Add Expense Button -->
    <button class="btn btn-success mb-3 fw-bold" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
        <i class="fas fa-plus"></i> Add Expense
    </button>

    <!-- Add Expense Modal -->
    <div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form method="POST" action="{{ route('cashier.expenses.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addExpenseModalLabel">Add Expense</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="expense_date" class="form-label fw-semibold">Date</label>
                        <input type="date" name="date" id="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="expense_title" class="form-label fw-semibold">Title</label>
                        <input type="text" name="title" id="expense_title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="expense_amount" class="form-label fw-semibold">Amount (₱)</label>
                        <input type="number" name="amount" id="expense_amount" class="form-control" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
      </div>
    </div>

    <!-- History Table -->
    <div class="table-responsive bg-white shadow-sm rounded p-2">
        @php
            $grouped = $expenses->groupBy(function($e) { return \Carbon\Carbon::parse($e->date)->format('F d, Y'); });
        @endphp
        @forelse($grouped as $date => $expenseList)
            <h5 class="mt-4 mb-2 text-primary">{{ $date }}</h5>
            <table class="table table-bordered align-middle mb-3">
                <thead class="table-light">
                    <tr>
                        <th style="min-width:220px;">Title</th>
                        <th style="min-width:100px;">Amount</th>
                        <th style="min-width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenseList as $expense)
                        <tr>
                            <td>{{ $expense->title }}</td>
                            <td>₱{{ number_format($expense->amount, 2) }}</td>
                            <td>
                                <!-- Edit (Modal) -->
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editExpenseModal{{ $expense->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <!-- Delete -->
                                <form method="POST" action="{{ route('cashier.expenses.destroy', $expense->id) }}"
                                    style="display:inline-block;"
                                    onsubmit="return confirm('Delete this expense?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                                <!-- Edit Modal -->
                                <div class="modal fade" id="editExpenseModal{{ $expense->id }}" tabindex="-1" aria-labelledby="editExpenseModalLabel{{ $expense->id }}" aria-hidden="true">
                                  <div class="modal-dialog">
                                    <form method="POST" action="{{ route('cashier.expenses.update', $expense->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning text-dark">
                                                <h5 class="modal-title" id="editExpenseModalLabel{{ $expense->id }}">Edit Expense</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Date</label>
                                                    <input type="date" name="date" class="form-control" value="{{ $expense->date }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Title</label>
                                                    <input type="text" name="title" class="form-control" value="{{ $expense->title }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Amount (₱)</label>
                                                    <input type="number" name="amount" class="form-control" value="{{ $expense->amount }}" min="0" step="0.01" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-warning">Update</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
            <div class="alert alert-info mt-4">No expenses recorded.</div>
        @endforelse
    </div>
</div>
@endsection
