(function () {
    'use strict';
    if (!('serviceWorker' in navigator)) return;

    window.addEventListener('load', function () {
        const swUrl = OC.generateUrl('/apps/pwa_suite/sw.js');

        navigator.serviceWorker.getRegistrations().then(function (registrations) {
            let pwaSuiteExists = false;
            const alienRegistrations = [];

            for (const reg of registrations) {
                const currentUrl = reg.active?.scriptURL || reg.installing?.scriptURL || reg.waiting?.scriptURL || '';
                
                if (currentUrl.includes('pwa_suite')) {
                    pwaSuiteExists = true;
                } else {
                    // Service Worker from another app detected: mark it for removal
                    alienRegistrations.push(reg.unregister());
                }
            }

            // 1. Remove foreign Service Workers in the background
            Promise.all(alienRegistrations).then(function () {
                // 2. Register PWA Suite if it isn't registered/controlling the instance yet
                if (!pwaSuiteExists) {
                    navigator.serviceWorker.register(swUrl, { scope: '/' })
                        .then(function (reg) {
                            console.log('[PWA Suite] Master Service Worker registered with scope:', reg.scope);
                        })
                        .catch(function (err) {
                            console.warn('[PWA Suite] Error registering master Service Worker:', err);
                        });
                }
            });
        });
    });
})();
