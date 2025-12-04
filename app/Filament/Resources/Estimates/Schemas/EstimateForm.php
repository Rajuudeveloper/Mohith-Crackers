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
            // Estimate no
            TextInput::make('estimate_no')
                ->label('Estimate No')
                ->default(function () {
                    $last = Estimate::latest('id')->first();

                    if (! $last || ! $last->estimate_no) {
                        return 'ES-1';
                    }

                    $number = (int) str_replace('ES-', '', $last->estimate_no);

                    return 'ES-' . ($number + 1);
                })
                ->dehydrated(),

            // Date
            DatePicker::make('estimate_date')
                ->label('Estimate Date')
                ->default(Carbon::today())
                ->required(),

            // Customer
            Select::make('customer_id')
                ->label('Customer')
                ->options(fn() => Customer::orderByDesc('id')->pluck('name', 'id')->toArray())
                ->required()
                ->searchable()
                ->preload()
                ->native(false)
                ->columnSpanFull(),

            // tax locked
            TextInput::make('tax_locked')->hidden()->default(false)->dehydrated(),

            // Repeater
            Repeater::make('items')
                ->relationship('items')
                ->label('Line Items')
                ->columnSpanFull()
                ->live(debounce: 500)
                // parent-level recalculation: compute sub_total/tax/grand whenever items state changes
                ->afterStateUpdated(function ($state, $set, $get) {
                    $subTotal = 0.0;
                    if (is_array($state)) {
                        foreach ($state as $index => $item) {
                            $qty = isset($item['qty']) ? (float) $item['qty'] : 0.0;
                            $price = isset($item['price']) ? (float) $item['price'] : 0.0;
                            $lineTotal = round($qty * $price, 2);
                            // ensure stored as numeric
                            $set("items.{$index}.total", $lineTotal);
                            $subTotal += $lineTotal;
                        }
                    }
                    $subTotal = round($subTotal, 2);
                    $set('sub_total', $subTotal);

                    // tax (18%) if not locked
                    $taxLocked = (bool) $get('tax_locked');
                    if (! $taxLocked) {
                        $tax = round($subTotal * 0.18, 2);
                        $set('tax', $tax);
                    }

                    $packing = (float) ($get('packing_charges') ?? 0);
                    $tax = (float) ($get('tax') ?? 0);
                    $grand = round($subTotal + $tax + $packing, 2);
                    $set('grand_total', $grand);
                })
                ->schema([
                    // product select
                    Select::make('product_id')
                        ->label('Product')
                        ->options(fn() => Product::orderByDesc('id')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->live(debounce: 500)
                        ->dehydrated()
                        ->columnSpan(2)
                        ->afterStateUpdated(function ($state, $set, $get) {
                            // when product changes, set product specific defaults (raw numbers)
                            if (! $state) {
                                $set('price', 0.0);
                                $set('packs_per_case', 0);
                                $set('cases', 1);
                                $set('packs', 0);
                                $set('qty', 0);
                                $set('total', 0.0);
                                return;
                            }

                            $product = Product::find($state);
                            if (! $product) {
                                $set('price', 0.0);
                                $set('packs_per_case', 0);
                                $set('packs', 0);
                                $set('qty', 0);
                                $set('total', 0.0);
                                return;
                            }

                            // raw numeric values (no formatting)
                            $set('price', (float) $product->price);
                            $set('packs_per_case', (int) $product->packs_per_case);

                            // compute packs & qty based on current cases (or default 1)
                            $cases = (int) ($get('cases') ?? 1);
                            $packs = $cases * (int) $product->packs_per_case;
                            $qty = $packs; // one pack = one qty
                            $set('packs', $packs);
                            $set('qty', $qty);

                            // compute total numeric
                            $total = round($qty * (float) $product->price, 2);
                            $set('total', $total);
                        }),

                    // packs_per_case (show master value)
                    TextInput::make('packs_per_case')
                        ->label('Packs / Case')
                        ->numeric()
                        ->readOnly()
                        ->dehydrated()
                        ->columnSpan(1),

                    // cases (user input) — use live debounce to avoid digit-loss
                    TextInput::make('cases')
                        ->label('Cases')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->live(debounce: 500)
                        ->extraInputAttributes(['inputmode' => 'numeric', 'autocomplete' => 'off'])
                        ->columnSpan(1)
                        ->dehydrated()
                        ->afterStateUpdated(function ($state, $set, $get) {

                            $productId = $get('product_id');
                            $product = $productId ? Product::find($productId) : null;

                            $cases = max(0, (int) $state);

                            if (! $product) {
                                $set('packs', 0);
                                $set('qty', 0);
                                $set('total', 0);
                                return;
                            }

                            $packsPerCase = (int) $product->packs_per_case;
                            $packs = $cases * $packsPerCase;
                            $qty = $packs;

                            $set('packs', $packs);
                            $set('qty', $qty);

                            $price = (float) ($get('price') ?? 0);
                            $total = round($qty * $price, 2);
                            $set('total', $total);

                            /** ✅ ✅ HARD FIX: FORCE FULL SUMMARY RECALC ✅ ✅ */
                            $items = $get('../../items') ?? [];   // ✅ THIS IS THE KEY FIX

                            $subTotal = 0.0;
                            foreach ($items as $i => $item) {
                                $lineQty   = (float) ($item['qty'] ?? 0);
                                $linePrice = (float) ($item['price'] ?? 0);
                                $subTotal += round($lineQty * $linePrice, 2);
                            }

                            $subTotal = round($subTotal, 2);
                            $set('../../sub_total', $subTotal);   // ✅ FORCE SET PARENT FIELD

                            if (! (bool) $get('../../tax_locked')) {
                                $tax = round($subTotal * 0.18, 2);
                                $set('../../tax', $tax);
                            }

                            $packing = (float) ($get('../../packing_charges') ?? 0);
                            $tax = (float) ($get('../../tax') ?? 0);

                            $set('../../grand_total', round($subTotal + $tax + $packing, 2));
                        }),


                    // packs (derived, read-only)
                    TextInput::make('packs')
                        ->label('Packs')
                        ->numeric()
                        ->readOnly()
                        ->dehydrated()
                        ->columnSpan(1),

                    // qty (derived, read-only)
                    TextInput::make('qty')
                        ->label('Qty')
                        ->numeric()
                        ->readOnly()
                        ->dehydrated()
                        ->columnSpan(1),

                    // price (editable) — use live debounce
                    TextInput::make('price')
                        ->label('Price')
                        ->numeric()
                        ->minValue(0)
                        ->dehydrated()
                        ->step(0.01)
                        ->live(debounce: 500)
                        ->extraInputAttributes(['inputmode' => 'decimal', 'autocomplete' => 'off'])
                        ->columnSpan(1)
                        ->afterStateUpdated(function ($state, $set, $get) {
                            $qty = (float) ($get('qty') ?? 0.0);
                            $price = (float) $state;
                            $total = round($qty * $price, 2);
                            $set('total', $total);

                            // immediate parent recalc (same pattern as above)
                            $items = $get('items') ?? [];
                            $subTotal = 0.0;
                            if (is_array($items)) {
                                foreach ($items as $it) {
                                    $lineQty = isset($it['qty']) ? (float) $it['qty'] : 0.0;
                                    $linePrice = isset($it['price']) ? (float) $it['price'] : 0.0;
                                    $subTotal += round($lineQty * $linePrice, 2);
                                }
                            }
                            $subTotal = round($subTotal, 2);
                            $set('sub_total', $subTotal);
                            if (! (bool) $get('tax_locked')) {
                                $set('tax', round($subTotal * 0.18, 2));
                            }
                            $packing = (float) ($get('packing_charges') ?? 0);
                            $tax = (float) ($get('tax') ?? 0);
                            $set('grand_total', round($subTotal + $tax + $packing, 2));
                        }),

                    // total (derived)
                    TextInput::make('total')
                        ->label('Total')
                        ->numeric()
                        ->readOnly()
                        ->dehydrated()
                        ->columnSpan(1),
                ])
                ->columns(6)
                ->defaultItems(1)
                ->cloneable()
                ->reorderable(),

            // SUMMARY
            TextInput::make('sub_total')
                ->label('Sub Total')
                ->readOnly()
                ->dehydrated()
                ->default(0)
                ->columnSpanFull(),

            TextInput::make('tax')
                ->label('Tax (Amount)')
                ->numeric()
                ->default(0)
                ->step(0.01)
                ->live(debounce: 500)
                ->extraInputAttributes(['inputmode' => 'decimal'])
                ->columnSpanFull()
                ->afterStateUpdated(function ($state, $set, $get) {
                    $tax = (float) ($state ?? 0);
                    $sub = (float) ($get('sub_total') ?? 0);
                    $pack = (float) ($get('packing_charges') ?? 0);
                    $set('grand_total', round($sub + $tax + $pack, 2));
                }),

            TextInput::make('packing_charges')
                ->label('Packing Charges')
                ->numeric()
                ->default(0)
                ->step(0.01)
                ->live(debounce: 500)
                ->extraInputAttributes(['inputmode' => 'decimal'])
                ->columnSpanFull()
                ->afterStateUpdated(function ($state, $set, $get) {
                    $sub = (float) ($get('sub_total') ?? 0);
                    $tax = (float) ($get('tax') ?? 0);
                    $pack = (float) ($state ?? 0);
                    $set('grand_total', round($sub + $tax + $pack, 2));
                }),

            TextInput::make('grand_total')
                ->label('Grand Total')
                ->readOnly()
                ->dehydrated()
                ->default(0)
                ->columnSpanFull(),
        ]);
    }
}
