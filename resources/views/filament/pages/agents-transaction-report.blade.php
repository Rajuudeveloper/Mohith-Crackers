<x-filament::page>
    <form wire:submit.prevent="submit" class="space-y-4 p-4 bg-white rounded-lg shadow">
        {{ $this->form }}

        <div class="flex justify-end">
            <x-filament::button type="submit">Generate Report</x-filament::button>
        </div>
    </form>

    @if($transactions->isNotEmpty())
        <div class="overflow-x-auto mt-6">
            <table class="w-full table-auto border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="border px-2 py-1">Date</th>
                        <th class="border px-2 py-1">Agent</th>
                        <th class="border px-2 py-1">Transaction</th>
                        <th class="border px-2 py-1">Reference</th>
                        <th class="border px-2 py-1">Customer</th>
                        <th class="border px-2 py-1">Debit</th>
                        <th class="border px-2 py-1">Credit</th>
                        <th class="border px-2 py-1">Running Balance</th>
                        <th class="border px-2 py-1">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $tx)
                        <tr>
                            <td class="border px-2 py-1">{{ \Carbon\Carbon::parse($tx->date)->format('d-m-Y') }}</td>
                            <td class="border px-2 py-1">{{ $tx->agent_name }}</td>
                            <td class="border px-2 py-1">{{ $tx->transaction_type }}</td>
                            <td class="border px-2 py-1">{{ $tx->reference }}</td>
                            <td class="border px-2 py-1">{{ $tx->customer_name }}</td>
                            <td class="border px-2 py-1 text-right">{{ number_format($tx->debit,2) }}</td>
                            <td class="border px-2 py-1 text-right">{{ number_format($tx->credit,2) }}</td>
                            <td class="border px-2 py-1 text-right">{{ number_format($tx->running_balance,2) }}</td>
                            <td class="border px-2 py-1">{{ $tx->notes }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament::page>
