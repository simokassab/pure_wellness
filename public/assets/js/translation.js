// *** translation.js

const translations = {
    ar: {
        direction: 'rtl',
        languageBtn: 'كوردی',
        gamesText: 'ألعاب متنوعة في انتظارك',
        subscribe: "إشترك",
        confirmBtn: 'تأكيد الاشتراك',
        welcomeText: '• اهلا بك في مسابقة "بطل الجائزة الكبرى',
        continueText: 'تابع',
        trialText: '• من أسياسيل للمشتركين الجدد أول ثلاث أيام مجانا ثم تكلفة الاشتراك 300 د.ع يوميا',
        cancelText: '• لإلغاء الاشتراك ارسل 0 مجانا إلى 4603',
        phoneNumber: 'أدخل رقم جوالك لتتلقى رمز المرور',
        otpNumber: 'رجاء إدخال رمز المرور الذي تلقيته',
        mobileWelcomeText: '• اهلا بك في مسابقة "بطل الجائزة الكبرى"',
        mobileFreeText: '• من أسباسيل للمشتركين الجدد أول ثلاث أيام مجانا',
        mobileCostText: '• ثم تكلفة الاشتراك 300 د.ع يوميا',
        mobileCancelText: '• لإلغاء الاشتراك ارسل 0 مجانا إلى 4603',
        failureText: "عذرًا لا يمكنك الاشتراك في الخدمة",
        successText: "شكرًا لاشتراكك في الخدمة"
    },
    ku: {
        direction: 'rtl',
        languageBtn: 'عربی',
        gamesText: 'یارییە جۆراوجۆرەکان چاوەڕێتن',
        subscribe: "بەشداربە",
        confirmBtn: 'پشتڕاستکردنەوەی بەشداریکردن',
        welcomeText: '• بەخێربێیت بۆ پێشبڕکێی "پاڵەوانی خەڵاتی گەورە"',
        continueText: 'فۆڵۆو بکە',
        trialText: '• لە ئێسپاسیلەوە بۆ بەشداربووە نوێیەکان بۆ سێ ڕۆژی یەکەم بەخۆڕاییە پاشان نرخی بەشداریکردن 300 د.ع ڕۆژانە',
        cancelText: '• بۆ هەڵوەشاندنەوەی بەشداریکردن 0 بنێرە بەخۆڕایی بۆ 4603',
        phoneNumber: 'بەشداریکردنەکە پشتڕاست بکەرەوە، ژمارەی مۆبایلەکەت بنووسە بۆ وەرگرتنی کۆدی هاتوچۆ',
        otpNumber: 'تکایە ئەو پاسکۆدەی کە وەرتگرتووە دابنێ',
        mobileWelcomeText: '• بەخێربێیت بۆ پێشبڕکێی "پاڵەوانی خەڵاتی گەورە"',
        mobileFreeText: '• لە ئێسپاسیلەوە بۆ بەشداربووە نوێیەکان بۆ سێ ڕۆژی یەکەم بەخۆڕاییە',
        mobileCostText: '• پاشان نرخی بەشداریکردن 300 د.ع ڕۆژانە',
        mobileCancelText: '• بۆ هەڵوەشاندنەوەی بەشداریکردن 0 بنێرە بەخۆڕایی بۆ 4603',
        failureText: "ببورن ناتوانن سەبسکرایبی خزمەتگوزارییەکە بکەن",
        successText: "سوپاس بۆ بەشداریکردنتان لە خزمەتگوزارییەکە"
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
    const newLang = currentLang === 'ar' ? 'ku' : 'ar';
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
            } else {
                element.textContent = content;
            }
        }
    }

    // Update language toggle button
    safelyUpdateElement('lang-toggle', translations[lang].languageBtn);

    // Update all text elements
    safelyUpdateElement('brand-name', translations[lang].brandName, true);
    safelyUpdateElement('success-text', translations[lang].successText, true);
    safelyUpdateElement('failure-text', translations[lang].failureText, true);
    safelyUpdateElement('games-text', translations[lang].gamesText);
    safelyUpdateElement('confirm-btn', translations[lang].confirmBtn);
    safelyUpdateElement('subscribe', translations[lang].subscribe);
    safelyUpdateElement('welcome-text', translations[lang].welcomeText);
    safelyUpdateElement('continue', translations[lang].continueText);
    safelyUpdateElement('trial-text', translations[lang].trialText);
    safelyUpdateElement('cancel-text', translations[lang].cancelText);
    safelyUpdateElement('games-phone', translations[lang].phoneNumber);
    safelyUpdateElement('games-otp', translations[lang].otpNumber);
    safelyUpdateElement('mobile-welcome-text', translations[lang].mobileWelcomeText);
    safelyUpdateElement('mobile-free-text', translations[lang].mobileFreeText);
    safelyUpdateElement('mobile-cost-text', translations[lang].mobileCostText);
    safelyUpdateElement('mobile-cancel-text', translations[lang].mobileCancelText);

    // document.getElementById('#welcome-text').style.fontWeight = '900'
    // document.getElementById('#trial-text').style.fontWeight = '900'
    // document.getElementById('#cancel-text').style.fontWeight = '900'
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
