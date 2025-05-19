@php
    $projectSource = request()->query('source'); // Get source from URL
    $source = \App\Models\ProjectSource::where('uuid', $projectSource)->first()->source->name;
    $isGoogleAds = in_array($source, ['Google-Ads']);
@endphp
    <!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Grand Hero</title>
    <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if($isGoogleAds)
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-N7FQGPP7');</script>
        <!-- End Google Tag Manager -->
    @endif
</head>


<body>
@if($isGoogleAds)
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N7FQGPP7"
                      height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
@endif
<div class="lang-switcher">
    <button id="lang-toggle" class="lang-btn" onclick="toggleLanguage()">كردی</button>
</div>

<div class="container">
    <div class="card">
        <img class='logo' alt="logo" src="{{ asset('assets/Logo-Grand.png') }}" width="100"/>

        <img class="image"  src="{{ asset('assets/puzzle.png') }}" width="160"/>
        <div class="text-content">
            <h2 id="games-text">ألعاب متنوعة في انتظارك</h2>

            <p id="games-phone">أدخل رقم جوالك لتتلقى رمز المرور</p>
        </div>

        <div class="form-group">
            <div class="phone-input" style="display: flex; gap: 1rem;">
                <p style="color: black">+964</p>
                <input title="'phone-number" type="text" maxlength="15"  id="phone"/>
            </div>
            <button class="submit-button" style="display: none" id="continue">تابع</button>
            <p id="loading-message" style="display: none; text-align: center; margin-top: 10px;">الرجاء الانتظار ...</p>

        </div>

        <div class="footer-info">
            <p id="welcome-text">• اهلا بك في مسابقة "بطل الجائزة الكبرى"</p>
            <p id="trial-text">• من أسياسيل للمشتركين الجدد أول ثلاث أيام مجانا ثم تكلفة الاشتراك 300 د.ج يوميا</p>
            <p id="cancel-text">• لإلغاء الاشتراك ارسل 0 مجانا إلى 4603</p>
        </div>
    </div>
</div>

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
