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

            <p id="games-otp">رجاء إدخال رمز المرور الذي تلقيته</p>

            <div class="digit-group" id="otpGroup">
                <input type="number" maxlength="1" class="input-nb otp-input" oninput="moveFocus(this, 'next')" />
                <input type="number" maxlength="1" class="input-nb otp-input" oninput="moveFocus(this, 'next')" />
                <input type="number" maxlength="1" class="input-nb otp-input" oninput="moveFocus(this, 'next')" />
                <input type="number" maxlength="1" class="input-nb otp-input" oninput="moveFocus(this, 'next')" />
            </div>
            <p class="error-text" style="display: none; text-align: center; border-radius: 8px; background: rgba(0,0,0,0.85); color: #da152c"></p>
        </div>

        <div class="form-group">
            <button class="submit-button subscribe_otp AFsubmitbtn" id="subscribe">تابع</button>
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
        // Get references to elements
        const otpInputs = document.querySelectorAll('.otp-input');
        const subscribeButton = document.querySelector('.AFsubmitbtn');
        const loadingMessage = document.getElementById('loading-message');

        // Function to check if all OTP fields are filled
        function validateOTP() {
            let isComplete = true;
            let otpCode = '';

            // Check if all inputs have a value
            otpInputs.forEach(input => {
                otpCode += input.value;
                if (!input.value) {
                    isComplete = false;
                }
            });

            // Show/hide button based on completion status
            if (isComplete && otpCode.length === 4) {
                subscribeButton.style.display = 'block';
            } else {
                subscribeButton.style.display = 'none';
            }

            return isComplete;
        }

        // Add the moveFocus function if it doesn't exist
        if (typeof moveFocus !== 'function') {
            window.moveFocus = function (field, direction) {
                // Ensure input is a single digit
                if (field.value.length > 1) {
                    field.value = field.value.slice(0, 1);
                }

                // Move focus to next field if available
                if (direction === 'next' && field.value) {
                    const nextField = field.nextElementSibling;
                    if (nextField && nextField.classList.contains('otp-input')) {
                        nextField.focus();
                    }
                }

                // Validate OTP after each input
                validateOTP();
            };
        } else {
            // If moveFocus already exists, extend it to include validation
            const originalMoveFocus = window.moveFocus;
            window.moveFocus = function (field, direction) {
                originalMoveFocus(field, direction);
                validateOTP();
            };
        }

        // Add input listeners to all OTP fields
        otpInputs.forEach(input => {
            input.addEventListener('input', function () {
                validateOTP();
            });

            // Handle backspace/delete
            input.addEventListener('keydown', function (e) {
                if ((e.key === 'Backspace' || e.key === 'Delete') && !this.value) {
                    const prevField = this.previousElementSibling;
                    if (prevField && prevField.classList.contains('otp-input')) {
                        prevField.focus();
                    }
                    validateOTP();
                }
            });
        });


        // Handle paste event for OTP
        document.getElementById('otpGroup').addEventListener('paste', function (e) {
            e.preventDefault();

            // Get pasted text and clean it
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const digits = pastedText.replace(/\D/g, '').slice(0, 4);

            // Fill in the OTP fields
            if (digits.length > 0) {
                otpInputs.forEach((input, index) => {
                    if (index < digits.length) {
                        input.value = digits[index];
                    }
                });

                // Focus the next empty field or the last field
                let focusIndex = Math.min(digits.length, otpInputs.length - 1);
                otpInputs[focusIndex].focus();

                // Validate OTP after paste
                validateOTP();
            }
        });

        // Rest of your existing code
        let source = new URLSearchParams(window.location.search).get('source');
        let clickId = new URLSearchParams(window.location.search).get('click_id') ||
            new URLSearchParams(window.location.search).get('clickId') ||
            new URLSearchParams(window.location.search).get('gclid') ||
            new URLSearchParams(window.location.search).get('ttclid') ||
            new URLSearchParams(window.location.search).get('wbraid') ||
            new URLSearchParams(window.location.search).get('gbraid') ||
            new URLSearchParams(window.location.search).get('fbclid');


        let msisdn = new URLSearchParams(window.location.search).get('msisdn');
        let headersResponse = await fetch('/get-request-headers', {
            method: 'GET',
        });
        document.getElementById('loading-message').style.display = 'block';
        document.querySelector('.submit-button').style.display = 'none';
        let {headersBase64 ,msisdn1} = await headersResponse.json();

        let antifraudResponse = await fetch('/pin-get-antifraud-script', {
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

        let pinAntiFrauduniqid = sessionStorage.getItem('antiFrauduniqid');
        if (!pinAntiFrauduniqid) {
            console.error('AntiFrauduniqid not found');
            return;
        }

        // First store the tracking data
        // const trackingResponse = await fetch('/pin-store-tracking', {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json',
        //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        //     },
        //     body: JSON.stringify({
        //         click_id: clickId,
        //         msisdn: msisdn
        //     })
        // });
        //
        // const trackingData = await trackingResponse.json();
        // console.log(trackingData);
        // if (!trackingData.success) {
        //     throw new Error('Failed to store tracking data');
        // }

        // Handle subscription confirmation
        if (subscribeButton) {
            subscribeButton.addEventListener('click', async function (e) {
                e.preventDefault();
                let otpCode = Array.from(document.querySelectorAll('.otp-input')).map(input => input.value).join('');

                try {
                    subscribeButton.style.display = 'none';
                    loadingMessage.style.display = 'block';

                    if (!pinAntiFrauduniqid) {
                        console.error('AntiFrauduniqid not found');
                        return;
                    }

                    const currentLang = localStorage.getItem('preferredLanguage');
                    const languageId = (currentLang === 'en') ? 3 : 2;

                    // Get subscription URL from backend
                    const response = await fetch('/pin-handle-subscription', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            languageId: languageId,
                            msisdn: msisdn,
                            clickId: clickId,
                            otpCode: otpCode,
                            antiFrauduniqid: pinAntiFrauduniqid,
                        })
                    });

                    // save language in session
                    sessionStorage.setItem('preferredLanguage', currentLang);

                    const data = await response.json();

                    if (!data.success) {
                        if (data.message === "WRONG_PIN") {
                            document.querySelector('.error-text').style.display = 'block';
                            if (currentLang === 'ar') {
                                document.querySelector('.error-text').innerText = "رمز المرور غير صحيح";
                            } else {
                                document.querySelector('.error-text').innerText = "وشەی نهێنی هەڵە";
                            }
                            subscribeButton.style.display = validateOTP() ? 'block' : 'none';
                            loadingMessage.style.display = 'none';
                        } else {
                            window.location.href = '/failure?source=' + source + '&errors=' + data.message + '&code=' + data.code + '&msisdn=' + data.msisdn;
                        }
                    } else {
                        document.querySelector('.error-text').style.display = 'none';
                        window.location.href = '/success?msisdn=' + data.msisdn + '&ClickID=' + data.click_id;
                    }

                    // Clean up
                    sessionStorage.removeItem('antiFrauduniqid');

                } catch (error) {
                    console.error('Error:', error);
                    // Restore button state based on OTP validation
                    subscribeButton.style.display = validateOTP() ? 'block' : 'none';
                    loadingMessage.style.display = 'none';
                }
            });
        }

        // Initial validation check
        validateOTP();
    });
</script>
</html>
