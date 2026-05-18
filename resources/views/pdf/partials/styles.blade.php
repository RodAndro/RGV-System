<style>
    @page {
        margin: 25mm 15mm 25mm 15mm;
        @top-center {
            content: element(header);
        }
        @bottom-center {
            content: "Page " counter(page) " of " counter(pages);
            font-size: 9px;
            color: #888;
        }
        @bottom-right {
            content: "Generated: {{ date('M d, Y - g:i A') }}";
            font-size: 8px;
            color: #888;
        }
        @bottom-left {
            content: "© {{ date('Y') }} RGV Multi-Tech Services";
            font-size: 8px;
            color: #888;
        }
    }
    body { font-family: Arial, sans-serif; font-size: 12px; }
    .pdf-header {
        position: running(header);
        text-align: center;
        padding-bottom: 10px;
        border-bottom: 2px solid #74c365;
        margin-bottom: 5mm;
    }
    .pdf-header .logo { margin-bottom: 5px; }
    .pdf-header h1 { color: #468a3f; margin: 0; font-size: 16px; }
    .pdf-header p { color: #666; margin: 3px 0 0; font-size: 11px; }
    .pdf-footer-text { text-align: center; font-size: 9px; color: #888; margin-top: 10mm; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
    .info-item { padding: 10px; background-color: #f9fafb; border-radius: 4px; }
    .info-label { font-weight: bold; color: #468a3f; margin-bottom: 5px; }
    .info-value { color: #374151; }
    .section-title { font-size: 14px; font-weight: bold; color: #468a3f; margin: 20px 0 10px; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; }
    .badge-pending { background-color: #fef3c7; color: #92400e; }
    .badge-approved { background-color: #dcfce7; color: #166534; }
    .badge-rejected { background-color: #fee2e2; color: #991b1b; }
    .badge-completed { background-color: #dbeafe; color: #1e40af; }
    .badge-cancelled { background-color: #e5e7eb; color: #374151; }
    .badge-available { background-color: #dcfce7; color: #166534; }
    .badge-borrowed { background-color: #fef3c7; color: #92400e; }
    .badge-maintenance { background-color: #fed7aa; color: #9a3412; }
    .badge-damaged { background-color: #fee2e2; color: #991b1b; }
    .low-stock { color: #dc2626; font-weight: bold; }
    .remarks { background-color: #fef3c7; padding: 15px; border-radius: 4px; margin-top: 10px; }
    .signature-section { margin-top: 40px; padding-top: 20px; border-top: 2px solid #74c365; }
    .signature-line { border-bottom: 1px solid #000; margin-bottom: 5px; min-height: 40px; }
    .signature-label { font-size: 10px; color: #888; text-align: center; }
    .total-row { text-align: center; margin-top: 20px; color: #666; font-weight: bold; }
</style>
