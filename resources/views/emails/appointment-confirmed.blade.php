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
        <h2 style="text-align: right;">تم تأكيد الموعد</h2>

        <p style="text-align: right;">عزيزتي {{ $appointment->customer->first_name }} {{ $appointment->customer->last_name }}،</p>

        <p style="text-align: right;">تم تأكيد موعدك مع <strong>{{ $appointment->serviceProvider->name }}</strong>.</p>

        <div class="section" style="direction: rtl; text-align: right;">
            <div class="info-line" style="text-align: right;">
                <span class="label">رقم الموعد:</span>
                <span class="value">#{{ $appointment->id }}</span>
            </div>

            <div class="info-line" style="text-align: right;">
                <span class="label">مقدم الخدمة:</span>
                <span class="value">{{ $appointment->serviceProvider->name }}</span>
            </div>

            @if($appointment->serviceProvider->phone_number)
            <div class="info-line" style="text-align: right;">
                <span class="label">هاتف مقدم الخدمة:</span>
                <span class="value">{{ $appointment->serviceProvider->phone_number }}</span>
            </div>
            @endif

            <div class="info-line" style="text-align: right;">
                <span class="label">المبلغ الإجمالي:</span>
                <span class="value">{{ number_format($appointment->total, 2) }} ريال</span>
            </div>

            @if($appointment->payment_status)
            <div class="info-line" style="text-align: right;">
                <span class="label">حالة الدفع:</span>
                <span class="value" style="text-transform: capitalize;">{{ $appointment->payment_status }}</span>
            </div>
            @endif
        </div>

        <div class="section" style="direction: rtl; text-align: right;">
            <strong>الخدمات:</strong>
            @foreach($appointment->appointmentServices as $service)
            <div class="service" style="text-align: right;">
                <div class="service-name">{{ $service->service->title }}</div>
                <div class="info-line" style="text-align: right;">
                    <span class="label">التاريخ:</span>
                    <span class="value">{{ \Carbon\Carbon::parse($service->date)->locale('ar')->translatedFormat('l، j F Y') }}</span>
                </div>
                <div class="info-line" style="text-align: right;">
                    <span class="label">الوقت:</span>
                    <span class="value">{{ \Carbon\Carbon::parse($service->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($service->end_time)->format('g:i A') }}</span>
                </div>
                @if($service->number_of_beneficiaries)
                <div class="info-line" style="text-align: right;">
                    <span class="label">عدد المستفيدين:</span>
                    <span class="value">{{ $service->number_of_beneficiaries }}</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        @if($appointment->serviceProvider->cancellation_enabled)
        <div class="cancellation" style="direction: rtl; text-align: right;">
            <strong>سياسة الإلغاء</strong>
            <p style="margin: 10px 0 0 0; text-align: right;">يُسمح بالإلغاء قبل {{ $appointment->serviceProvider->cancellation_hours_before }} ساعة من موعد الحجز.</p>
        </div>
        @endif

        @if($appointment->comment)
        <div class="section" style="direction: rtl; text-align: right;">
            <div class="info-line" style="text-align: right;">
                <span class="label">ملاحظتك:</span>
            </div>
            <p style="text-align: right;">{{ $appointment->comment }}</p>
        </div>
        @endif

        <p style="text-align: right;">نتطلع لرؤيتك!</p>

        <div class="footer">
            <p>هذا بريد إلكتروني تلقائي من بوكلي.</p>
            <p>&copy; {{ date('Y') }} بوكلي. جميع الحقوق محفوظة.</p>
        </div>
    </div>
</body>
</html>
