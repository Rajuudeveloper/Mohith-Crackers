<!-- scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    $(function() {
        flatpickr("#estimateDate", {
            dateFormat: "d-m-Y",
            altInput: true,
            altFormat: "d-m-Y"
        });
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#customer').select2({
            placeholder: 'Select customer...',
            allowClear: true,
            width: 'resolve'
        });

        const products = @json($products);
        let taxAmtManuallyEdited = false;
        
        // Flag to track if commission was manually edited
        let commissionManuallyEdited = false;

        function buildProductOptions(selectedId = null) {
            return products.map(p => {
                const selected = selectedId && selectedId == p.id ? 'selected' : '';
                return `<option value="${p.id}" ${selected} data-packs="${p.packs_per_case}" data-uom="${p.uom_name}" data-price="${p.price}">${p.name}</option>`;
            }).join('');
        }

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
            $('#lineItemsTable tbody').append($row);
            $row.find('.productSelect').select2({
                placeholder: 'Select product...',
                allowClear: true,
                width: 'resolve'
            });

            if (selectedId) {
                updateLineItem($row, true);
            } else {
                updateLineItem($row, false);
            }
            recalcAll();
        }

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
                $priceInput.val(defaultPrice ? defaultPrice.toFixed(2) : '');
            }

            const price = parseFloat($priceInput.val()) || 0;
            const lineTotal = qty * price;
            $row.find('.lineTotal').text(lineTotal.toFixed(2));
        }

        function recalcAll() {
            // Update each row totals first
            $('#lineItemsTable tbody tr').each(function() {
                updateLineItem($(this), false);
            });

            // Calculate sub total
            let subTotal = 0;
            $('#lineItemsTable tbody tr').each(function() {
                subTotal += parseFloat($(this).find('.lineTotal').text()) || 0;
            });

            // Packing amount
            const packingPercent = parseFloat($('#packingPercent').val()) || 0;
            const packingAmount = (subTotal * packingPercent) / 100;

            // Tax amount
            const taxAmt = parseFloat($('#tax_amt').val()) || 0;

            // Calculate pre-commission total
            let preCommissionTotal = subTotal + packingAmount + taxAmt;

            // Round off handling (before commission)
            let roundOffAmount = 0;
            if ($('#roundOffCheck').is(':checked')) {
                const rounded = Math.round(preCommissionTotal);
                roundOffAmount = rounded - preCommissionTotal;
                preCommissionTotal = rounded;
            }

            // COMMISSION CALCULATION
            let commission = 0;
            
            // Check if commission was manually edited
            const commissionInputVal = $('#commission').val();
            const commissionInput = parseFloat(commissionInputVal) || 0;
            
            if (commissionManuallyEdited) {
                // Use manually entered value
                commission = commissionInput;
            } else {
                // Calculate default 5% commission from preCommissionTotal
                commission = (preCommissionTotal * 5) / 100;
                
                // Round commission to 2 decimal places
                commission = Math.round(commission * 100) / 100;
                
                // Update the commission input field with calculated value
                $('#commission').val(commission.toFixed(2));
            }

            // Calculate final Grand Total (after commission deduction)
            let grandTotal = preCommissionTotal - commission;

            // Update all UI elements
            $('#subTotal').val(subTotal.toFixed(2));
            $('#packingAmount').text(packingAmount.toFixed(2));
            $('#taxAmountDisplay').text(taxAmt.toFixed(2));
            $('#roundOffAmount').text((roundOffAmount >= 0 ? '+' : '') + roundOffAmount.toFixed(2));
            // $('#commissionDisplay').text(commission.toFixed(2)); // Optional: You can add a display span
            $('#grandTotal').text(grandTotal.toFixed(2));
        }

        // --- Existing Events ---
        $(document).on('change', '.productSelect', function() {
            const $row = $(this).closest('tr');
            updateLineItem($row, true);
            recalcAll();
        });

        $(document).on('input change', '.caseInput, .priceInput', function() {
            recalcAll();
        });

        $('#addLineItem').on('click', function() {
            addLineItem();
        });

        $(document).on('click', '.removeLineItem', function() {
            $(this).closest('tr').remove();
            recalcAll();
        });

        $('#tax_id').on('change', function() {
            const pct = parseFloat($(this).val());
            const sub = parseFloat($('#subTotal').val()) || 0;
            if (!isNaN(pct)) {
                const calculated = (sub * pct) / 100;
                $('#tax_amt').val(calculated.toFixed(2));
                taxAmtManuallyEdited = false;
                recalcAll();
            } else {
                if (!taxAmtManuallyEdited) {
                    $('#tax_amt').val((0).toFixed(2));
                    recalcAll();
                }
            }
        });

        $('#tax_amt').on('input change', function() {
            taxAmtManuallyEdited = true;
            recalcAll();
        });

        $('#packingPercent').on('input change', function() {
            recalcAll();
        });

        $('#roundOffCheck').on('change', function() {
            recalcAll();
        });

        // --- NEW: Commission Events ---
        $('#commission').on('input change', function() {
            // When user types in commission field, mark it as manually edited
            commissionManuallyEdited = true;
            recalcAll();
        });

        // Reset commission to auto-calc when field is cleared
        $('#commission').on('blur', function() {
            if ($(this).val() === '' || $(this).val() === '0') {
                // If user clears the field or sets to 0, revert to auto calculation
                commissionManuallyEdited = false;
                recalcAll();
            }
        });

        // Initialize existing rows
        $('#lineItemsTable tbody tr').each(function() {
            const $row = $(this);
            $row.find('.productSelect').select2({
                placeholder: 'Select product...',
                allowClear: true,
                width: 'resolve'
            });

            const priceInputVal = $row.find('.priceInput').val();
            updateLineItem($row, !priceInputVal);
        });
        
        // Initialize commission (set to auto-calc mode on page load)
        // If there's existing commission value from database, mark as manually edited
        const initialCommission = $('#commission').val();
        if (initialCommission && initialCommission !== '0') {
            commissionManuallyEdited = true;
        }
        
        // Initial calculation
        recalcAll();

        // ----- AJAX submit (updated with commission) -----
        $('#estimateForm').on('submit', function(e) {
            e.preventDefault();
            $('#saveBtn').prop('disabled', true);

            // Build payload with commission
            const payload = {
                estimate_no: $('#estimateNo').val(),
                estimate_date: $('#estimateDate').val(),
                customer_id: $('#customer').val(),
                sub_total: parseFloat($('#subTotal').val()) || 0,
                tax_id: $('#tax_id').val() === "" ? null : parseFloat($('#tax_id').val()),
                tax_amt: parseFloat($('#tax_amt').val()) || 0,
                packing_percent: parseFloat($('#packingPercent').val()) || 0,
                packing_amount: parseFloat($('#packingAmount').text()) || 0,
                commission: parseFloat($('#commission').val()) || 0, // Add commission to payload
                is_round_off: $('#roundOffCheck').is(':checked') ? 1 : 0,
                round_off_amount: parseFloat($('#roundOffAmount').text()) || 0,
                grand_total: parseFloat($('#grandTotal').text()) || 0,
                line_items: []
            };

            // Collect line items
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
                url = "{{ url('/') }}" + `/admin/custom/estimates/${estimateId}/update`;
                method = 'POST';
            } else {
                url = "{{ route('estimates.custom.store') }}";
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
    });
</script>
