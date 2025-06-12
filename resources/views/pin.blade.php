
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
            <h2 id="info-phone" class="info" style="padding-top: 2rem;">أدخل رقم جوالك لتتلقى رمز المرور</h2>

            <div class="flex flex-center" style="gap: 1rem;">
                <div class="phone flex">
                    <p>+967</p>
                    <input id='phone-number' type="text" title="phone">
                </div>

                <p class="error"></p>

                <button aria-label="submit" type="submit" id="continue" class="submit-btn">تابع</button>
            </div>
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
<script>
    document.addEventListener('DOMContentLoaded', async function () {

        const phoneInput = document.getElementById('phone');
        const continueButton = document.getElementById('continue');
        const loadingMessage = document.getElementById('loading-message');
        const errorDiv = document.getElementById('error');
        let full_number = '';

        phoneInput.addEventListener('input', function () {
            // Remove all non-numeric characters
            this.value = this.value.replace(/\D/g, '');

            full_number = '964' + this.value;

            // Validation
            if (this.value.length < 7) {
                continueButton.style.display = 'none';
            } else if (full_number.startsWith('96477') || full_number.startsWith('964077')) {
                // remove the 0 after the 964
                full_number = full_number.replace(/^9640/, '964');
                console.log(full_number);
                // Check if the number is valid
                if (full_number.length === 13) {
                    // If valid, show the continue button
                    continueButton.style.display = 'inline-block';
                } else {
                    // If not valid, hide the continue button
                    continueButton.style.display = 'none';
                }
            } else {
                continueButton.style.display = 'none';
            }
        });

        let source = new URLSearchParams(window.location.search).get('source');
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

        const headersResponse = await fetch('/get-request-headers', {
            method: 'GET',
        });
        const {headersBase64, msisdn1} = await headersResponse.json();

        let antifraudResponse = await fetch('/pin-get-antifraud-script', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                user_headers: headersBase64,
                msisdn: '',
                user_ip: "{{ base64_encode(Request::ip()) }}",
                click_id: clickId,
                source: new URLSearchParams(window.location.search).get('source'),
                save_antifraud: '0', // dont save the antifrauduniqid in db
                page: 1

            })
        });

        const antifraudData =   await antifraudResponse.json();
        console.log(antifraudData);
        if (!antifraudData.success) {
            throw new Error('Failed to get anti-fraud script');
        }

        // Store both the script and the AntiFrauduniqid
        sessionStorage.setItem('antiFraudScript', antifraudData.script);
        const antiFraudScript = sessionStorage.getItem('antiFraudScript');
        if (antiFraudScript) {
            const scriptElement = document.createElement('script');
            scriptElement.innerHTML = antiFraudScript;
            document.head.appendChild(scriptElement);
            sessionStorage.removeItem('antiFraudScript');
            // document.getElementById('loading-message').style.display = 'none';
            // document.querySelector('.submit-button').style.display = 'block';
        }

        // alert('antiFrauduniqid: ' + antifraudData.antiFrauduniqid);
        sessionStorage.setItem('MCPuniqid', antifraudData.mcp_uniq_id);


        // click button
        document.querySelector('.submit-button').addEventListener('click', async function (e) {
            e.preventDefault();
            continueButton.style.display = 'none';
            loadingMessage.style.display = 'block';
            try {
                const trackingResponse = await fetch('/pin-store-tracking', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        click_id: clickId,
                        msisdn: full_number
                    })
                });

                const trackingData = await trackingResponse.json();
                console.log(trackingData);
                if (!trackingData.success) {
                    throw new Error('Failed to store tracking data');
                }

                const getPinResponse = await fetch('/get-pin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        click_id: clickId,
                        msisdn: full_number,
                        languageId: currentLanguage === 'AR' ? 2 : 3,
                    })
                });

                const getPinData = await getPinResponse.json();
                if (!getPinData.success) {
                    loadingMessage.style.display = 'block';
                    loadingMessage.innerHTML = getPinData.message;
                    // window.location.href = '/failure?code='+getPinData.code+'&message='+getPinData.message+'&msisdn='+full_number;
                } else {

                    window.location.href = `/otp?gclid=${clickId}&source=${source}&msisdn=${full_number}&uniqid=${sessionStorage.getItem('MCPuniqid')}`;
                }
            } catch (error) {
                console.error('Error:', error);
                // window.location.href = '/failure';
            }
        });
    });
</script>
</html>
