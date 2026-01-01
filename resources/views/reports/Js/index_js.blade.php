<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (required for daterangepicker) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Moment.js (required for daterangepicker) -->
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>

<!-- daterangepicker -->
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (required for daterangepicker) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Moment.js (required for daterangepicker) -->
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>

<!-- daterangepicker -->
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    // Initialize Select2 for agent dropdown
    $(document).ready(function() {
        // Select2 initialization
        $('.select2-agent').select2({
            // theme: 'bootstrap-5',
            placeholder: 'Search for an agent...',
            allowClear: true,
            width: '100%',
            closeOnSelect: true
        });
        // Auto-focus search input when dropdown opens

        // Date range picker initialization with dd-mm-yyyy format
        $('#date-range-picker').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'DD-MM-YYYY', // Changed from YYYY-MM-DD to DD-MM-YYYY
                cancelLabel: 'Clear',
                applyLabel: 'Apply',
                fromLabel: 'From',
                toLabel: 'To',
                customRangeLabel: 'Custom',
                daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ],
                firstDay: 1
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'),
                    moment().subtract(1, 'month').endOf('month')
                ]
            },
            startDate: $('#from-date').val() ? moment($('#from-date').val(), 'YYYY-MM-DD') : moment()
                .subtract(30, 'days'),
            endDate: $('#to-date').val() ? moment($('#to-date').val(), 'YYYY-MM-DD') : moment(),
            maxDate: moment()
        });

        // Update the display and hidden inputs when dates are selected
        $('#date-range-picker').on('apply.daterangepicker', function(ev, picker) {
            // Display format: dd-mm-yyyy to dd-mm-yyyy
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' to ' + picker.endDate.format(
                'DD-MM-YYYY'));

            // Hidden inputs format: YYYY-MM-DD (for database/backend)
            $('#from-date').val(picker.startDate.format('YYYY-MM-DD'));
            $('#to-date').val(picker.endDate.format('YYYY-MM-DD'));
        });

        // Clear the display and hidden inputs when dates are cleared
        $('#date-range-picker').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#from-date').val('');
            $('#to-date').val('');
        });

        // Initialize the display if we have values (convert from YYYY-MM-DD to DD-MM-YYYY)
        if ($('#from-date').val() && $('#to-date').val()) {
            const fromDate = moment($('#from-date').val(), 'YYYY-MM-DD');
            const toDate = moment($('#to-date').val(), 'YYYY-MM-DD');
            $('#date-range-picker').val(fromDate.format('DD-MM-YYYY') + ' to ' + toDate.format('DD-MM-YYYY'));
        }

    });

    // Set default dates for date inputs (fallback for when daterangepicker doesn't work)
    document.addEventListener('DOMContentLoaded', function() {
        // Only set defaults if no dates are already selected
        if (!$('#from-date').val()) {
            // Set default "to date" as today
            const today = new Date().toISOString().split('T')[0];

            // Set default "from date" as 30 days ago
            const thirtyDaysAgo = new Date();
            thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
            const fromDateDefault = thirtyDaysAgo.toISOString().split('T')[0];

            // Update hidden inputs
            document.getElementById('from-date').value = fromDateDefault;
            document.getElementById('to-date').value = today;

            // Update date range picker display
            if ($('#date-range-picker').length) {
                $('#date-range-picker').val(fromDateDefault + ' to ' + today);
            }
        }
    });
</script>
<script>
    // Preserve filter parameters in pagination links
    document.addEventListener('DOMContentLoaded', function() {
        // Get current filter values
        const agentId = document.getElementById('agent-select') ? document.getElementById('agent-select')
            .value : '';
        const fromDate = document.getElementById('from-date') ? document.getElementById('from-date').value : '';
        const toDate = document.getElementById('to-date') ? document.getElementById('to-date').value : '';

        // Update all pagination links with filter parameters
        const paginationLinks = document.querySelectorAll('.pagination a.page-link');

        paginationLinks.forEach(link => {
            if (link.href) {
                const url = new URL(link.href);

                // Add or update filter parameters
                if (agentId) {
                    url.searchParams.set('agent_id', agentId);
                } else {
                    url.searchParams.delete('agent_id');
                }

                if (fromDate) {
                    url.searchParams.set('from_date', fromDate);
                } else {
                    url.searchParams.delete('from_date');
                }

                if (toDate) {
                    url.searchParams.set('to_date', toDate);
                } else {
                    url.searchParams.delete('to_date');
                }

                // Update the link href
                link.href = url.toString();
            }
        });

        // Also handle any other links that might lose filters
        const clearFilterLink = document.querySelector('a[href*="reports.agenttransactions"]');
        if (clearFilterLink && clearFilterLink.textContent.includes('Clear')) {
            // Keep clear filter link as is (it should clear all filters)
        }
    });
</script>
<script>
    // Print functionality
    document.getElementById('print-btn').addEventListener('click', function() {
        window.print();
    });

    // PDF functionality
    document.addEventListener('DOMContentLoaded', function() {
        const printBtn = document.getElementById('print-btn');

        if (printBtn) {
            printBtn.addEventListener('click', function(e) {
                e.preventDefault();

                // Get the PDF URL (same as view PDF)
                const agentId = document.getElementById('agent-id-hidden')?.value ||
                    document.querySelector('select[name="agent_id"]')?.value ||
                    '{{ request('agent_id') }}';
                const fromDate = document.getElementById('from-date')?.value ||
                    '{{ $fromDate }}';
                const toDate = document.getElementById('to-date')?.value ||
                    '{{ $toDate }}';

                let pdfUrl = '{{ route('reports.agenttransactions.pdf') }}?print=1&';

                if (agentId) pdfUrl += `agent_id=${agentId}&`;
                if (fromDate) pdfUrl += `from_date=${fromDate}&`;
                if (toDate) pdfUrl += `to_date=${toDate}&`;

                pdfUrl = pdfUrl.slice(0, -1); // Remove trailing & or ?

                // Open PDF in new window
                const printWindow = window.open(pdfUrl, '_blank');

                // Try to trigger print after PDF loads (may not work due to browser security)
                // setTimeout(() => {
                //     if (printWindow) {
                //         printWindow.print();
                //     }
                // }, 1000);
            });
        }
        const viewPdfBtn = document.getElementById('view-pdf-btn');
        const downloadPdfBtn = document.getElementById('download-pdf-btn');

        function getPdfUrl(action) {
            const agentId = document.getElementById('agent-id-hidden')?.value ||
                document.querySelector('select[name="agent_id"]')?.value ||
                '{{ request('agent_id') }}';
            const fromDate = document.getElementById('from-date')?.value || '{{ $fromDate }}';
            const toDate = document.getElementById('to-date')?.value || '{{ $toDate }}';

            let url = '{{ route('reports.agenttransactions.pdf') }}?';

            if (agentId) url += `agent_id=${agentId}&`;
            if (fromDate) url += `from_date=${fromDate}&`;
            if (toDate) url += `to_date=${toDate}&`;
            if (action === 'download') url += `download=1&`;

            return url.slice(0, -1); // Remove trailing & or ?
        }

        // View PDF - opens in new tab
        if (viewPdfBtn) {
            viewPdfBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = getPdfUrl('view');
                window.open(url, '_blank');
            });
        }
        downloadPdfBtn.href = getPdfUrl('download');
    });
</script>
