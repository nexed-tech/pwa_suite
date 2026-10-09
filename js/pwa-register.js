(function () {
    'use strict';
    if (!('serviceWorker' in navigator)) return;

    window.addEventListener('load', function () {
        const swUrl = OC.generateUrl('/apps/pwa_suite/sw.js');
        const scope = new URL('/', window.location.origin).href;

        navigator.serviceWorker.getRegistrations().then(function (registrations) {
            for (const reg of registrations) {
                const currentUrl = reg.active?.scriptURL || reg.installing?.scriptURL || reg.waiting?.scriptURL || '';
                if (reg.scope === scope && !currentUrl.includes('pwa_suite')) {
                    // Another app's Service Worker owns the scope, e.g. the Notifications app's with
                    // Web Push: it must stay (removing it breaks push, and the app registers it again
                    // on every page, which loops). Ours only adds an offline page; browsers don't need
                    // a Service Worker to install the PWA.
                    console.log('[PWA Suite] Scope ' + scope + ' belongs to ' + currentUrl + '; not registering ours');
                    return;
                }
                if (currentUrl.includes('pwa_suite')) {
                    return;   // already registered
                }
            }
            navigator.serviceWorker.register(swUrl, { scope: '/' })
                .then(function (reg) {
                    console.log('[PWA Suite] Master Service Worker registered with scope:', reg.scope);
                })
                .catch(function (err) {
                    console.warn('[PWA Suite] Error registering master Service Worker:', err);
                });
        });
    });
})();
