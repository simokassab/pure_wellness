<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Hero</title>
    <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<div class="lang-switcher">
    <button id="lang-toggle" class="lang-btn" onclick="toggleLanguage()">كردی</button>
</div>

<div class="container">
    <div class="card">
        <img class='logo' alt="logo" src="{{ asset('assets/Logo-Grand.png') }}" width="100"/>

        <img class="image"  src="{{ asset('assets/puzzle.png') }}" width="160"/>

        <div class="text-content">
            <h2 id="failure-text">عذرًا لا يمكنك الاشتراك
                في الخدمة</h2>
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
