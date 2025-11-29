<?php

namespace App\Filament\Resources\Estimates\Schemas;

use App\Models\Customer;
use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;

class EstimateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ===========================
            // CUSTOMER DROPDOWN (FULL WIDTH)
            // ===========================

            Select::make('customer_id')
                ->label('Customer')
                ->options(Customer::orderByDesc('id')->pluck('name', 'id')->toArray())
                ->required()
                ->searchable()
                ->preload()
                ->native(false)
                ->columnSpanFull(),


            // ===========================
            // LINE ITEMS (PURE CLIENT SIDE)
            // ===========================

            Repeater::make('items')
                ->relationship('items')
                ->label('Line Items')
                ->columnSpanFull()
                ->schema([

                    Select::make('product_id')
                        ->label('Product')
                        ->options(Product::orderByDesc('id')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->columnSpan(2)
                        ->afterStateUpdated(function ($state, $set, $get) {
                            if (! $state) return;

                            $product = Product::find($state);
                            if ($product) {
                                $set('uom_name', $product->uom ?? '');
                                $set('price', $product->price ?? 0);

                                $qty = $get('qty') ?? 1;
                                $set('total', number_format($qty * ($product->price ?? 0), 2, '.', ''));
                            }
                        }),

                    TextInput::make('uom_name')
                        ->label('UOM')
                        ->readOnly(),

                    // QTY INPUT
                    TextInput::make('qty')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->extraAttributes([
                            'wire:ignore' => true,
                            '@input' => '
                                (function(){
                                    const row = $event.target.closest("[data-repeater-item]") || $event.target.closest(".filament-repeater-item");
                                    if (!row) return;

                                    const qty = parseFloat($event.target.value || 0);
                                    const priceInput = row.querySelector("input[name*=\'[price]\']");
                                    const totalInput = row.querySelector("input[name*=\'[total]\']");
                                    const price = parseFloat(priceInput?.value || 0);

                                    if (totalInput) totalInput.value = (qty * price).toFixed(2);

                                    updateMainTotals();
                                })();
                            ',
                        ]),

                    // PRICE INPUT
                    TextInput::make('price')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->extraAttributes([
                            'wire:ignore' => true,
                            '@input' => '
                                (function(){
                                    const row = $event.target.closest("[data-repeater-item]") || $event.target.closest(".filament-repeater-item");
                                    if (!row) return;

                                    const price = parseFloat($event.target.value || 0);
                                    const qtyInput = row.querySelector("input[name*=\'[qty]\']");
                                    const totalInput = row.querySelector("input[name*=\'[total]\']");
                                    const qty = parseFloat(qtyInput?.value || 0);

                                    if (totalInput) totalInput.value = (qty * price).toFixed(2);

                                    updateMainTotals();
                                })();
                            ',
                        ]),

                    // TOTAL (READONLY)
                    TextInput::make('total')
                        ->label('Total')
                        ->readOnly()
                        ->extraAttributes([
                            'wire:ignore' => true,
                        ])
                        ->dehydrated(),

                ])
                ->columns(6)
                ->defaultItems(1)
                ->cloneable()
                ->reorderable(),


            // ===========================
            // SUMMARY SECTION (PURE CLIENT)
            // ===========================

            TextInput::make('sub_total')
                ->label('Sub Total')
                ->readOnly()
                ->dehydrated()
                ->default(0)
                ->columnSpanFull(),

            TextInput::make('tax')
                ->label('Tax')
                ->numeric()
                ->default(0)
                ->extraAttributes([
                    '@input' => '
                        (function(){
                            updateMainTotals();
                        })();
                    ',
                ])
                ->columnSpanFull(),

            TextInput::make('packing_charges')
                ->label('Packing Charges')
                ->numeric()
                ->default(0)
                ->extraAttributes([
                    '@input' => '
                        (function(){
                            updateMainTotals();
                        })();
                    ',
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
