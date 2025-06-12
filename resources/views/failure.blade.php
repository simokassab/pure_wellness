<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pure Welness</title>
    <link rel="shortcut icon" href="{{ asset('assets/favicon-32x32.png') }}" sizes="32x32" type="image/svg">
    <link rel="shortcut icon" href="{{ asset('assets/favicon-16x16.png') }}" sizes="16x16" type="image/svg">
    <link rel="shortcut icon" href="{{ asset('assets/favicon.ico') }}" sizes="72x72" type="image/svg">
    <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body>
<section class="flex flex-center">
    <button id="lang-toggle" class="lang-btn" onclick="toggleLanguage()">كردی</button>

    <img alt="bio" src="{{ asset('assets/image.png') }}" class="image">

    <div class="form flex flex-between">
        <h1 id='welcome-text' class="welcome-text">بيور ويلنس خدمة تقدّم فيديوهات ونصائح لاسلوب حياة صحّي</h1>
        <div class="flex flex-between content">
            <h2 id="failureText" class="info" style="padding-top: 2rem;">عذرًا لا يمكنك الاشتراك في الخدمة</h2>

            <div class="instructions">
                <p id="footer-text"> اهلا بك في مسابقة "بطل الجائزة الكبرى"</p>
                <p id="start-text"> كلفة الرسالة المستلمة 240 د.ع. يوميًا</p>
                <p id="trial-text"> من أسياسيل للمشتركين الجدد أول ثلاث أيام مجانا ثم تكلفة الاشتراك 300 د.ج يوميا </p>
                <p id="cancel-text"> لإلغاء الاشتراك ارسل 0 مجانا إلى 4603 </p>
                <p id="help-text">للمساعدة أو للحصول على معلومات اضافية الرجاء التواصل: support@prime0build.co</p>
            </div>
        </div>
    </div>

</section>
<script src="{{ asset('assets/js/translation.js') }}"></script>
</body>
