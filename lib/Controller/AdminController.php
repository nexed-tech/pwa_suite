<?php

namespace OCA\PwaSuite\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IAppConfig;
use OCP\IL10N;
use OCP\Files\IAppData;

class AdminController extends Controller {

    private IAppConfig $appConfig;
    private IAppData $appData;
    private IL10N $l;

    public function __construct(string $appName, IRequest $request, IAppConfig $appConfig, IAppData $appData, IL10N $l) {
        parent::__construct($appName, $request);
        $this->appConfig = $appConfig;
        $this->appData = $appData;
        $this->l = $l;
    }

    public function saveConfig(): JSONResponse {
        $params = $this->request->getParams();

        $keys = [
            'appName' => 'app_name',
            'themeColor' => 'theme_color',
            'bgColor' => 'bg_color',
            'displayMode' => 'display_mode',
            'advancedMode' => 'advanced_mode',
            'customManifest' => 'custom_manifest',
            'customSw' => 'custom_sw',
        ];
        foreach ($keys as $param => $key) {
            if (isset($params[$param])) {
                $this->appConfig->setValueString('pwa_suite', $key, (string)$params[$param]);
            }
        }

        return new JSONResponse(['status' => 'success']);
    }

    public function uploadIcon(): JSONResponse {
        $uploaded = $this->request->getUploadedFile('pwa_icon');

        if (!$uploaded || $uploaded['error'] !== UPLOAD_ERR_OK) {
            return new JSONResponse(['status' => 'error', 'message' => $this->l->t('Error uploading image')], 400);
        }

        $content = file_get_contents($uploaded['tmp_name']);
        if (!$content) {
            return new JSONResponse(['status' => 'error', 'message' => $this->l->t('Empty file')], 400);
        }

        try {
            $folder = $this->appData->getFolder('icons');
        } catch (\Exception $e) {
            $folder = $this->appData->newFolder('icons');
        }

        try {
            $file = $folder->getFile('app-icon.png');
            $file->putContent($content);
        } catch (\Exception $e) {
            $file = $folder->newFile('app-icon.png');
            $file->putContent($content);
        }

        $version = time();
        $this->appConfig->setValueString('pwa_suite', 'has_custom_icon', 'yes');
        $this->appConfig->setValueString('pwa_suite', 'icon_version', (string)$version);

        return new JSONResponse(['status' => 'success', 'version' => $version]);
    }
}
