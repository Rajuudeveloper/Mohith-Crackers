<?php

namespace App\Filament\Resources\Estimates\Schemas;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Estimate;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;

class EstimateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ESTIMATE NO (auto-generate)
            TextInput::make('estimate_no')
                ->label('Estimate No')
                ->default(function () {
                    // Get last estimate number
                    $last = Estimate::latest('id')->first();
                    $number = $last ? ((int) str_replace('ES-', '', $last->estimate_no)) + 1 : 1;
                    return 'ES-' . $number;
                })
                ->disabled(false)      // must be enabled to submit
                ->dehydrated(true),

            // ESTIMATE DATE (default today)
            DatePicker::make('estimate_date')
                ->label('Estimate Date')
                ->default(Carbon::today())
                ->required(),

            // CUSTOMER
            Select::make('customer_id')
                ->label('Customer')
                ->options(Customer::orderBy('id', 'desc')->pluck('name', 'id')->toArray())
                ->required()
                ->searchable()
                ->preload()
                ->native(false)
                ->columnSpanFull(),

            // HIDDEN FLAG: when user edits tax manually we'll lock auto-updates
            TextInput::make('tax_locked')
                ->hidden()
                ->default(false)
                ->dehydrated(),

            // LINE ITEMS (server reactive)
            Repeater::make('items')
                ->relationship('items')
                ->label('Line Items')
                ->columnSpanFull()
                ->live(debounce: 500) // Changed from reactive() to live() with debounce
                // when anything inside items changes (add/remove/edit), recalc totals
                ->afterStateUpdated(function ($state, $set, $get) {
                    $subTotal = 0;

                    if (is_array($state)) {
                        foreach ($state as $index => $item) {
                            $qty = isset($item['qty']) ? (float) $item['qty'] : 0;
                            $price = isset($item['price']) ? (float) $item['price'] : 0;
                            $lineTotal = round($qty * $price, 2);

                            // Ensure each line's total state is correct
                            // Filament expects nested set names like items.0.total
                            $set("items.{$index}.total", number_format($lineTotal, 2, '.', ''));

                            $subTotal += $lineTotal;
                        }
                    }

                    $subTotal = round($subTotal, 2);
                    $set('sub_total', number_format($subTotal, 2, '.', ''));

                    // Auto-calc tax as 18% only if tax is not locked by user
                    $taxLocked = (bool) $get('tax_locked');
                    if (! $taxLocked) {
                        $autoTax = round($subTotal * 0.18, 2);
                        $set('tax', number_format($autoTax, 2, '.', ''));
                    }

                    // Packing charges might be empty => 0
                    $packing = (float) ($get('packing_charges') ?? 0);
                    $tax = (float) ($get('tax') ?? 0);

                    $grand = round($subTotal + $tax + $packing, 2);
                    $set('grand_total', number_format($grand, 2, '.', ''));
                })
                ->schema([

                    Select::make('product_id')
                        ->label('Product')
                        ->options(Product::orderBy('id', 'desc')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->columnSpan(2)
                        ->live(debounce: 500) // Changed from reactive() to live()
                        ->afterStateUpdated(function ($state, $set, $get) {
                            if (! $state) {
                                $set('uom_name', '');
                                $set('price', 0);
                                $set('total', number_format(0, 2, '.', ''));
                                return;
                            }

                            $product = Product::find($state);
                            if ($product) {
                                $set('uom_name', $product->uom ?? '');
                                $set('price', number_format($product->price ?? 0, 2, '.', ''));

                                $qty = (float) ($get('qty') ?? 1);
                                $lineTotal = round($qty * ($product->price ?? 0), 2);
                                $set('total', number_format($lineTotal, 2, '.', ''));
                            }
                        }),

                    TextInput::make('uom_name')
                        ->label('UOM')
                        ->readOnly(),

                    // QTY - FIXED VERSION
                    TextInput::make('qty')
                        ->label('Qty')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->live(debounce: 500) // Changed from reactive() to live()
                        ->afterStateUpdated(function ($state, $set, $get) {
                            $qty = (float) ($state ?? 0);
                            $price = (float) ($get('price') ?? 0);
                            $lineTotal = round($qty * $price, 2);
                            $set('total', number_format($lineTotal, 2, '.', ''));
                        })
                        ->extraInputAttributes([
                            'inputmode' => 'decimal',
                        ]),

                    // PRICE - FIXED VERSION
                    TextInput::make('price')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->step(0.01)
                        ->live(debounce: 500) // Changed from reactive() to live()
                        ->afterStateUpdated(function ($state, $set, $get) {
                            $price = (float) ($state ?? 0);
                            $qty = (float) ($get('qty') ?? 0);
                            $lineTotal = round($qty * $price, 2);
                            $set('total', number_format($lineTotal, 2, '.', ''));
                        })
                        ->extraInputAttributes([
                            'inputmode' => 'decimal',
                        ]),

                    // TOTAL (readonly but dehydrated so it saves)
                    TextInput::make('total')
                        ->label('Total')
                        ->readOnly()
                        ->dehydrated(),
                ])
                ->columns(6)
                ->defaultItems(1)
                ->cloneable()
                ->reorderable(),

            // SUMMARY SECTION (server-driven)
            TextInput::make('sub_total')
                ->label('Sub Total')
                ->readOnly()
                ->dehydrated()
                ->default(0)
                ->columnSpanFull(),

            // Tax amount (editable). When user edits it we lock auto updates.
            TextInput::make('tax')
                ->label('Tax (Amount)')
                ->numeric()
                ->default(0)
                ->step(0.01)
                ->live(debounce: 500) // Changed from reactive() to live()
                ->afterStateUpdated(function ($state, $set, $get) {
                    // user touched tax => lock automatic recalculation
                    // $set('tax_locked', true);

                    // Recompute grand total when packing changes

                    $tax = (float) ($state ?? 0);
                    $sub = (float) ($get('sub_total') ?? 0);
                    $pack = (float) ($get('packing_charges') ?? 0);
                    $grand = round($sub + $tax + $pack, 2);
                    $set('grand_total', number_format($grand, 2, '.', ''));
                })
                ->extraInputAttributes([
                    'inputmode' => 'decimal',
                ])
                ->columnSpanFull(),

            TextInput::make('packing_charges')
                ->label('Packing Charges')
                ->numeric()
                ->default(0)
                ->step(0.01)
                ->live(debounce: 500) // Changed from reactive() to live()
                ->afterStateUpdated(function ($state, $set, $get) {
                    // Recompute grand total when packing changes
                    $sub = (float) ($get('sub_total') ?? 0);
                    $tax = (float) ($get('tax') ?? 0);
                    $pack = (float) ($state ?? 0);
                    $grand = round($sub + $tax + $pack, 2);
                    $set('grand_total', number_format($grand, 2, '.', ''));
                })
                ->extraInputAttributes([
                    'inputmode' => 'decimal',
                ])
                ->columnSpanFull(),

            TextInput::make('grand_total')
                ->label('Grand Total')
                ->readOnly()
                ->dehydrated()
                ->default(0)
                ->columnSpanFull(),
        ]);
    }
}
