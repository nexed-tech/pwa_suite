<?php

namespace OCA\PwaSuite\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\IAppConfig;
use OCP\IURLGenerator;
use OCP\Util;

class AdminSettings implements ISettings {

    private IAppConfig $appConfig;
    private IURLGenerator $urlGenerator;

    public function __construct(IAppConfig $appConfig, IURLGenerator $urlGenerator) {
        $this->appConfig = $appConfig;
        $this->urlGenerator = $urlGenerator;
    }

    public function getForm(): TemplateResponse {
        Util::addTranslations('pwa_suite');
        Util::addScript('pwa_suite', 'admin-script');

        $iconVersion = $this->get('icon_version', '1');
        $iconUrl = $this->urlGenerator->linkToRoute('pwa_suite.pwa.getIcon') . '?v=' . $iconVersion;

        $parameters = [
            'appName' => $this->get('app_name', 'Nextcloud PWA'),
            'themeColor' => $this->get('theme_color', '#181818'),
            'bgColor' => $this->get('bg_color', '#181818'),
            'displayMode' => $this->get('display_mode', 'standalone'),
            'advancedMode' => $this->get('advanced_mode', 'no'),
            'customManifest' => $this->get('custom_manifest', ''),
            'customSw' => $this->get('custom_sw', ''),
            'iconUrl' => $iconUrl,
        ];

        return new TemplateResponse('pwa_suite', 'admin', $parameters, '');
    }

    private function get(string $key, string $default): string {
        return $this->appConfig->getValueString('pwa_suite', $key, $default);
    }

    public function getSection(): string {
        return 'theming';
    }

    public function getPriority(): int {
        return 50;
    }
}
