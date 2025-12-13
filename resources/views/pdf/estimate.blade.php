<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
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
                <p>NO: INV-001<br>
                    Date: 04-12-2025</p>
            </div>

            <!-- Customer Details -->
            <div class="customer-details">
                <table>
                    <tr>
                        <td><strong>Bill To:</strong></td>
                        <td>XYZ Enterprises</td>
                    </tr>
                    <tr>
                        <td><strong>Address:</strong></td>
                        <td>456 Client Road, City, State - 654321</td>
                    </tr>
                    <tr>
                        <td><strong>GSTIN:</strong></td>
                        <td>33BBBBB1111B2Z6</td>
                    </tr>
                </table>
            </div>

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
                        <tr>
                            <td>1</td>
                            <td>KITKAT</td>
                            <td>10</td>
                            <td>60</td>
                            <td>600 UNIT</td>
                            <td>₹ 35.00</td>
                            <td>₹ 21000.00</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>FPSPECIAL</td>
                            <td>10</td>
                            <td>60</td>
                            <td>600 UNIT</td>
                            <td>₹ 35.00</td>
                            <td>₹ 21000.00</td>
                        </tr>
                        <tr>
                            <td></td>
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
                        <td class="value">₹ 2650.00</td>
                    </tr>
                    <tr>
                        <td class="label">Tax (18%):</td>
                        <td class="value">₹ 477.00</td>
                    </tr>
                    <tr>
                        <td class="label">Packing Charges (1.10%):</td>
                        <td class="value">₹ 50.00</td>
                    </tr>
                    <tr>
                        <td class="label"><strong>Grand Total:</strong></td>
                        <td class="value"><strong>₹ 42000.00</strong></td>
                    </tr>
                </table>
                <div class="clearfix"></div>
            </div>

            <!-- Footer / Signature -->
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
