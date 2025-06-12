const translations = {
    ar: {
        direction: 'rtl',
        languageBtn: 'عربی',
        welcomeText: `بيور ويلنس خدمة تقدّم فيديوهات ونصائح لاسلوب حياة صحّي`,
        welcomeInfo: `أثبت ذكائك، اكسب لقب "المتميزون"، <span class='bold-text'>واربح جوائز</span>`,
        welcomeDesc: `كلما زادت نقاطك زادت فرصك بالفوز`,
        subscribe: "إشترك",
        infoPhone: 'أدخل رقم جوالك لتتلقى رمز المرور',
        infoPin: 'رجاء إدخال رمز المرور الذي تلقيته',
        confirmBtn: 'تأكيد الاشتراك',
        continueText: 'متابعة',
        footerText: '• أهلاً بك في بيور ويلنيس، باشتراكك انت توافق على الاحكام والشروط التالية:',
        startText: '• كلفة الرسالة المستلمة 240 د.ع. يوميًا',
        trialText: '• يحصل المشتركين الجدد على يوم مجاني، ثم يتجدد الاشتراك تلقائيًا',
        cancelText: '• لالغاء الاشتراك ارسل UPW مجاناً الى 3368.',
        helpText: '• للمساعدة أو للحصول على معلومات اضافية الرجاء التواصل: support@prime0build.co',
        failureText: "عذرًا لا يمكنك الاشتراك في الخدمة",
        successText: "شكرًا لاشتراكك في الخدمة"
    },
    ku: {
        direction: 'rtl',
        languageBtn: 'كوردی',
        welcomeText: `خزمەتگوزاری Pure Wellness کە ڤیدیۆ و ئامۆژگاری بۆ شێوازی ژیان دابین دەکات`,
        welcomeInfo: `سەلمێنراوە کە تۆ  <span class='bold-text'> زانیاریت هەیە</span>`,
        welcomeDesc: `خاڵی زیاتر = ئەگەری بردنەوە زیاترە`,
        subscribe: "بەشداربە",
        infoPhone: 'بەشداریکردنەکە پشتڕاست بکەرەوە، ژمارەی مۆبایلەکەت بنووسە بۆ وەرگرتنی کۆدی هاتوچۆ',
        infoPin: 'تکایە ئەو پاسکۆدەی کە وەرتگرتووە دابنێ',
        confirmBtn: 'پشتڕاستکردنەوەی بەشداریکردن',
        continueText: 'بەدواداچوون',
        footerText: '• بەخێربێن بۆ Pure Wellness، بە بەشداریکردنت، تۆ ڕەزامەندیت لەسەر ئەم مەرج و رێسایانەی خوارەوە:',
        startText: '• تێچووی پەیامی وەرگرتن 240 D.',
        trialText: '• بەشداربووانی نوێ ڕۆژێکی بێ بەرامبەر وەردەگرن، پاشان بەشداریکردنەکە بە شێوەیەکی ئۆتۆماتیکی نوێ دەکرێتەوە',
        cancelText: '• بۆ هەڵوەشاندنەوەی بەشداریکردنەکە، UPW بە خۆڕایی بنێرە بۆ 3368.',
        helpText: '• بۆ یارمەتی یان بۆ زانیاری زیاتر، تکایە پەیوەندی بکەن بە: support@prime0build.co',
        failureText: "ببورن ناتوانن سەبسکرایبی خزمەتگوزارییەکە بکەن",
        successText: "سوپاس بۆ بەشداریکردنتان لە خزمەتگوزارییەکە"
    },
    en: {
        direction: 'ltr',
        languageBtn: 'En',
        welcomeText: `Pure Wellness is a service that offers videos and tips for a healthy lifestyle.`,
        welcomeInfo: `Prove Your Intelligence, Earn the Title of The Distinguished, and <span class='bold-text'>Get Rewarded</span>`,
        welcomeDesc: 'Higher points = Higher chance of winning',
        subscribe: "Subscribe",
        infoPhone: "Enter your mobile to receive a PIN code",
        infoPin: "Enter PIN code",
        continueText: 'Continue',
        confirmBtn: "Confirm Subscription",
        footerText: 'Welcome to Pure Wellness. By subscribing, you agree to the following terms and conditions:',
        startText: 'The cost of the received message is 240 IQD per day.',
        trialText: 'New subscribers get a free day, then the subscription renews automatically.',
        cancelText: 'To unsubscribe, send UPW for free to 3368.',
        helpText: 'For assistance or additional information, please contact: support@prime0build.co',
        failureText: "Sorry, you cannot subscribe to the service.",
        successText: "Thank you for subscribing to the service."
    }
};

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    // Load saved language or default to Arabic
    const savedLang = localStorage.getItem('preferredLanguage') || 'ar';
    changeLanguage(savedLang);
});

// Toggle between languages
function toggleLanguage() {
    const currentLang = localStorage.getItem('preferredLanguage') || 'ar';
    const newLang = currentLang === 'ar' ? 'ku' : currentLang === 'ku' ? 'en' : 'ar';
    changeLanguage(newLang);
}

// Change language function
function changeLanguage(lang) {
    // Save to localStorage
    localStorage.setItem('preferredLanguage', lang);

    // Set direction
    document.dir = translations[lang].direction;

    // Helper function to safely update element content
    function safelyUpdateElement(id, content, isHTML = false) {
        const element = document.getElementById(id);
        if (element) {
            if (isHTML) {
                element.innerHTML = content;
            }
            else {
                element.textContent = content;
            }
        }
    }

    // Update language toggle button
    safelyUpdateElement('lang-toggle', translations[lang].languageBtn);
    // Update all text elements
    safelyUpdateElement('success-text', translations[lang].successText, true);
    safelyUpdateElement('failure-text', translations[lang].failureText, true);
    safelyUpdateElement('confirm-btn', translations[lang].confirmBtn);
    safelyUpdateElement('welcome-text', translations[lang].welcomeText, true);
    safelyUpdateElement('welcome-info', translations[lang].welcomeInfo,true);
    safelyUpdateElement('welcome-description', translations[lang].welcomeDesc,true);
    safelyUpdateElement('subscribe', translations[lang].subscribe);
    safelyUpdateElement('continue', translations[lang].continueText);
    safelyUpdateElement('footer-text', translations[lang].footerText);
    safelyUpdateElement('start-text', translations[lang].startText);
    safelyUpdateElement('trial-text', translations[lang].trialText);
    safelyUpdateElement('help-text', translations[lang].helpText);
    safelyUpdateElement('cancel-text', translations[lang].cancelText);
    safelyUpdateElement('info-phone', translations[lang].infoPhone);
    safelyUpdateElement('info-pin', translations[lang].infoPin);
}


/*** Function: OTP ***/
function moveFocus(current, direction) {
    if (direction === 'next') {
        if (current.value.length >= 1) {
            const nextInput = current.nextElementSibling;
            if (nextInput) {
                nextInput.focus();
            }
        }
    }

    // No more than 4 digits
    if (current === document.querySelector('.otp-input:last-of-type') && current.value.length >= 1) {
        current.value = current.value.charAt(0); // Keep only the first character
    }
}
// let otp = document.getElementById('subscribe')

// if(otp)
//     otp.addEventListener('click', function() {
//         const inputs = document.querySelectorAll('.otp-input');
//         let otp = '';
//         inputs.forEach(input => {
//             otp += input.value;
//         });
//         alert(`Entered OTP: ${otp}`);
//     });
