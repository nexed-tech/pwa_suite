<?php

namespace OCA\PwaSuite\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IAppConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Files\IAppData;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;

class PwaController extends Controller {

    private IAppConfig $appConfig;
    private IAppData $appData;
    private IURLGenerator $urlGenerator;
    private IL10N $l;

    public function __construct(string $appName, IRequest $request, IAppConfig $appConfig, IAppData $appData, IURLGenerator $urlGenerator, IL10N $l) {
        parent::__construct($appName, $request);
        $this->appConfig = $appConfig;
        $this->appData = $appData;
        $this->urlGenerator = $urlGenerator;
        $this->l = $l;
    }

    /**
     * @NoCSRFRequired
     * @PublicPage
     */
    #[PublicPage]
    #[NoCSRFRequired]
    public function getIcon(): void {
        $hasCustom = $this->appConfig->getValueString('pwa_suite', 'has_custom_icon', 'no');

        if ($hasCustom === 'yes') {
            try {
                $folder = $this->appData->getFolder('icons');
                $file = $folder->getFile('app-icon.png');
                $content = $file->getContent();

                header('Content-Type: image/png');
                header('Content-Length: ' . strlen($content));
                header('Cache-Control: public, max-age=604800');
                echo $content;
                exit;
            } catch (\Exception $e) {
                // Automatic fallback if the file is missing
            }
        }

        $fallbackIcon = $this->urlGenerator->linkToRouteAbsolute('theming.Icon.getThemeIcon') . '?v=0';
        header('Location: ' . $fallbackIcon);
        exit;
    }

    /**
     * @NoCSRFRequired
     * @PublicPage
     */
    #[PublicPage]
    #[NoCSRFRequired]
    public function getManifest(string $app = ''): DataResponse {
        $advancedMode = $this->appConfig->getValueString('pwa_suite', 'advanced_mode', 'no');
        $customManifest = $this->appConfig->getValueString('pwa_suite', 'custom_manifest', '');

        if ($advancedMode === 'yes' && !empty(trim($customManifest))) {
            $decoded = json_decode($customManifest, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $response = new DataResponse($this->applyPageApp($decoded, $app));
                $response->addHeader('Content-Type', 'application/manifest+json; charset=utf-8');
                return $response;
            }
        }

        $appName = $this->appConfig->getValueString('pwa_suite', 'app_name', 'Nextcloud PWA');
        $themeColor = $this->appConfig->getValueString('pwa_suite', 'theme_color', '#181818');
        $bgColor = $this->appConfig->getValueString('pwa_suite', 'bg_color', '#181818');
        $displayMode = $this->appConfig->getValueString('pwa_suite', 'display_mode', 'standalone');
        $iconVersion = $this->appConfig->getValueString('pwa_suite', 'icon_version', '1');

        $iconUrl = $this->urlGenerator->linkToRoute('pwa_suite.pwa.getIcon') . '?v=' . $iconVersion;

        $manifest = [
            'id' => 'nextcloud-custom-pwa',
            'name' => $appName,
            'short_name' => $appName,
            'description' => $this->l->t('%s - unified workspace', [$appName]),
            'start_url' => '/',
            'scope' => '/',
            'display' => $displayMode,
            'orientation' => 'any',
            'background_color' => $bgColor,
            'theme_color' => $themeColor,
            'icons' => [
                [
                    'src' => $iconUrl,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => $iconUrl,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ]
            ],
            'shortcuts' => [
                [
                    'name' => $this->l->t('Files'),
                    'url' => '/apps/files/',
                    'icons' => [['src' => $iconUrl, 'sizes' => '512x512']]
                ]
            ]
        ];

        $response = new DataResponse($this->applyPageApp($manifest, $app));
        $response->addHeader('Content-Type', 'application/manifest+json; charset=utf-8');
        return $response;
    }

    /**
     * For an app id the manifest gets an id and start_url of its own for that app, so every
     * Nextcloud app is a separate PWA instead of all of them collapsing into one. The app id
     * comes from ?app=<appid> (added by Application.php based on the page) or from the core
     * theming URL /apps/theming/manifest/<appid> that Application.php intercepts.
     *
     * An optional "apps" object in the custom manifest holds per-app overrides keyed by
     * app id, e.g. "apps": { "spreed": { "name": "Talk", "icons": [...] } }. The matching
     * entry is merged into that app's manifest (everything except "id"); the "apps" key
     * itself is never sent to the browser.
     */
    private function applyPageApp(array $manifest, string $app = ''): array {
        $overrides = isset($manifest['apps']) && is_array($manifest['apps']) ? $manifest['apps'] : [];
        unset($manifest['apps']);

        $app = strtolower($app !== '' ? $app : (string)$this->request->getParam('app', ''));
        if ($app === 'core' || !preg_match('/^[a-z0-9_]+$/', $app)) {
            return $manifest;
        }
        $appUrl = '/apps/' . $app . '/';
        $manifest['id'] = $appUrl;
        $manifest['start_url'] = $appUrl;

        if (isset($overrides[$app]) && is_array($overrides[$app])) {
            $override = $overrides[$app];
            unset($override['id']);
            $manifest = array_replace($manifest, $override);
        }
        return $manifest;
    }

    /**
     * @NoCSRFRequired
     * @PublicPage
     */
    #[PublicPage]
    #[NoCSRFRequired]
    public function getServiceWorker(): DataResponse {
        $advancedMode = $this->appConfig->getValueString('pwa_suite', 'advanced_mode', 'no');
        $customSw = $this->appConfig->getValueString('pwa_suite', 'custom_sw', '');

        if ($advancedMode === 'yes' && !empty(trim($customSw))) {
            $response = new DataResponse($customSw);
            $response->addHeader('Content-Type', 'application/javascript; charset=utf-8');
            $response->addHeader('Service-Worker-Allowed', '/');
            return $response;
        }

        $rawAppName = $this->appConfig->getValueString('pwa_suite', 'app_name', 'Nextcloud PWA');
        $rawThemeColor = $this->appConfig->getValueString('pwa_suite', 'theme_color', '#181818');
        $rawBgColor = $this->appConfig->getValueString('pwa_suite', 'bg_color', '#181818');

        // Escaped for JavaScript string literals
        $appNameJs = addslashes($rawAppName);
        $viewJs = addslashes($this->l->t('View'));

        // Sanitized for safe injection into the offline page's HTML/CSS (which sits inside a JS template literal)
        $html = static fn (string $text): string => str_replace(['`', '$', '\\'], ['&#96;', '&#36;', '&#92;'], htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
        $appNameHtml = $html($rawAppName);
        $langHtml = $html($this->l->getLanguageCode());
        $offlineTitleHtml = $html($this->l->t('%s offline', [$rawAppName]));
        $offlineTextHtml = $html($this->l->t('No connection to the server.'));
        $retryHtml = $html($this->l->t('Retry'));
        $themeColor = preg_match('/^#[a-fA-F0-9]{3,8}$/', $rawThemeColor) ? $rawThemeColor : '#181818';
        $bgColor = preg_match('/^#[a-fA-F0-9]{3,8}$/', $rawBgColor) ? $rawBgColor : '#181818';

        $swCode = <<<JS
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (e) => e.waitUntil(clients.claim()));

self.addEventListener('fetch', (e) => {
    if (e.request.mode === 'navigate') {
        e.respondWith(
            fetch(e.request).catch(() => new Response(`
                <!DOCTYPE html>
                <html lang="{$langHtml}">
                <head>
                    <meta charset="UTF-8">
                    <title>{$offlineTitleHtml}</title>
                    <style>
                        body { background:{$bgColor}; color:#fff; font-family:system-ui; display:flex; height:100vh; align-items:center; justify-content:center; margin:0; text-align:center; }
                        .c { background:#222; padding:2rem; border-radius:12px; max-width:380px; }
                        h1 { color:{$themeColor}; margin-top:0; }
                        button { background:{$themeColor}; color:#fff; border:0; padding:.75rem 1.5rem; border-radius:6px; font-weight:700; cursor:pointer; width:100%; margin-top:1rem; }
                    </style>
                </head>
                <body>
                    <div class="c">
                        <h1>{$appNameHtml}</h1>
                        <p>{$offlineTextHtml}</p>
                        <button onclick="location.reload()">{$retryHtml}</button>
                    </div>
                </body>
                </html>
            `, { headers: { 'content-type': 'text/html;charset=UTF-8' } }))
        );
    }
});

self.addEventListener('push', (e) => {
    if (!e.data) return;

    let title = '{$appNameJs}';
    let body = '';
    let rawUrl = '/';
    let icon = '/apps/pwa_suite/icon';
    let badge = '/apps/pwa_suite/icon';
    let tag = 'nextcloud-notification';
    let isPriority = false;
    let actionsList = [];
    let customVibrate = null;

    try {
        const json = e.data.json();
        title = json.title || json.subject || title;
        body = json.body || json.message || '';
        rawUrl = json.targetUrl || json.url || json.link || '/';
        icon = json.icon || icon;
        badge = json.badge || badge;
        tag = json.tag || json.id || tag;

        isPriority = Boolean(
            json.type === 'call' ||
            (typeof tag === 'string' && tag.includes('spreed')) ||
            json.requireInteraction
        );

        if (Array.isArray(json.actions)) {
            actionsList = json.actions;
        }

        if (json.vibrate) {
            customVibrate = json.vibrate;
        }
    } catch (err) {
        const text = e.data.text();
        const lines = text.split('\\n');
        if (lines.length > 1) {
            title = lines[0];
            body = lines.slice(1).join('\\n');
        } else {
            body = text;
        }
    }

    // Origin check to block arbitrary external URLs in the payload
    const rawTargetObj = new URL(rawUrl, self.location.origin);
    const resolvedUrl = (rawTargetObj.origin === self.location.origin) ? rawTargetObj.href : self.location.origin;

    const mappedActions = actionsList.map((act, index) => ({
        action: act.action || ('action_' + index),
        title: act.title || '{$viewJs}',
        icon: act.icon || undefined
    }));

    const options = {
        body: body,
        icon: icon,
        badge: badge,
        tag: tag,
        renotify: true,
        requireInteraction: isPriority,
        vibrate: customVibrate || (isPriority ? [300, 100, 300, 100, 300] : [100, 50, 100]),
        actions: mappedActions,
        data: {
            url: resolvedUrl,
            actionsMap: actionsList
        }
    };

    e.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (e) => {
    e.notification.close();

    const notifData = e.notification.data || {};
    let targetUrl = notifData.url || '/';

    if (e.action && Array.isArray(notifData.actionsMap)) {
        const selectedAction = notifData.actionsMap.find(
            (a, i) => a.action === e.action || ('action_' + i) === e.action
        );
        if (selectedAction && (selectedAction.url || selectedAction.targetUrl)) {
            targetUrl = selectedAction.url || selectedAction.targetUrl;
        }
    }

    // Guard against open redirect / tab hijacking: only navigate within the same origin
    const targetObj = new URL(targetUrl, self.location.origin);
    const resolvedTarget = (targetObj.origin === self.location.origin) ? targetObj.href : self.location.origin;

    e.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (let client of windowClients) {
                if (client.url.startsWith(self.location.origin) && 'focus' in client) {
                    return client.focus().then((focusedClient) => {
                        if (focusedClient && 'navigate' in focusedClient) {
                            return focusedClient.navigate(resolvedTarget);
                        }
                    });
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(resolvedTarget);
            }
        })
    );
});
JS;

        $response = new DataResponse($swCode);
        $response->addHeader('Content-Type', 'application/javascript; charset=utf-8');
        $response->addHeader('Service-Worker-Allowed', '/');
        return $response;
    }
}
