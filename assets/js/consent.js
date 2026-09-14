(function () {
    'use strict';

    var measurementId = 'G-X2TNR1JX67';
    var storageKey = 'le-crystal-analytics-consent';
    var banner;

    function loadAnalytics() {
        if (window.__leCrystalAnalyticsLoaded) { return; }
        window.__leCrystalAnalyticsLoaded = true;
        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', measurementId, { anonymize_ip: true });

        var script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(measurementId);
        document.head.appendChild(script);
    }

    function removeBanner() {
        if (banner) { banner.remove(); banner = null; }
    }

    function saveChoice(choice) {
        try { localStorage.setItem(storageKey, choice); } catch (error) { }
        removeBanner();
        if (choice === 'accepted') {
            loadAnalytics();
        }
    }

    function showBanner() {
        if (banner) { return; }
        banner = document.createElement('section');
        banner.className = 'cookie-consent';
        banner.setAttribute('role', 'dialog');
        banner.setAttribute('aria-label', 'Choix relatif aux cookies');
        banner.innerHTML = '<div class="cookie-consent__inner"><div class="cookie-consent__copy"><p class="cookie-consent__title">Cookies et confidentialité</p><p class="cookie-consent__text">Nous utilisons Google Analytics pour mesurer l\'audience de notre site. Vous pouvez accepter ou refuser ces cookies. <a href="politique-confidentialite.html">En savoir plus</a></p></div><div class="cookie-consent__actions"><button class="cookie-consent__button cookie-consent__button--refuse" type="button" data-choice="refused">Refuser</button><button class="cookie-consent__button cookie-consent__button--accept" type="button" data-choice="accepted">Accepter</button></div></div>';
        banner.addEventListener('click', function (event) {
            var choice = event.target.getAttribute('data-choice');
            if (choice) { saveChoice(choice); }
        });
        document.body.appendChild(banner);
    }

    var choice;
    try { choice = localStorage.getItem(storageKey); } catch (error) { choice = null; }
    if (choice === 'accepted') {
        loadAnalytics();
    } else if (choice !== 'refused') {
        showBanner();
    }
})();
