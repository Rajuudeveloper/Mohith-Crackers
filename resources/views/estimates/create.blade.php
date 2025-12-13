<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Estimates</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap & Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/estimate.css') }}">
</head>

<body>
    <div class="container">

        @if (@$estimate->id)
            <h3 class="mb-4" style="display: inline-block">Edit Estimate</h3>
        @else
            <h3 class="mb-4" style="display: inline-block">Create Form</h3>
        @endif
        <a href="{{ url('admin/estimates') }}" class="btn btn-secondary" style="float: right">← Back</a>

        <form id="estimateForm">
            <input type="hidden" id="estimateId" value="{{ $estimate->id ?? '' }}">

            <div class="row mb-3">
                <div class="col-md-3 mb-2">
                    <label class="form-label">Estimate No</label>
                    <input type="text" id="estimateNo" class="form-control" placeholder="Estimate No"
                        value="{{ $estimate->estimate_no ?? @$estimateNo }}">
                </div>

                <div class="col-md-3 mb-2">
                    <label class="form-label">Estimate Date</label>
                    <input type="date" id="estimateDate" class="form-control"
                        value="{{ $estimate->estimate_date ?? date('Y-m-d') }}">
                </div>

                <div class="col-md-3 mb-2">
                    <label class="form-label">Customer</label>
                    <select id="customer" class="form-select" style="width:100%">
                        <option></option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}"
                                {{ isset($estimate) && $estimate->customer_id == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Notes removed as requested -->
            </div>

            <h5 class="mt-4">Line Items</h5>
            <div class="table-responsive">
                <table class="table table-dark table-striped" id="lineItemsTable">
                    <thead>
                        <tr>
                            <th width="25%">Product</th>
                            <th width="8%">Case</th>
                            <th width="7%">Packs</th>
                            <th width="15%">Qty (UOM)</th>
                            <th width="13%">Price</th>
                            <th width="10%">Total</th>
                            <th width="3%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Existing rows for edit --}}
                        @if (isset($estimate) && $estimate->items->count())
                            @foreach ($estimate->items as $index => $item)
                                <tr data-index="{{ $index + 1 }}">
                                    <td>
                                        <select class="form-select productSelect" style="width:100%">
                                            <option></option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}"
                                                    data-packs="{{ $product->packs_per_case }}"
                                                    data-uom="{{ $product->uom_name }}"
                                                    data-price="{{ $product->price }}"
                                                    {{ $product->id == $item->product_id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td><input type="number" class="form-control caseInput"
                                            value="{{ $item->cases }}" min="0"></td>
                                    <td><input type="number" class="form-control packsInput" readonly></td>

                                    <td>
                                        <div class="qty-uom">
                                            <input type="number" class="form-control qtyInput" readonly>
                                            <span class="uomText"></span>
                                        </div>
                                    </td>

                                    <td><input type="number" class="form-control priceInput"
                                            value="{{ $item->price }}" min="0" step="0.01"></td>
                                    <td class="lineTotal">0.00</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-link p-0 text-danger removeLineItem"
                                            title="Remove">
                                            <i class="bi bi-trash3-fill fs-5"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <button type="button" id="addLineItem" class="btn btn-primary mb-3">Add Line Item</button>


            <div class="bottom_summary_section mt-4 p-4 rounded-4 border border-secondary"
                style="background: linear-gradient(180deg,#1f2933,#111827);">

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-white fw-bold">Summary</h5>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="roundOffCheck"
                            {{ isset($estimate) && $estimate->is_round_off ? 'checked' : '' }}>
                        <label class="form-check-label text-white fw-semibold">
                            Apply Round Off
                        </label>
                    </div>
                </div>

                <!-- INPUT ROW -->
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <label class="form-label text-light">Sub Total</label>
                        <input type="text" id="subTotal" class="form-control text-end fw-bold" readonly
                            value="0.00">
                    </div>

                    <div class="col-md-3 col-6">
                        <label class="form-label text-light">Tax %</label>
                        <select id="tax_id" class="form-select">
                            <option value="">-- Select --</option>
                            @foreach (config('taxes') as $val => $label)
                                <option value="{{ $val }}"
                                    {{ isset($estimate) && $estimate->tax_id == $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-6">
                        <label class="form-label text-light">Tax Amount</label>
                        <input type="number" id="tax_amt" class="form-control text-end"
                            value="{{ $estimate->tax_amt ?? 0 }}" step="0.01">
                        <small class="text-secondary">Editable</small>
                    </div>

                    <div class="col-md-3 col-6">
                        <label class="form-label text-light">Packing (%)</label>
                        <input type="number" id="packingPercent" class="form-control text-end"
                            value="{{ $estimate->packing_percent ?? 0 }}" step="0.01">
                    </div>
                </div>

                <!-- CALCULATED VALUES -->
                <div class="row mt-4 g-3 align-items-center">

                    <!-- PACKING AMOUNT -->
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded bg-dark border text-center">
                            <div class="text-secondary small">Packing Amount</div>
                            <div class="fw-bold text-white">
                                ₹ <span id="packingAmount">0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- FINAL TAX -->
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded bg-dark border text-center">
                            <div class="text-secondary small">Final Tax</div>
                            <div class="fw-bold text-white">
                                ₹ <span id="taxAmountDisplay">{{ $estimate->tax_amt ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ROUND & GRAND -->
                    <div class="col-md-6 col-12">
                        <div class="p-3 rounded bg-black border border-secondary text-end">
                            <div class="small text-secondary">Round Difference</div>
                            <div id="roundOffAmount" class="fw-bold text-warning">
                                {{ number_format($estimate->round_off_amount ?? 0, 2) }}
                            </div>

                            <hr class="border-secondary my-2">

                            <div class="small text-secondary">Grand Total</div>
                            <div class="fs-2 fw-bold text-success">
                                ₹ <span id="grandTotal">0.00</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>


            <div class="mt-3 text-end">
                <button type="submit" id="saveBtn" class="btn btn-primary">Submit Estimate</button>
            </div>
        </form>
    </div>

    @include('estimates.Js.add_edit_js')
</body>

</html>
