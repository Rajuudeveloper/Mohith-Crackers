<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;


class AgentsTransactionReport extends Page
{
    use InteractsWithForms;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;
    // protected static string|BackedEnum|null $navigationGroup = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Agents Transaction Report';
    protected static ?string $title = 'Agents Transaction Report';
    protected string $view = 'filament.pages.agents-transaction-report';

    // Filters
    public ?int $agent_id = null;
    public ?string $transaction_type = null;
    public ?string $from_date = null;
    public ?string $to_date = null;

    // Data
    public Collection $transactions;

    public function mount(): void
    {
        $this->transactions = collect();
        $this->form->fill(); // optional: initialize form
    }

    public function submit(): void
    {
        $this->transactions = $this->getTransactions();
    }

    protected function getTransactions(): Collection
    {
        // Base query for estimates (Debit)
        $estimateQuery = DB::table('estimates')
            ->select([
                'estimates.created_at as date',
                'agents.id as agent_id',
                'agents.name as agent_name',
                DB::raw("'Estimate' as transaction_type"),
                DB::raw('estimates.id as reference'),
                'customers.name as customer_name',
                DB::raw('estimates.grand_total as debit'),
                DB::raw('0 as credit'),
                DB::raw('NULL as notes'),
            ])
            ->join('customers', 'customers.id', '=', 'estimates.customer_id')
            ->join('agents', 'agents.id', '=', 'customers.agent_id');

        // Base query for collections (Credit)
        $collectionQuery = DB::table('agent_collections')
            ->select([
                'agent_collections.payment_date as date',
                'agents.id as agent_id',
                'agents.name as agent_name',
                DB::raw("'Collection' as transaction_type"),
                'agent_collections.payment_mode as reference',
                DB::raw('NULL as customer_name'),
                DB::raw('0 as debit'),
                'agent_collections.amount as credit',
                'agent_collections.notes as notes',
            ])
            ->join('agents', 'agents.id', '=', 'agent_collections.agent_id');

        // Apply filters
        if ($this->agent_id) {
            $estimateQuery->where('agents.id', $this->agent_id);
            $collectionQuery->where('agents.id', $this->agent_id);
        }

        if ($this->from_date) {
            $estimateQuery->whereDate('estimates.created_at', '>=', $this->from_date);
            $collectionQuery->whereDate('agent_collections.payment_date', '>=', $this->from_date);
        }

        if ($this->to_date) {
            $estimateQuery->whereDate('estimates.created_at', '<=', $this->to_date);
            $collectionQuery->whereDate('agent_collections.payment_date', '<=', $this->to_date);
        }

        if ($this->transaction_type === 'Estimate') {
            $collectionQuery->whereRaw('1 = 0'); // exclude collections
        }

        if ($this->transaction_type === 'Collection') {
            $estimateQuery->whereRaw('1 = 0'); // exclude estimates
        }

        // Combine both queries using UNION ALL
        $allTransactions = $estimateQuery->unionAll($collectionQuery)
            ->orderBy('date')
            ->orderBy('agent_id')
            ->get();

        // Calculate running balance
        $runningBalances = [];
        $balances = [];
        foreach ($allTransactions as $transaction) {
            $agentId = $transaction->agent_id;

            if (!isset($balances[$agentId])) {
                // Set opening balance
                $agent = DB::table('agents')->find($agentId);
                $balances[$agentId] = $agent->cr_dr === 'Dr' ? $agent->opening_balance : -$agent->opening_balance;
            }

            $balances[$agentId] += $transaction->debit - $transaction->credit;
            $transaction->running_balance = $balances[$agentId];

            $runningBalances[] = $transaction;
        }

        return collect($runningBalances);
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('agent_id')
                ->label('Agent')
                ->options(DB::table('agents')->pluck('name', 'id')->toArray())
                ->searchable()
                ->placeholder('All Agents'),

            Select::make('transaction_type')
                ->label('Transaction Type')
                ->options([
                    'Estimate' => 'Estimate',
                    'Collection' => 'Collection',
                ])
                ->placeholder('All Types'),

            DatePicker::make('from_date')->label('From Date'),
            DatePicker::make('to_date')->label('To Date'),
        ];
    }
}
