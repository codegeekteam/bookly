<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم تحويل المبلغ المستحق</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            direction: rtl;
            text-align: right;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #4CAF50;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #4CAF50;
            margin: 0;
            font-size: 28px;
        }
        .content {
            color: #333;
            line-height: 1.6;
        }
        .info-box {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .appointments-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .appointments-table th {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            text-align: right;
        }
        .appointments-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
        }
        .appointments-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-amount {
            font-size: 24px;
            color: #4CAF50;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            background-color: #f0f8f0;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ تم تحويل المبلغ المستحق</h1>
        </div>

        <div class="content">
            <p>مرحباً {{ $payout->serviceProvider->name ?? 'عزيزنا المزود' }}،</p>

            <p>يسعدنا إبلاغك بأنه تم تحويل المبلغ المستحق بنجاح.</p>

            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">رقم العملية:</span>
                    <span class="info-value">#{{ $payout->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">تاريخ التحويل:</span>
                    <span class="info-value">{{ $payout->transferred_at->format('Y-m-d H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">تاريخ الاستحقاق:</span>
                    <span class="info-value">{{ $payout->due_date->format('Y-m-d') }}</span>
                </div>
            </div>

            <div class="total-amount">
                المبلغ الإجمالي: {{ number_format($payout->total_amount, 2) }} ريال
            </div>

            <h3>تفاصيل المواعيد المشمولة:</h3>

            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>رقم الموعد</th>
                        <th>تاريخ الموعد</th>
                        <th>نوع الدفع</th>
                        <th>المبلغ (ريال)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payout->deferredPayouts as $deferredPayout)
                    <tr>
                        <td>#{{ $deferredPayout->appointment_id }}</td>
                        <td>
                            @php
                                $firstService = $deferredPayout->appointment->appointmentServices->first();
                                $date = $firstService?->date;
                                if ($date instanceof \Carbon\Carbon) {
                                    echo $date->format('Y-m-d');
                                } elseif (is_string($date)) {
                                    echo $date;
                                } else {
                                    echo 'N/A';
                                }
                            @endphp
                        </td>
                        <td>
                            @if($deferredPayout->payment_type === 'deposit')
                                عربون
                            @else
                                المبلغ المتبقي
                            @endif
                        </td>
                        <td>{{ number_format($deferredPayout->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p style="margin-top: 30px;">
                إذا كان لديك أي استفسار، يرجى التواصل معنا.
            </p>

            <p>
                مع تحياتنا،<br>
                <strong>فريق Bookly</strong>
            </p>
        </div>

        <div class="footer">
            <p>هذه رسالة تلقائية، يرجى عدم الرد عليها.</p>
        </div>
    </div>
</body>
</html>
