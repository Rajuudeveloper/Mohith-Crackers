<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Estimate;
use App\Models\Customer;
use App\Models\AgentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AgentTransactionsController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $agentId = $request->query('agent_id');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        // Get all agents for dropdown
        $agents = Agent::orderBy('id')->get();

        // Query 1: Estimates data
        $estimatesQuery = Estimate::select(
            DB::raw("'Estimate' as type"),
            'estimates.id',
            'estimates.estimate_no as reference',
            DB::raw("customers.agent_id"),
            'estimates.grand_total as amount',
            'estimates.estimate_date as date',
            DB::raw("NULL as payment_mode"),
            DB::raw("'estimate' as transaction_type"),
            DB::raw("1 as sort_order") // Add sort order: 1 for estimates (first)
        )
            ->join('customers', 'estimates.customer_id', '=', 'customers.id')
            ->when($agentId, function ($query) use ($agentId) {
                return $query->where('customers.agent_id', $agentId);
            })
            ->when($fromDate, function ($query) use ($fromDate) {
                return $query->whereDate('estimates.estimate_date', '>=', $fromDate);
            })
            ->when($toDate, function ($query) use ($toDate) {
                return $query->whereDate('estimates.estimate_date', '<=', $toDate);
            });

        // Query 2: Collections data
        $collectionsQuery = AgentCollection::select(
            DB::raw("'Collection' as type"),
            'agent_collections.id',
            DB::raw("'-' as reference"),
            'agent_collections.agent_id',
            'agent_collections.amount',
            'agent_collections.payment_date as date',
            DB::raw("CASE 
                WHEN payment_mode = 1 THEN 'Cash'
                WHEN payment_mode = 2 THEN 'AXIS Bank'
                WHEN payment_mode = 3 THEN 'HDFC Bank'
                WHEN payment_mode = 4 THEN 'SBI Bank'
                WHEN payment_mode = 5 THEN 'Cheque'
                WHEN payment_mode = 6 THEN 'UPI'
                ELSE 'Unknown'
            END as payment_mode"),
            DB::raw("'collection' as transaction_type"),
            DB::raw("2 as sort_order") // Add sort order: 2 for collections (second)
        )
            ->when($agentId, function ($query) use ($agentId) {
                return $query->where('agent_id', $agentId);
            })
            ->when($fromDate, function ($query) use ($fromDate) {
                return $query->whereDate('payment_date', '>=', $fromDate);
            })
            ->when($toDate, function ($query) use ($toDate) {
                return $query->whereDate('payment_date', '<=', $toDate);
            });

        // Combine both queries with UNION ALL
        $transactionsQuery = $estimatesQuery->unionAll($collectionsQuery);

        // Get the combined results with pagination and proper sorting
        $transactions = DB::query()
            ->fromSub($transactionsQuery, 'combined')
            ->orderBy('date', 'desc')
            ->orderBy('sort_order', 'asc') // Estimates (1) first, then Collections (2)
            ->orderBy('id', 'desc') // Additional sorting for consistent ordering
            ->paginate(20);

        // Calculate statistics
        $totalEstimates = Estimate::join('customers', 'estimates.customer_id', '=', 'customers.id')
            ->when($agentId, function ($query) use ($agentId) {
                return $query->where('customers.agent_id', $agentId);
            })
            ->when($fromDate, function ($query) use ($fromDate) {
                return $query->whereDate('estimates.estimate_date', '>=', $fromDate);
            })
            ->when($toDate, function ($query) use ($toDate) {
                return $query->whereDate('estimates.estimate_date', '<=', $toDate);
            })
            ->sum('estimates.grand_total');

        $totalCollections = AgentCollection::query()
            ->when($agentId, function ($query) use ($agentId) {
                return $query->where('agent_id', $agentId);
            })
            ->when($fromDate, function ($query) use ($fromDate) {
                return $query->whereDate('payment_date', '>=', $fromDate);
            })
            ->when($toDate, function ($query) use ($toDate) {
                return $query->whereDate('payment_date', '<=', $toDate);
            })
            ->sum('amount');

        $netBalance = $totalEstimates - $totalCollections;

        return view('reports.index', compact(
            'transactions',
            'agents',
            'agentId',
            'fromDate',
            'toDate',
            'totalEstimates',
            'totalCollections',
            'netBalance'
        ));
    }
    public function pdf(Request $request)
    {
        // Get filter parameters (same logic as index)
        $agentId = $request->query('agent_id');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $download = $request->query('download', false);

        // Get agent name if filtered
        $agentName = 'All Agents';
        if ($agentId) {
            $agent = Agent::find($agentId);
            $agentName = $agent ? $agent->name : 'Unknown Agent';
        }

        // Query 1: Estimates data (same as index)
        $estimatesQuery = Estimate::select(
            DB::raw("'Estimate' as type"),
            'estimates.id',
            'estimates.estimate_no as reference',
            DB::raw("customers.agent_id"),
            'estimates.grand_total as amount',
            'estimates.estimate_date as date',
            DB::raw("NULL as payment_mode"),
            DB::raw("1 as sort_order")
        )
            ->join('customers', 'estimates.customer_id', '=', 'customers.id')
            ->when($agentId, function ($query) use ($agentId) {
                return $query->where('customers.agent_id', $agentId);
            })
            ->when($fromDate, function ($query) use ($fromDate) {
                return $query->whereDate('estimates.estimate_date', '>=', $fromDate);
            })
            ->when($toDate, function ($query) use ($toDate) {
                return $query->whereDate('estimates.estimate_date', '<=', $toDate);
            });

        // Query 2: Collections data (same as index)
        $collectionsQuery = AgentCollection::select(
            DB::raw("'Collection' as type"),
            'agent_collections.id',
            DB::raw("'-' as reference"),
            'agent_collections.agent_id',
            'agent_collections.amount',
            'agent_collections.payment_date as date',
            DB::raw("CASE 
                WHEN payment_mode = 1 THEN 'Cash'
                WHEN payment_mode = 2 THEN 'AXIS Bank'
                WHEN payment_mode = 3 THEN 'HDFC Bank'
                WHEN payment_mode = 4 THEN 'SBI Bank'
                WHEN payment_mode = 5 THEN 'Cheque'
                WHEN payment_mode = 6 THEN 'UPI'
                ELSE 'Unknown'
            END as payment_mode"),
            DB::raw("2 as sort_order")
        )
            ->when($agentId, function ($query) use ($agentId) {
                return $query->where('agent_id', $agentId);
            })
            ->when($fromDate, function ($query) use ($fromDate) {
                return $query->whereDate('payment_date', '>=', $fromDate);
            })
            ->when($toDate, function ($query) use ($toDate) {
                return $query->whereDate('payment_date', '<=', $toDate);
            });

        // Combine queries and get all results (no pagination for PDF)
        $transactionsQuery = $estimatesQuery->unionAll($collectionsQuery);
        $transactions = DB::query()
            ->fromSub($transactionsQuery, 'combined')
            ->orderBy('date', 'desc')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        // Calculate statistics (same as index)
        $totalEstimates = Estimate::join('customers', 'estimates.customer_id', '=', 'customers.id')
            ->when($agentId, function ($query) use ($agentId) {
                return $query->where('customers.agent_id', $agentId);
            })
            ->when($fromDate, function ($query) use ($fromDate) {
                return $query->whereDate('estimates.estimate_date', '>=', $fromDate);
            })
            ->when($toDate, function ($query) use ($toDate) {
                return $query->whereDate('estimates.estimate_date', '<=', $toDate);
            })
            ->sum('estimates.grand_total');

        $totalCollections = AgentCollection::query()
            ->when($agentId, function ($query) use ($agentId) {
                return $query->where('agent_id', $agentId);
            })
            ->when($fromDate, function ($query) use ($fromDate) {
                return $query->whereDate('payment_date', '>=', $fromDate);
            })
            ->when($toDate, function ($query) use ($toDate) {
                return $query->whereDate('payment_date', '<=', $toDate);
            })
            ->sum('amount');

        $netBalance = $totalEstimates - $totalCollections;

        // Format dates for display
        $formattedFromDate = $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d-m-Y') : 'N/A';
        $formattedToDate = $toDate ? \Carbon\Carbon::parse($toDate)->format('d-m-Y') : 'N/A';

        // Get all agents for footer if needed
        $agents = Agent::orderBy('name')->get();

        $data = [
            'transactions' => $transactions,
            'agentName' => $agentName,
            'fromDate' => $formattedFromDate,
            'toDate' => $formattedToDate,
            'totalEstimates' => $totalEstimates,
            'totalCollections' => $totalCollections,
            'netBalance' => $netBalance,
            'agents' => $agents,
            'dateRange' => $formattedFromDate . ' to ' . $formattedToDate,
            'generatedDate' => now()->format('d-m-Y H:i:s'),
        ];

        $pdf = Pdf::loadView('reports.pdf.agent_transactions', $data);

        if ($download) {
            return $pdf->download('agent-transactions-report-' . now()->format('Y-m-d') . '.pdf');
        }

        return $pdf->stream('agent-transactions-report.pdf');
    }
}
