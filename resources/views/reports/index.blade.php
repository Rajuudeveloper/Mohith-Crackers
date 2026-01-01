<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Transactions Report</title>

    <!-- Bootstrap 5 for styling and pagination -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Select2 for searchable dropdown -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <!-- daterangepicker -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="{{ asset('css/agent_transactions.css') }}">
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="card-title">
                        <i class="bi bi-cash-stack"></i>
                        Agent Transactions Report
                    </h1>
                    <a href="/admin" class="btn btn-danger">
                        <i class="bi bi-arrow-left"></i>
                        Back
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Filter Section -->
                <form method="GET" action="{{ route('reports.agenttransactions') }}" class="filter-section">
                    <div class="filter-row">
                        <!-- Agent Filter -->
                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="bi bi-person"></i>
                                Filter by Agent
                            </label>
                            <select name="agent_id" class="form-select select2-agent" id="agent-select">
                                <option value="">All Agents</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}"
                                        {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date Range Filter -->
                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="bi bi-calendar"></i>
                                Date Range
                            </label>
                            <div class="date-inputs">
                                <input type="text" name="date_range" class="form-control" id="date-range-picker"
                                    value="{{ $fromDate && $toDate ? $fromDate . ' to ' . $toDate : '' }}"
                                    placeholder="Select date range" readonly>
                                <input type="hidden" name="from_date" id="from-date" value="{{ $fromDate ?? '' }}">
                                <input type="hidden" name="to_date" id="to-date" value="{{ $toDate ?? '' }}">
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="filter-group">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-funnel"></i>
                                    Apply Filters
                                </button>
                                <a href="{{ route('reports.agenttransactions') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i>
                                    Clear Filters
                                </a>

                            </div>
                        </div>

                    </div>
                    <div class="pdf_section" style="padding-top: 10px;">
                        <button type="button" class="btn btn-info" id="print-btn">
                            <i class="bi bi-printer"></i>
                            Print
                        </button>
                        <a href="#" class="btn btn-success" id="view-pdf-btn">
                            <i class="bi bi-eye"></i>
                            View PDF
                        </a>
                        <a href="#" class="btn btn-warning" id="download-pdf-btn">
                            <i class="bi bi-download"></i>
                            Download PDF
                        </a>
                    </div>
                </form>

                @if ($transactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Agent</th>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th>Payment Mode</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transactions as $transaction)
                                    @php
                                        // Get agent name (you'll need to adjust this based on your actual data structure)
$agentName = App\Models\Agent::find($transaction->agent_id)->name ?? 'N/A';

// Format date
$formattedDate = \Carbon\Carbon::parse($transaction->date)->format('d-m-Y');

// Determine type class
$typeClass =
    $transaction->type == 'Estimate' ? 'type-estimate' : 'type-collection';

// Format amount
$formattedAmount = '₹' . number_format($transaction->amount, 2);
                                    @endphp
                                    <tr>
                                        <td>{{ $formattedDate }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <i class="bi bi-person-circle"></i>
                                                </div>
                                                <div>
                                                    <div>{{ $agentName }}</div>
                                                    
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($transaction->type == 'Estimate')
                                                <span class="badge badge-warning">Estimate</span>
                                            @else
                                                <span class="badge badge-success">Collection</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $transaction->reference }}</strong>
                                           
                                        </td>
                                        <td>
                                            @if ($transaction->type == 'Collection' && $transaction->payment_mode)
                                                <span class="payment-mode-badge">
                                                    {{ ucfirst($transaction->payment_mode) }}
                                                </span>
                                            @else
                                                <span class="text-muted">–</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong
                                                class="{{ $transaction->type == 'Collection' ? 'amount-positive' : 'type-estimate' }}">
                                                {{ $formattedAmount }}
                                            </strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Statistics -->
                    <div class="stats-bar">
                        <div class="stats-item">
                            <div class="stats-value positive">₹{{ number_format($totalEstimates, 2) }}</div>
                            <div class="stats-label">Total Estimates</div>
                        </div>
                        <div class="stats-item">
                            <div class="stats-value positive">₹{{ number_format($totalCollections, 2) }}</div>
                            <div class="stats-label">Total Collections</div>
                        </div>
                        <div class="stats-item">
                            <div class="stats-value {{ $netBalance >= 0 ? 'positive' : 'negative' }}">
                                ₹{{ number_format(abs($netBalance), 2) }}
                                @if ($netBalance < 0)
                                    <small style="font-size: 12px;">(Due)</small>
                                @endif
                            </div>
                            <div class="stats-label">Net Balance</div>
                        </div>
                        <div class="stats-item">
                            <div class="stats-value">{{ $transactions->total() }}</div>
                            <div class="stats-label">Total Records</div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0">
                                {{-- Previous Page Link --}}
                                @if ($transactions->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            <i class="bi bi-chevron-left"></i>
                                        </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="{{ $transactions->appends([
                                                    'agent_id' => $agentId,
                                                    'from_date' => $fromDate,
                                                    'to_date' => $toDate,
                                                ])->previousPageUrl() }}"
                                            rel="prev">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                @endif

                                {{-- Pagination Numbers --}}
                                @php
                                    $current = $transactions->currentPage();
                                    $last = $transactions->lastPage();
                                    $start = max($current - 2, 1);
                                    $end = min($current + 2, $last);
                                @endphp

                                {{-- First Page --}}
                                @if ($start > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $transactions->url(1) }}">1</a>
                                    </li>
                                    @if ($start > 2)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                @endif

                                {{-- Page Numbers --}}
                                @for ($page = $start; $page <= $end; $page++)
                                    @if ($page == $current)
                                        <li class="page-item active">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link"
                                                href="{{ $transactions->url($page) }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endfor

                                {{-- Last Page --}}
                                @if ($end < $last)
                                    @if ($end < $last - 1)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="{{ $transactions->url($last) }}">{{ $last }}</a>
                                    </li>
                                @endif

                                {{-- Next Page Link --}}
                                @if ($transactions->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="{{ $transactions->appends([
                                                    'agent_id' => $agentId,
                                                    'from_date' => $fromDate,
                                                    'to_date' => $toDate,
                                                ])->nextPageUrl() }}"
                                            rel="next">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            <i class="bi bi-chevron-right"></i>
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="bi bi-clipboard-x"></i>
                        </div>
                        <h4 class="empty-text">No transactions found</h4>
                        <p class="text-muted">Try adjusting your filters or check back later.</p>
                        <a href="{{ route('reports.agenttransactions') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-arrow-clockwise"></i>
                            Reset Filters
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>


    @include('reports.Js.index_js')
</body>

</html>
