<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم تأكيد الموعد</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        h2 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 10px;
        }
        p {
            margin: 15px 0;
        }
        .info-line {
            margin: 8px 0;
            padding-right: 0;
        }
        .label {
            font-weight: 600;
            color: #555;
            min-width: 140px;
            display: inline-block;
        }
        .value {
            color: #333;
        }
        .section {
            margin: 25px 0;
            padding: 20px 0;
            border-top: 1px solid #e0e0e0;
        }
        .service {
            margin: 15px 20px 15px 0;
        }
        .service-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .cancellation {
            margin: 20px 0;
            padding: 15px;
            background-color: #fffbf0;
            border-right: 3px solid #f39c12;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            color: #888;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body dir="rtl" style="direction: rtl; text-align: right;">
    <div style="direction: rtl; text-align: right;">
        <h2 style="text-align: right;">تم إلغاء موعدك</h2>

        <p style="text-align: right;">عزيزتي {{ $appointment->customer->first_name }} {{ $appointment->customer->last_name }}،</p>

        <p style="text-align: right;">تم رفض الموعد</p>

        <div class="section" style="direction: rtl; text-align: right;">
            <div class="info-line" style="text-align: right;">
                <span class="label">رقم الموعد:</span>
                <span class="value">#{{ $appointment->id }}</span>
            </div>

            <div class="info-line" style="text-align: right;">
                <span class="label">تم الإلغاء :</span>
                {{-- <span class="value">Your appointment #  {{ $appointment->id }}  with {{ $appointment->serviceProvider->name }} on {{ \Carbon\Carbon::parse($serviceDate)->format('l') }} & {{ \Carbon\Carbon::parse($serviceDate)->format('d-m-Y') }} & {{ $appointment->services[0]->pivot->start_time }} has been cancelled due to no action. You will be refunded the full deposit amount of  SAR {{ $appointment->deposit_amount }} to your bank account within ' . config('app.refund_days') . ' days. <br>تم إلغاء موعدك رقم {{ $appointment->id }} مع {{ $appointment->serviceProvider->name }} بتاريخ {{ \Carbon\Carbon::parse($serviceDate)->format('l') }} و {{ \Carbon\Carbon::parse($serviceDate)->format('d-m-Y') }} و {{ $appointment->services[0]->pivot->start_time }} لعدم اتخاذ أي إجراء. سيتم رد مبلغ التأمين بالكامل وقدره {{ $appointment->deposit_amount }} ريال سعودي إلى حسابك البنكي خلال 7 أيام</span> --}}
                <span class="value">تم إلغاء موعدك رقم {{ $appointment->id }} مع {{ $appointment->serviceProvider->name }} بتاريخ {{ \Carbon\Carbon::parse($serviceDate)->format('l') }} و {{ \Carbon\Carbon::parse($serviceDate)->format('d-m-Y') }} و {{ $appointment->services[0]->pivot->start_time }} لعدم اتخاذ أي إجراء. سيتم رد مبلغ التأمين بالكامل وقدره {{ $appointment->deposit_amount }} ريال سعودي إلى حسابك البنكي خلال 7 أيام</span>
            </div>
       
        </div>

        <p style="text-align: right;">نأسف للإزعاج</p>

        <div class="footer">
            <p>هذا بريد إلكتروني تلقائي من بوكلي.</p>
            <p>&copy; {{ date('Y') }} بوكلي. جميع الحقوق محفوظة.</p>
        </div>
    </div>
</body>
</html>
