<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحميل تطبيق بوكلي</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Cairo', sans-serif;
        }

        .gradient-bg {
            background: #ffffff;
        }

        .provider-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .download-btn {
            transition: all 0.3s ease;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .provider-image {
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Bookly Logo -->
        <div class="text-center mb-8">
            <img src="{{ asset('assets/bookly En 04.png') }}" alt="Bookly Logo" class="mx-auto h-16 md:h-20 mb-4">
        </div>

        <!-- Main Card -->
        <div class="provider-card rounded-3xl shadow-2xl p-8 text-center">
            <!-- Provider Image -->
            @if($providerImage)
                <div class="mb-6">
                    <img src="{{ $providerImage }}"
                         alt="{{ $providerName }}"
                         class="provider-image w-32 h-32 rounded-full mx-auto">
                </div>
            @endif

            <!-- Provider Name -->
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-3">
                {{ $providerName }}
            </h1>

            <!-- Call to Action -->
            <p class="text-gray-600 mb-2 text-lg font-medium">
                يدعوك إلى بوكلي
            </p>

            <p class="text-gray-500 mb-8 text-sm">
                قم بتحميل التطبيق لحجز موعدك والاستمتاع بالمزايا الحصرية
            </p>

            <!-- Download Buttons -->
            <div class="space-y-4">
                <!-- App Store Button -->
                <a href="#" class="download-btn block w-full bg-black text-white rounded-xl py-4 px-6 flex items-center justify-center space-x-reverse space-x-3 hover:bg-gray-800">
                    <div class="text-right">
                        <div class="text-xs">حمّل على</div>
                        <div class="text-xl font-semibold">App Store</div>
                    </div>
                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.71 19.5C17.88 20.74 17 21.95 15.66 21.97C14.32 22 13.89 21.18 12.37 21.18C10.84 21.18 10.37 21.95 9.09997 22C7.78997 22.05 6.79997 20.68 5.95997 19.47C4.24997 17 2.93997 12.45 4.69997 9.39C5.56997 7.87 7.12997 6.91 8.81997 6.88C10.1 6.86 11.32 7.75 12.11 7.75C12.89 7.75 14.37 6.68 15.92 6.84C16.57 6.87 18.39 7.1 19.56 8.82C19.47 8.88 17.39 10.1 17.41 12.63C17.44 15.65 20.06 16.66 20.09 16.67C20.06 16.74 19.67 18.11 18.71 19.5ZM13 3.5C13.73 2.67 14.94 2.04 15.94 2C16.07 3.17 15.6 4.35 14.9 5.19C14.21 6.04 13.07 6.7 11.95 6.61C11.8 5.46 12.36 4.26 13 3.5Z"/>
                    </svg>
                </a>

                <!-- Google Play Button -->
                <a href="#" class="download-btn block w-full bg-black text-white rounded-xl py-4 px-6 flex items-center justify-center space-x-reverse space-x-3 hover:bg-gray-800">
                    <div class="text-right">
                        <div class="text-xs">حمّل على</div>
                        <div class="text-xl font-semibold">Google Play</div>
                    </div>
                    <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.5,12.92 20.16,13.19L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                    </svg>
                </a>
            </div>

            <!-- Additional Info -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-gray-500 text-sm">
                    متاح لأجهزة iOS و Android
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-gray-600 text-sm">
                &copy; {{ date('Y') }} بوكلي. جميع الحقوق محفوظة.
            </p>
        </div>
    </div>
</body>
</html>
