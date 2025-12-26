<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            line-height: 1.4;
            direction: rtl;
        }

        .invoice-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 15px;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 15px;
        }

        .company-logo {
            height: 50px;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 10px;
            font-weight: bold;
            color: #262160;
            margin-bottom: 3px;
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 15px;
        }

        .invoice-title {
            font-size: 14px;
            font-weight: bold;
            color: #262160;
            margin-bottom: 5px;
        }

        .invoice-meta {
            font-size: 8px;
            color: #666;
            line-height: 1.5;
        }

        .customer-section {
            margin-bottom: 15px;
            font-size: 8px;
        }

        .section-label {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 9px;
        }

        .info-line {
            margin-bottom: 3px;
            color: #555;
        }

        .items-section {
            margin-bottom: 15px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        .items-table th {
            background-color: #f5f5f5;
            padding: 5px 3px;
            text-align: center;
            font-size: 8px;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }

        .items-table td {
            padding: 5px 3px;
            text-align: center;
            border-bottom: 1px dotted #ddd;
        }

        .items-table tbody tr:last-child td {
            border-bottom: 1px solid #ddd;
        }

        .text-right {
            text-align: right !important;
        }

        .totals-section {
            margin-top: 10px;
            font-size: 9px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
        }

        .total-label {
            color: #555;
        }

        .total-value {
            font-weight: bold;
        }

        .total-row.grand-total {
            border-top: 2px solid #262160;
            margin-top: 8px;
            padding-top: 8px;
            font-size: 11px;
            font-weight: bold;
        }

        .total-row.grand-total .total-label,
        .total-row.grand-total .total-value {
            color: #262160;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #999;
            text-align: center;
            font-size: 8px;
            color: #666;
        }

        .company-info {
            font-size: 7px;
            color: #777;
            line-height: 1.4;
            margin-top: 8px;
        }

        .thank-you {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 5px;
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
        <!-- Logo Section -->
        <div class="logo-section">
            <img src="{{ public_path('assets/bookly En 04.png') }}" alt="Bookly Logo" class="company-logo">
            <div class="company-name">{{ $invoice->company_details['name_ar'] }}</div>
        </div>

        <!-- Customer Section -->
        <div class="customer-section">
            <div class="section-label">تفاصيل الطلب</div>
            <div class="info-line">العميل: {{ $invoice->customer_name }}</div>
            <div class="info-line">رقم الموعد: #{{ $invoice->appointment_id }}</div>
            @if($invoice->customer_phone)
            <div class="info-line">الهاتف: {{ $invoice->customer_phone }}</div>
            @endif
        </div>

        <!-- Items Section -->
        <div class="items-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="text-right" style="width: 40%;">الخدمة</th>
                        <th style="width: 15%;">عدد</th>
                        <th style="width: 22%;">سعر</th>
                        <th style="width: 23%;">المجموع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr>
                        <td class="text-right">{{ $item['title'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ number_format($item['price'], 2) }}</td>
                        <td>{{ number_format($item['subtotal'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="total-row grand-total">
                <span class="total-label">المجموع الإجمالي</span>
                <span class="total-value">{{ number_format($invoice->total_amount, 2) }} ر.س</span>
            </div>
            <div style="text-align: center; font-size: 7px; color: #777; margin-top: 5px;">
                جميع الاسعار تشمل ١٥٪ ضريبة القيمة المضافة
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">شكراً لاختياركم {{ $invoice->company_details['name_ar'] }}</div>
        </div>

        <div style="text-align: center; margin-top: 15px; font-size: 8px; color: #999;">
            بطاقة الدفع
        </div>
    </div>
</body>
</html>
