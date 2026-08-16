<style>
    html, body {
        background: #fff;
        margin: 0;
        padding: 0;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    body > :not(.invoice-print) {
        display: none !important;
    }

    .invoice-print {
        font-family: Arial, sans-serif;
        font-size: 14px;
        color: #000 !important;
        padding: 4px 8px;
    }

    .invoice-print * {
        color: #000 !important;
    }

    .header-title {
        font-size: 11px;
        font-weight: bold;
        margin-bottom: 1px;
    }

    .header-sub {
        font-size: 9px;
        margin-bottom: 1px;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
    }

    .report-table th {
        border: 1px solid #000;
        padding: 4px 3px;
        text-align: center;
        background-color: #e0e0e0;
        font-weight: bold;
        font-size: 8px;
        white-space: nowrap;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .report-table td {
        border: 1px solid #000;
        padding: 4px 3px;
        font-size: 8px;
        vertical-align: top;
        line-height: 1.4;
    }

    .row-total td {
        background-color: #DBEAFE !important;
        color: #1E40AF !important;
        font-weight: bold;
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .row-grand-total td {
        background-color: #FFFF99 !important;
        color: #000 !important;
        font-weight: bold;
        border-top: 2px solid #000;
        border-bottom: 2px solid #000;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .text-right { text-align: right; }
    .text-center { text-align: center; }

    .signature-section {
        margin-top: 30px;
        width: 70%;
        float: left;
    }

    .signature-table {
        width: 100%;
        border-collapse: collapse;
    }

    .signature-table td {
        width: 25%;
        text-align: left;
        vertical-align: bottom;
        padding: 0;
        border: none;
    }

    .sig-label {
        font-size: 9px;
        font-weight: normal;
        margin-bottom: 50px;
        display: block;
    }

    .sig-line {
        border-top: 1px solid #000;
        width: 110px;
        display: block;
    }

    @media print {
        @page {
            size: 330mm 215mm landscape;
            margin: 4mm;
        }

        body {
            margin: 0;
        }
    }
</style>
    