{{--@php--}}
{{--    $msisdn = request()->query('msisdn');--}}
{{--    $clickId = request()->query('ClickID');--}}
{{--    $tracking = \App\Models\Tracking::where('msisdn', $msisdn)->where('anti_fraud_click_id', $clickId)->first();--}}
{{--    $source = $tracking->ProjectSource->source->name;--}}
{{--    $isGoogleAds = in_array($source, ['Google-Ads']);--}}
{{--    $isTiktok = in_array($source, ['Tiktok']);--}}
{{--@endphp--}}
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Hero</title>
    <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-N7FQGPP7');</script>
    <!-- End Google Tag Manager -->
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N7FQGPP7"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<div class="lang-switcher">
    <button id="lang-toggle" class="lang-btn" onclick="toggleLanguage()">كردی</button>
</div>

<div class="container">
    <div class="card">
        <img class='logo' alt="logo" src="{{ asset('assets/Logo-Grand.png') }}" width="100"/>

        <img class="image"  src="{{ asset('assets/puzzle.png') }}" width="160"/>


        <div class="text-content">
            <h2 id="success-text">شكرًا لاشتراكك في الخدمة</h2>
        </div>

        <div class="footer-info">
            <p id="welcome-text">• اهلا بك في مسابقة "البطل الكبير"</p>
            <p id="trial-text">• من أسياسيل للمشتركين الجدد أول ثلاث أيام مجانا ثم تكلفة الاشتراك 300 د.ج يوميا</p>
            <p id="cancel-text">• لإلغاء الاشتراك ارسل 0 مجانا إلى 4603</p>
        </div>
    </div>
</div>

</body>
<script src="{{ asset('assets/js/translation.js') }}" defer></script>
</html>
