<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 20px;
        }

        .company-details {
            text-align: left;
        }

        .company-logo {
            height: 60px;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #262160;
            margin-bottom: 10px;
        }

        .company-info {
            font-size: 12px;
            color: #666;
            line-height: 1.6;
        }

        .invoice-title {
            text-align: right;
            margin-top: 10px;
        }

        .invoice-title h1 {
            font-size: 32px;
            color: #262160;
            margin-bottom: 5px;
        }

        .invoice-number {
            font-size: 14px;
            color: #666;
        }

        .invoice-date {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .customer-details {
            background-color: #f8f9fa;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
        }

        .customer-details h3 {
            color: #262160;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .customer-info {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-item {
            display: table-cell;
            font-size: 14px;
            padding: 5px 0;
            width: 50%;
        }

        .info-label {
            font-weight: bold;
            color: #666;
        }

        .info-value {
            color: #333;
            margin-left: 5px;
        }

        .items-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }

        .items-table thead {
            background-color: #262160;
            color: white;
        }

        .items-table th {
            padding: 12px;
            text-align: left;
            font-size: 14px;
            font-weight: 500;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }

        .items-table tbody tr:last-child td {
            border-bottom: 2px solid #e0e0e0;
        }

        .text-right {
            text-align: right;
        }

        .totals-section {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }

        .totals-table {
            width: 350px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }

        .total-label {
            color: #666;
        }

        .total-value {
            font-weight: 500;
            color: #333;
        }

        .total-row.subtotal {
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 10px;
            padding-bottom: 10px;
        }

        .total-row.grand-total {
            border-top: 2px solid #262160;
            margin-top: 10px;
            padding-top: 10px;
            font-size: 18px;
            font-weight: bold;
        }

        .total-row.grand-total .total-label {
            color: #262160;
        }

        .total-row.grand-total .total-value {
            color: #262160;
        }

        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        .vat-info {
            margin-top: 10px;
            font-size: 12px;
            color: #666;
        }

        @media print {
            .invoice-container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header Section -->
        <div class="header">
            <div class="company-details">
                <img src="{{ public_path('assets/bookly En 04.png') }}" alt="Bookly Logo" class="company-logo">
                <div class="company-info">
                    {{ $invoice->company_details['address'] }}<br>
                    {{ $invoice->company_details['city'] }}, {{ $invoice->company_details['country'] }}<br>
                    {{ $invoice->company_details['postal_code'] }}<br>
                    Phone: {{ $invoice->company_details['phone'] }}<br>
                    Email: {{ $invoice->company_details['email'] }}<br>
                    VAT Number: {{ $invoice->company_details['vat_number'] }}<br>
                    CR Number: {{ $invoice->company_details['cr_number'] }}
                </div>
            </div>
            <div class="invoice-title">
                <h1>INVOICE</h1>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                <div class="invoice-date">Date: {{ $invoice->invoice_date->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- Customer Details -->
        <div class="customer-details">
            <h3>Bill To:</h3>
            <div class="customer-info">
                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Customer Name:</span><span class="info-value">{{ $invoice->customer_name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Appointment ID:</span><span class="info-value">#{{ $invoice->appointment_id }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Email:</span><span class="info-value">{{ $invoice->customer_email ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone:</span><span class="info-value">{{ $invoice->customer_phone ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Service Description</th>
                    <th style="width: 15%;" class="text-right">Quantity</th>
                    <th style="width: 17.5%;" class="text-right">Unit Price</th>
                    <th style="width: 17.5%;" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item['title'] }}</td>
                    <td class="text-right">{{ $item['quantity'] }}</td>
                    <td class="text-right">SAR {{ number_format($item['price'], 2) }}</td>
                    <td class="text-right">SAR {{ number_format($item['subtotal'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="totals-table">
                <div class="total-row subtotal">
                    <span class="total-label">Total without VAT:</span>
                    <span class="total-value">SAR {{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">VAT ({{ $vatPercentage }}%):</span>
                    <span class="total-value">SAR {{ number_format($invoice->vat_amount, 2) }}</span>
                </div>
                <div class="total-row grand-total">
                    <span class="total-label">Total Amount:</span>
                    <span class="total-value">SAR {{ number_format($invoice->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for choosing {{ $invoice->company_details['name'] }}!</p>
            <p class="vat-info">
                This invoice includes {{ $vatPercentage }}% VAT as per Saudi Arabia tax regulations.<br>
                For any queries, please contact us at {{ $invoice->company_details['email'] }}
            </p>
        </div>
    </div>
</body>
</html>