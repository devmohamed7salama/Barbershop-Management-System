<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة رقم {{ $invoice->invoice_number ?? '#INV-' . $invoice->id }} - صالون المقص الذهبي</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #D4AF37;
            --dark: #1A1A1A;
            --light: #F9F9F9;
            --text-dark: #2B2B2B;
            --text-muted: #747878;
            --border-color: #c4c7c7;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #121212 0%, #1e1e1e 100%);
            color: #ffffff;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            direction: rtl;
        }

        .receipt-container {
            background-color: #ffffff;
            color: var(--text-dark);
            width: 100%;
            max-width: 450px;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            border-top: 8px solid var(--primary);
            position: relative;
        }

        /* Decorative top notch for thermal/coupon feel */
        .receipt-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 5px;
            background-color: var(--dark);
            border-bottom-left-radius: 6px;
            border-bottom-right-radius: 6px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 800;
            color: var(--dark);
            margin: 0 0 5px 0;
        }

        .header p {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0;
            font-weight: 600;
        }

        .divider {
            border-top: 2px dashed var(--border-color);
            margin: 20px 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            font-size: 13px;
            line-height: 1.6;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-item .label {
            font-weight: 700;
            color: var(--text-muted);
            font-size: 11px;
            margin-bottom: 2px;
        }

        .info-item .value {
            font-weight: 600;
            color: var(--dark);
        }

        .info-item.full-width {
            grid-column: span 2;
        }

        .items-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 10px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .items-table th {
            text-align: right;
            padding: 8px 0;
            color: var(--text-muted);
            font-weight: 700;
            border-bottom: 2px solid var(--border-color);
        }

        .items-table td {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            color: var(--dark);
            font-weight: 600;
        }

        .items-table .price-col {
            text-align: left;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            margin-top: 10px;
            font-size: 16px;
        }

        .total-row .label {
            font-weight: 800;
            color: var(--dark);
        }

        .total-row .value {
            font-weight: 800;
            color: var(--primary);
            font-size: 20px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .footer p {
            margin: 4px 0;
        }

        /* Non-printable buttons section for web preview */
        .actions-panel {
            margin-top: 25px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn {
            font-family: 'Cairo', sans-serif;
            font-weight: 700;
            font-size: 13px;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-print {
            background-color: var(--dark);
            color: #ffffff;
        }

        .btn-print:hover {
            background-color: #333333;
        }

        .btn-close-window {
            background-color: #e0e0e0;
            color: var(--text-dark);
        }

        .btn-close-window:hover {
            background-color: #d0d0d0;
        }

        /* Print styles */
        @media print {
            body {
                background: none !important;
                color: #000000 !important;
                padding: 0 !important;
                display: block !important;
                min-height: auto !important;
            }

            .receipt-container {
                box-shadow: none !important;
                border-radius: 0 !important;
                border-top: none !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .receipt-container::before {
                display: none !important;
            }

            .actions-panel {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <h1>صالون المقص الذهبي</h1>
            <p>نظام إدارة الصالون والمواعيد الفاخر</p>
        </div>

        <div class="divider"></div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-item">
                <span class="label">رقم الفاتورة</span>
                <span class="value">{{ $invoice->invoice_number ?? '#INV-' . $invoice->id }}</span>
            </div>
            <div class="info-item">
                <span class="label">تاريخ الصدور</span>
                <span class="value">{{ $invoice->created_at->format('Y-m-d h:i A') }}</span>
            </div>
            <div class="info-item">
                <span class="label">العميل</span>
                <span class="value">{{ $invoice->customer?->customer_name ?? 'عميل زائر' }}</span>
            </div>
            <div class="info-item">
                <span class="label">رقم الهاتف</span>
                <span class="value">{{ $invoice->customer?->customer_phone ?? 'بدون هاتف' }}</span>
            </div>
            <div class="info-item full-width">
                <span class="label">الحلاق المسؤول</span>
                <span class="value">{{ $invoice->barber?->barber_name ?? 'غير محدد' }}</span>
            </div>
            @if($invoice->appointment)
            <div class="info-item full-width">
                <span class="label">تفاصيل الحجز</span>
                <span class="value">{{ $invoice->appointment->appointment_date }} في تمام الساعة {{ $invoice->appointment->appointment_time ? date('h:i A', strtotime($invoice->appointment->appointment_time)) : '' }}</span>
            </div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Services -->
        <div class="items-title">الخدمات المقدمة:</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>الخدمة</th>
                    <th class="price-col">السعر</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->invoiceitems as $item)
                <tr>
                    <td>{{ $item->service?->service_name ?? 'خدمة صالون' }}</td>
                    <td class="price-col">{{ number_format($item->price, 2) }} ج.م</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider" style="border-top-style: solid; border-top-color: var(--dark);"></div>

        <!-- Total -->
        <div class="total-row">
            <span class="label">الإجمالي الكلي:</span>
            <span class="value">{{ number_format($invoice->total_price, 2) }} ج.م</span>
        </div>

        <div class="divider"></div>

        <!-- Rating Section with QR Code -->
        <div class="rating-qr-section" style="text-align: center; margin: 20px 0; padding: 15px; border-radius: 12px; background-color: #faf8f0; border: 1px solid #eadeb8;">
            <p style="font-weight: 700; color: #1A1A1A; margin: 0 0 5px 0; font-size: 14px; font-family: 'Cairo', sans-serif;">شاركنا رأيك وقيم زيارتك!</p>
            <p style="font-size: 11px; color: var(--text-muted); margin: 0 0 12px 0; font-family: 'Cairo', sans-serif;">امسح الكود أدناه لتقييم صالوننا والحلاق الخاص بك</p>
            
            @php
                $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
                $ratingUrl = rtrim($frontendUrl, '/') . '/rate/' . $invoice->id;
            @endphp
            
            <div style="background-color: white; display: inline-block; padding: 10px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 8px;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data={{ urlencode($ratingUrl) }}" alt="QR Code لتقييم الخدمة" style="display: block; width: 130px; height: 130px;" />
            </div>
            
            <div >
                <a style="font-size: 14px;text-decoration: none; font-weight: 600; color: #D4AF37; font-family: 'Cairo', sans-serif; word-break: break-all;" href="{{ $ratingUrl }}" target="_blank">لينك صفحة التقيم</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>نشكركم على زيارتكم لصالوننا الفاخر!</p>
            <p>تمت العملية بنجاح - تم الدفع</p>
        </div>

        <!-- Web Actions Panel (hidden in print) -->
        <div class="actions-panel">
            <button class="btn btn-print" onclick="window.print()">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                </svg>
                <span>طباعة</span>
            </button>
            <button class="btn btn-close-window" onclick="window.close()">
                <span>إغلاق النافذة</span>
            </button>
        </div>
    </div>

    <!-- Auto Print Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('print') && urlParams.get('print') === 'true') {
                // Auto trigger window.print
                setTimeout(function() {
                    window.print();
                }, 500);
            }
        });
    </script>
</body>
</html>
