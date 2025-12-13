<!-- scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(function() {
        // CSRF for AJAX (if later used)
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // init select2 for customer & existing product selects
        $('#customer').select2({
            placeholder: 'Select customer...',
            allowClear: true,
            width: 'resolve'
        });

        // products list from server
        const products = @json($products);

        // flag to track whether tax_amt was manually edited by user
        let taxAmtManuallyEdited = false;

        // helper to build product options (string)
        function buildProductOptions(selectedId = null) {
            return products.map(p => {
                const selected = selectedId && selectedId == p.id ? 'selected' : '';
                // escape name is not necessary because blade json already safe; but we output as-is
                return `<option value="${p.id}" ${selected} data-packs="${p.packs_per_case}" data-uom="${p.uom_name}" data-price="${p.price}">${p.name}</option>`;
            }).join('');
        }

        // add line item (for new rows)
        function addLineItem(selectedId = null, caseVal = 1, priceVal = '', packsVal = '', qtyVal = '') {
            const productOptions = '<option></option>' + buildProductOptions(selectedId);
            const $row = $(`
                <tr>
                <td><select class="form-select productSelect" style="width:100%">${productOptions}</select></td>
                <td><input type="number" class="form-control caseInput" value="${caseVal}" min="0"></td>
                <td><input type="number" class="form-control packsInput" readonly value="${packsVal || ''}"></td>
                <td>
                <div class="qty-uom">
                    <input type="number" class="form-control qtyInput" readonly value="${qtyVal || ''}">
                    <span class="uomText"></span>
                </div>
                </td>
                <td><input type="number" class="form-control priceInput" value="${priceVal || ''}" min="0" step="0.01"></td>
                <td class="lineTotal">0.00</td>
                <td class="text-center">
                <button type="button" class="btn btn-link p-0 text-danger removeLineItem" title="Remove">
                    <i class="bi bi-trash3-fill fs-5"></i>
                </button>
                </td>
                </tr>
            `);
            // append and init select2 on the product select
            $('#lineItemsTable tbody').append($row);
            $row.find('.productSelect').select2({
                placeholder: 'Select product...',
                allowClear: true,
                width: 'resolve'
            });

            // If selectedId provided, trigger update to populate packs/qty/price
            if (selectedId) {
                updateLineItem($row, true); // set price from product if empty
            } else {
                // new rows: if product default selected via options, leave empty until selection or set defaults
                updateLineItem($row, false);
            }
            recalcAll();
        }

        // update single row (set packs, qty, uom, set price only when requested)
        function updateLineItem($row, setPriceIfEmpty = false) {
            const $select = $row.find('.productSelect');
            const $selectedOption = $select.find('option:selected');
            const packs = parseFloat($selectedOption.data('packs')) || 0;
            const uom = $selectedOption.data('uom') || '';
            const defaultPrice = parseFloat($selectedOption.data('price')) || 0;

            const cases = parseFloat($row.find('.caseInput').val()) || 0;
            const qty = cases * packs;

            $row.find('.packsInput').val(packs);
            $row.find('.qtyInput').val(qty);
            $row.find('.uomText').text(uom);

            const $priceInput = $row.find('.priceInput');
            if (!$priceInput.val() || setPriceIfEmpty) {
                // set default price (rounded to 2 decimals)
                $priceInput.val(defaultPrice ? defaultPrice.toFixed(2) : '');
            }

            const price = parseFloat($priceInput.val()) || 0;
            const lineTotal = qty * price;
            $row.find('.lineTotal').text(lineTotal.toFixed(2));
        }

        // recalc totals (subtotal, packing amount, tax, grand total, roundoff)
        function recalcAll() {
            // update each row totals first
            $('#lineItemsTable tbody tr').each(function() {
                updateLineItem($(this), false); // do not overwrite user-edited price
            });

            // sub total = sum of lineTotals
            let subTotal = 0;
            $('#lineItemsTable tbody tr').each(function() {
                subTotal += parseFloat($(this).find('.lineTotal').text()) || 0;
            });

            // packing percent -> packing amount
            const packingPercent = parseFloat($('#packingPercent').val()) || 0;
            const packingAmount = (subTotal * packingPercent) / 100;

            // tax: use value from tax_amt input (which may be auto-set from dropdown or manually edited)
            const taxAmt = parseFloat($('#tax_amt').val()) || 0;

            // compute preliminary grand
            let grand = subTotal + packingAmount + taxAmt;

            // round off handling
            let roundOffAmount = 0;
            if ($('#roundOffCheck').is(':checked')) {
                const rounded = Math.round(grand);
                roundOffAmount = rounded - grand;
                grand = rounded;
            }

            // update UI
            $('#subTotal').val(subTotal.toFixed(2));
            $('#packingAmount').text(packingAmount.toFixed(2));
            $('#taxAmountDisplay').text(taxAmt.toFixed(2));
            $('#roundOffAmount').text((roundOffAmount >= 0 ? '+' : '') + roundOffAmount.toFixed(2));
            $('#grandTotal').text(grand.toFixed(2));
        }

        // --- events ---

        // product change: populate packs, qty, set default price (overwrite)
        $(document).on('change', '.productSelect', function() {
            const $row = $(this).closest('tr');
            updateLineItem($row, true); // set price from product master
            recalcAll();
        });

        // case, price changes -> recalc
        $(document).on('input change', '.caseInput, .priceInput', function() {
            // do not override price (user can type)
            recalcAll();
        });

        // add row
        $('#addLineItem').on('click', function() {
            addLineItem();
        });

        // remove row
        $(document).on('click', '.removeLineItem', function() {
            $(this).closest('tr').remove();
            recalcAll();
        });

        // TAX dropdown: when changed, auto-calc tax_amt from subtotal unless user later edits tax_amt
        $('#tax_id').on('change', function() {
            const pct = parseFloat($(this).val());
            const sub = parseFloat($('#subTotal').val()) || 0;
            if (!isNaN(pct)) {
                const calculated = (sub * pct) / 100;
                $('#tax_amt').val(calculated.toFixed(2));
                taxAmtManuallyEdited = false; // mark as auto
                recalcAll();
            } else {
                // if blank selected, do nothing; user may manually set tax_amt
                if (!taxAmtManuallyEdited) {
                    $('#tax_amt').val((0).toFixed(2));
                    recalcAll();
                }
            }
        });

        // tax amount manual override: set flag
        $('#tax_amt').on('input change', function() {
            taxAmtManuallyEdited = true;
            recalcAll();
        });

        // packing percent change
        $('#packingPercent').on('input change', function() {
            recalcAll();
        });

        // round off toggle
        $('#roundOffCheck').on('change', function() {
            recalcAll();
        });

        // initialize existing rows (edit mode)
        $('#lineItemsTable tbody tr').each(function() {
            const $row = $(this);
            $row.find('.productSelect').select2({
                placeholder: 'Select product...',
                allowClear: true,
                width: 'resolve'
            });

            // set initial packs/qty/price based on selected product (but keep existing price value if present)
            // If price input is empty, set from product
            const priceInputVal = $row.find('.priceInput').val();
            updateLineItem($row, !priceInputVal); // setPriceIfEmpty = true only if priceInput empty
        });
        // initial totals
        recalcAll();

        // ----- AJAX submit (store/update) -----
        $('#estimateForm').on('submit', function(e) {
            e.preventDefault();
            $('#saveBtn').prop('disabled', true);

            // Build payload
            const payload = {
                estimate_no: $('#estimateNo').val(),
                estimate_date: $('#estimateDate').val(),
                customer_id: $('#customer').val(),
                sub_total: parseFloat($('#subTotal').val()) || 0,
                tax_id: $('#tax_id').val() === "" ? null : parseFloat($('#tax_id').val()),
                tax_amt: parseFloat($('#tax_amt').val()) || 0,
                packing_percent: parseFloat($('#packingPercent').val()) || 0,
                packing_amount: parseFloat($('#packingAmount').text()) || 0,
                is_round_off: $('#roundOffCheck').is(':checked') ? 1 : 0,
                round_off_amount: parseFloat($('#roundOffAmount').text()) || 0,
                grand_total: parseFloat($('#grandTotal').text()) || 0,
                line_items: []
            };

            // collect line items
            $('#lineItemsTable tbody tr').each(function() {
                const $r = $(this);
                payload.line_items.push({
                    product_id: $r.find('.productSelect').val(),
                    cases: parseFloat($r.find('.caseInput').val()) || 0,
                    packs: parseFloat($r.find('.packsInput').val()) || 0,
                    qty: parseFloat($r.find('.qtyInput').val()) || 0,
                    uom_name: $r.find('.uomText').text() || '',
                    price: parseFloat($r.find('.priceInput').val()) || 0,
                    total: parseFloat($r.find('.lineTotal').text()) || 0,
                });
            });

            const estimateId = $('#estimateId').val();
            let url, method;
            if (estimateId) {
                // update route you used earlier
                url = "{{ url('/') }}" + `/admin/custom/estimates/${estimateId}/update`;
                method =
                    'POST'; // you said you used POST update; change to PUT if your route expects PUT
            } else {
                url = "{{ route('estimates.custom.store') }}"; // keep your custom store route
                method = 'POST';
            }

            $.ajax({
                url: url,
                method: method,
                contentType: 'application/json',
                data: JSON.stringify(payload),
                dataType: 'json',
            }).done(function(res) {
                $('#saveBtn').prop('disabled', false);
                if (res.success && res.estimate_id) {
                    // redirect to edit page after create
                    window.location.href = "{{ url('admin/estimates') }}";
                } else if (res.success) {
                    alert('Saved successfully');
                } else {
                    alert('Save failed: ' + (res.message || 'Unknown'));
                }
            }).fail(function(err) {
                $('#saveBtn').prop('disabled', false);
                console.error(err);
                if (err.responseJSON && err.responseJSON.errors) {
                    const firstKey = Object.keys(err.responseJSON.errors)[0];
                    alert(err.responseJSON.errors[firstKey][0]);
                } else {
                    alert('Save failed. Check console for details.');
                }
            });
        });

    }); // end doc ready
</script>
