<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\SystemSetting::getValue('sales_invoice_print_title', 'SALES INVOICE') }} - {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            background: white;
            color: black;
        }
        .receipt {
            width: 320px;
            max-width: 320px;
            margin: 0 auto;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .receipt-title {
            font-size: 13px;
            font-weight: bold;
            margin: 6px 0;
            text-transform: uppercase;
        }
        .receipt-info div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 10px;
        }
        .receipt-info .label { font-weight: bold; }
        .item {
            margin-bottom: 8px;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 5px;
            font-size: 10px;
        }
        .item-name { font-weight: bold; margin-bottom: 2px; }
        .item-line {
            display: flex;
            justify-content: space-between;
        }
        .totals {
            border-top: 1px dashed #000;
            padding-top: 8px;
            margin-top: 8px;
            font-size: 10px;
        }
        .totals .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .totals .final {
            font-weight: bold;
            font-size: 12px;
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #000;
        }
        .footer {
            text-align: center;
            margin-top: 12px;
            font-size: 9px;
        }
        .no-print {
            text-align: center;
            margin: 12px 0;
        }
        @media print {
            @page { margin: 0; }
            body { margin: 6mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
@php
    $currency = strtoupper(trim((string) ($invoice->currency ?: 'TZS')));
@endphp
    <div class="receipt">
        <div class="header">
            <div class="company-name">{{ $invoice->company->name ?? config('app.name') }}</div>
            <div class="receipt-title">{{ \App\Models\SystemSetting::getValue('sales_invoice_print_title', 'SALES INVOICE') }}</div>
            @if($invoice->branch)
                <div>{{ $invoice->branch->name }}</div>
            @endif
        </div>

        <div class="receipt-info">
            <div>
                <span class="label">Invoice:</span>
                <span>{{ $invoice->invoice_number }}</span>
            </div>
            <div>
                <span class="label">Date:</span>
                <span>{{ $invoice->invoice_date?->format('d/m/Y') ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="label">Customer:</span>
                <span>{{ $invoice->customer->name ?? 'N/A' }}</span>
            </div>
            @if($invoice->reference_no)
            <div>
                <span class="label">Ref:</span>
                <span>{{ $invoice->reference_no }}</span>
            </div>
            @endif
        </div>

        <div class="items">
            @foreach($invoice->items as $item)
            <div class="item">
                <div class="item-name">{{ $item->item_name }}</div>
                <div class="item-line">
                    <span>{{ number_format((float) $item->quantity, 2) }} x {{ number_format((float) $item->unit_price, 2) }}</span>
                    <span>{{ number_format((float) $item->line_total, 2) }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="totals">
            <div class="row">
                <span>Subtotal:</span>
                <span>{{ number_format((float) $invoice->subtotal, 2) }} {{ $currency }}</span>
            </div>
            @if((float) $invoice->vat_amount > 0)
            <div class="row">
                <span>VAT:</span>
                <span>{{ number_format((float) $invoice->vat_amount, 2) }} {{ $currency }}</span>
            </div>
            @endif
            @if((float) $invoice->discount_amount > 0)
            <div class="row">
                <span>Discount:</span>
                <span>-{{ number_format((float) $invoice->discount_amount, 2) }} {{ $currency }}</span>
            </div>
            @endif
            <div class="row final">
                <span>TOTAL:</span>
                <span>{{ number_format((float) $invoice->total_amount, 2) }} {{ $currency }}</span>
            </div>
            <div class="row">
                <span>Paid:</span>
                <span>{{ number_format((float) $invoice->paid_amount, 2) }} {{ $currency }}</span>
            </div>
            <div class="row">
                <span>Balance:</span>
                <span>{{ number_format((float) $invoice->balance_due, 2) }} {{ $currency }}</span>
            </div>
        </div>

        <div class="footer">
            <div>Thank you</div>
            <div>Printed: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="no-print">
        <button type="button" onclick="window.print()">Print again</button>
    </div>

    <script nonce="{{ $cspNonce ?? '' }}">
        window.onload = function () {
            setTimeout(function () { window.print(); }, 250);
        };
    </script>
</body>
</html>
