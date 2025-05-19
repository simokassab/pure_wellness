@php
    $projectSource = request()->query('source'); // Get source from URL
    $source = \App\Models\ProjectSource::where('uuid', $projectSource)->first()->source->name;
    $isGoogleAds = in_array($source, ['Google-Ads']);
@endphp
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Hero</title>
    <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}">
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
        </div>

        <div class="form-group">
            <button class="submit-button verify_btn AFsubmitbtn" id="confirm-btn">تأكيد الاشتراك</button>
            <p id="loading-message" style="display: none; text-align: center; margin-top: 10px;">الرجاء الانتظار ...</p>

        </div>

        <div class="footer-info">
            <p id="welcome-text">• اهلا بك في مسابقة "البطل الكبير"</p>
            <p id="trial-text">• من أسياسيل للمشتركين الجدد أول ثلاث أيام مجانا ثم تكلفة الاشتراك 300 د.ج يوميا</p>
            <p id="cancel-text">• لإلغاء الاشتراك ارسل 0 مجانا إلى 4603</p>
        </div>
    </div>
</div>
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
<script src="{{ asset('assets/js/translation.js') }}" defer></script>
</html>
