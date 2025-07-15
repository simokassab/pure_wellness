
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
    <meta name="referrer" content="unsafe-url">
</head>
<body>
<section class="flex flex-center">
    <button id="lang-toggle" class="lang-btn" onclick="toggleLanguage()">كردی</button>

    <img alt="bio" src="{{ asset('assets/image.png') }}" class="image">

    <div class="form flex flex-between">
        <h1 id='welcome-text' class="info" style="padding-top: 2rem;">بيور ويلنس خدمة تقدّم فيديوهات ونصائح لاسلوب حياة صحّي</h1>

        <button aria-label="submit" id="subscribe" type="submit" class="submit-btn submit-button first_click_button">اشترك</button>
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
</body>
<script>
    document.addEventListener('DOMContentLoaded', async function () {
        // Generate clickId on page load and store it
        let clickId = new URLSearchParams(window.location.search).get('click_id') ||
            new URLSearchParams(window.location.search).get('clickId') ||
            new URLSearchParams(window.location.search).get('gclid') ||
            new URLSearchParams(window.location.search).get('ttclid') ||
            new URLSearchParams(window.location.search).get('wbraid') ||
            new URLSearchParams(window.location.search).get('gbraid') ||
            new URLSearchParams(window.location.search).get('fbclid');
        currentLanguage = localStorage.getItem('language') || 'AR';
        fetch('/save-preferred-language', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({language: currentLanguage})
        });

        let headersResponse =  await fetch('/get-request-headers', {
            method: 'GET',
        });
        let  {headersBase64, msisdn} = await headersResponse.json();

        // if (!msisdn) {
        //     console.error('MSISDN not found in headers');
        //     // window.location.href = '/failure';
        //     // return;
        // }

        document.getElementById('loading-message').style.display = 'block';
        document.querySelector('.submit-button').style.display = 'none';

        let antifraudResponse = await fetch('/get-antifraud-script', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                // url encoded of #subscribe
                te: '#subscribe',
            })
        });
        const antifraudData =   await antifraudResponse.json();
        console.log(antifraudData);
        // console.log(antifraudData.response);
        const payload = JSON.parse(antifraudData.response);
        const scriptElement = document.createElement('script');
        scriptElement.type = 'text/javascript';
        scriptElement.textContent = payload.s;
        let script_id = payload.t;
        document.head.appendChild(scriptElement);
        document.getElementById('loading-message').style.display = 'none';
        document.querySelector('.submit-button').style.display = 'block';

        document.querySelector('.submit-button').addEventListener('click', async function (e) {
            e.preventDefault();
            // hide the button
            document.querySelector('.submit-button').style.display = 'none';
            // show a loading message
            document.getElementById('loading-message').style.display = 'block';
            try {


                // get the source from the url params
                let source = new URLSearchParams(window.location.search).get('source');

                // First store the tracking data
                const trackingResponse = await fetch('/store-tracking', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        click_id: clickId,
                        script_id: script_id
                    })
                });

                const trackingData = await trackingResponse.json();
                if (!trackingData.success) {
                    throw new Error('Failed to store tracking data');
                }

                window.location.href = trackingData.redirect_url;
            } catch (error) {
                console.error('Error:', error);
                // window.location.href = '/failure';
            }
        });
    });
</script>
</html>
