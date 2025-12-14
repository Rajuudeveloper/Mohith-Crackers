<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Estimate</title>
    <link rel="stylesheet" href="{{ public_path('css/estimate_pdf.css') }}">
</head>

<body>
    <div class="pdf_container">
        <div class="container">

            <!-- Header -->
            <div class="main_header" style="padding-top: 0;">
                <img class="header_logo"
                    src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/navrang_logo.jpg'))) }}"
                    alt="Logo">
                <br>
                <img class="header_2_logo"
                    src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/mo_full_logo.jpeg'))) }}"
                    alt="Logo">

                <h3 style="margin-top: 5px">Estimate</h3>
                <p>
                    NO: {{ $estimate->estimate_no ?? 'N/A' }}<br>
                    Date: {{ $estimate->created_at->format('d-m-Y') }}
                </p>
            </div>

            <!-- Customer Details -->
            <div class="customer-details">
                <table>
                    <tr>
                        <td><strong>Bill To:</strong></td>
                        <td>{{ $estimate->customer->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Address:</strong></td>
                        <td>{{ $estimate->customer->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>GSTIN:</strong></td>
                        <td>{{ $estimate->customer->gstin ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Products -->
            <div class="products_section">
                <table class="products">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Cases</th>
                            <th>Pack</th>
                            <th>Qty</th>
                            <th>Rate</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($estimate->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->cases }}</td>
                                <td>{{ $item->packs }}</td>
                                <td>{{ $item->qty }} {{ $item->uom_name ?? $item->product->uom_name }}</td>
                                <td>₹ {{ number_format($item->price, 2) }}</td>
                                <td>₹ {{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach

                        <tr>
                            <td>&nbsp;</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="totals">
                <table>
                    <tr>
                        <td class="label">Sub Total:</td>
                        <td class="value">₹ {{ number_format($estimate->sub_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tax ({{ number_format($estimate->tax_id, 2) }}%):</td>
                        <td class="value">₹ {{ number_format($estimate->tax_amt, 2) }}</td>
                    </tr>
                    <td class="label">
                        Packing Charges ({{ number_format($estimate->packing_percent, 2) }}%):
                    </td>
                    <td class="value">
                        ₹ {{ number_format($estimate->packing_charges, 2) }}
                    </td>
                    <tr>
                        <td class="label"><strong>Grand Total:</strong></td>
                        <td class="value"><strong>₹ {{ number_format($estimate->grand_total, 2) }}</strong></td>
                    </tr>
                </table>
                <div class="clearfix"></div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="signature">
                    <p>Authorised Signatory</p>
                    <div class="signature-line"></div>
                </div>
                <div class="clearfix"></div>
            </div>

        </div>

        <img class="bg_logo"
            src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/mo_logo.jpg'))) }}"
            alt="Logo">
    </div>
</body>

</html>
