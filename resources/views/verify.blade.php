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
        <h1 class="info" id="welcome-text" style="padding-top: 2rem;">بيور ويلنس خدمة تقدّم فيديوهات ونصائح لاسلوب حياة صحّي</h1>

        <button aria-label="submit" id="confirm-btn" type="submit" class="submit-btn submit-button verify_btn AFsubmitbtn">تأكيد الاشتراك</button>
        <p id="loading-message" style="display: none; text-align: center; margin-top: 10px;">الرجاء الانتظار ...</p>

        <p class="error"></p>

        <div class="instructions">
            <p id="footer-text"> اهلا بك في مسابقة "بطل الجائزة الكبرى"</p>
            <p id="start-text"> كلفة الرسالة المستلمة 240 د.ع. يوميًا</p>
            <p id="trial-text"> من أسياسيل للمشتركين الجدد أول ثلاث أيام مجانا ثم تكلفة الاشتراك 300 د.ج يوميا </p>
            <p id="cancel-text"> لإلغاء الاشتراك ارسل 0 مجانا إلى 4603 </p>
            <p id="help-text">للمساعدة أو للحصول على معلومات اضافية الرجاء التواصل: support@prime0build.co</p>
        </div>
    </div>

</section>
<script src="{{ asset('assets/js/translation.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', async function () {


        let clickId = new URLSearchParams(window.location.search).get('click_id') ||
            new URLSearchParams(window.location.search).get('clickId') ||
            new URLSearchParams(window.location.search).get('gclid') ||
            new URLSearchParams(window.location.search).get('ttclid') ||
            new URLSearchParams(window.location.search).get('wbraid') ||
            new URLSearchParams(window.location.search).get('gbraid') ||
            new URLSearchParams(window.location.search).get('fbclid');

        let headersResponse =  await fetch('/get-request-headers', {
            method: 'GET',
        });
        document.getElementById('loading-message').style.display = 'block';
        document.querySelector('.submit-button').style.display = 'none';
        let  {headersBase64, msisdn} = await headersResponse.json();

        let antifraudResponse = await fetch('/get-antifraud-script', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                user_headers: headersBase64,
                msisdn: msisdn,
                user_ip: "{{ base64_encode(Request::ip()) }}",
                click_id: clickId,
                source: new URLSearchParams(window.location.search).get('source'),
                save_antifraud: '1', // dont save the antifrauduniqid in db
                page: 2

            })
        });
        const antifraudData = await antifraudResponse.json();
        console.log(antifraudData);
        if (!antifraudData.success) {
            window.location.href = '/failure?msisdn=' + msisdn + '&error=' + antifraudData.message;
        }

        // Store both the script and the AntiFrauduniqid
        sessionStorage.setItem('antiFraudScript', antifraudData.script);
        const antiFraudScript = sessionStorage.getItem('antiFraudScript');
        if (antiFraudScript) {
            const scriptElement = document.createElement('script');
            scriptElement.innerHTML = antiFraudScript;
            document.head.appendChild(scriptElement);
            sessionStorage.removeItem('antiFraudScript');
            document.getElementById('loading-message').style.display = 'none';
            document.querySelector('.submit-button').style.display = 'block';
            sessionStorage.setItem('antiFrauduniqid', antifraudData.antiFrauduniqid);
        }


        let antiFrauduniqid = sessionStorage.getItem('antiFrauduniqid');
        if (!antiFrauduniqid) {
            console.error('AntiFrauduniqid not found');
            return;
        }
        // Handle subscription confirmation
        const subscribeButton = document.querySelector('.submit-button');

        if (subscribeButton) {
            subscribeButton.addEventListener('click', async function (e) {

                e.preventDefault();
                document.querySelector('.submit-button').style.display = 'none';
                // show a loading message
                document.getElementById('loading-message').style.display = 'block';
                try {


                    const currentLang = localStorage.getItem('preferredLanguage');
                    const languageId = (currentLang === 'en') ? 3 : 2;
                    // Get subscription URL from backend
                    const response = await fetch('/handle-subscription', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            antiFrauduniqid: antiFrauduniqid,
                            languageId: languageId,
                            msisdn: msisdn,
                        })
                    });

                    // save language in session
                    sessionStorage.setItem('preferredLanguage', currentLang);

                    const data = await response.json();
                    // alert(data.redirectUrl);
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to get subscription URL');
                    }

                    // Redirect to the subscription URL
                    window.location.href = data.redirectUrl;

                    // Clean up
                    sessionStorage.removeItem('antiFrauduniqid');
                } catch (error) {
                    console.error('Error:', error);
                    // window.location.href = '/failure';
                }
            });
        }
    });
</script>

</body>
</html>
