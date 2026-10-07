<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
?>
<div class="section pwa-admin-section">
    <h2>PWA Suite &amp; Customizer</h2>
    <p class="settings-hint"><?php p($l->t('Configure the identity, icon, colors and behavior of your corporate PWA.')); ?></p>

    <div class="pwa-field-group">
        <label for="pwa-app-name"><?php p($l->t('PWA name:')); ?></label>
        <input type="text" id="pwa-app-name" value="<?php p($_['appName']); ?>" placeholder="Nextcloud PWA" />
    </div>

    <div class="pwa-field-group" style="margin-top: 15px;">
        <label for="pwa-icon-file"><?php p($l->t('PWA icon (PNG, 512x512 recommended):')); ?></label>
        <div style="display: flex; align-items: center; gap: 15px; margin-top: 5px;">
            <img id="pwa-icon-preview" src="<?php p($_['iconUrl']); ?>" alt="<?php p($l->t('Icon preview')); ?>" style="width: 48px; height: 48px; border-radius: 8px; border: 1px solid var(--color-border);" />
            <input type="file" id="pwa-icon-file" accept="image/png,image/jpeg,image/webp" />
        </div>
    </div>

    <div class="pwa-field-group" style="margin-top: 15px;">
        <label for="pwa-theme-color"><?php p($l->t('Theme color:')); ?></label>
        <input type="color" id="pwa-theme-color" value="<?php p($_['themeColor']); ?>" />
    </div>

    <div class="pwa-field-group">
        <label for="pwa-bg-color"><?php p($l->t('Background color (splash screen):')); ?></label>
        <input type="color" id="pwa-bg-color" value="<?php p($_['bgColor']); ?>" />
    </div>

    <div class="pwa-field-group">
        <label for="pwa-display-mode"><?php p($l->t('Display mode:')); ?></label>
        <select id="pwa-display-mode">
            <option value="standalone" <?php if ($_['displayMode'] === 'standalone') p('selected'); ?>><?php p($l->t('Standalone (native app)')); ?></option>
            <option value="minimal-ui" <?php if ($_['displayMode'] === 'minimal-ui') p('selected'); ?>><?php p($l->t('Minimal UI')); ?></option>
            <option value="fullscreen" <?php if ($_['displayMode'] === 'fullscreen') p('selected'); ?>><?php p($l->t('Fullscreen')); ?></option>
            <option value="browser" <?php if ($_['displayMode'] === 'browser') p('selected'); ?>><?php p($l->t('Browser (regular tab)')); ?></option>
        </select>
    </div>

    <div class="pwa-field-group" style="margin-top: 20px;">
        <input type="checkbox" id="pwa-advanced-toggle" <?php if ($_['advancedMode'] === 'yes') p('checked'); ?> />
        <label for="pwa-advanced-toggle"><?php p($l->t('Enable expert mode (manual override)')); ?></label>
    </div>

    <div id="pwa-advanced-section" style="<?php echo $_['advancedMode'] === 'yes' ? 'display: block;' : 'display: none;'; ?> margin-top: 15px;">
        <p class="settings-hint" style="color: #e9322d;"><?php p($l->t('Warning: if the fields below contain code, they override the visual settings above.')); ?></p>
        <p class="settings-hint"><?php p($l->t('On pages of a Nextcloud app, "id" and "start_url" are set to that app (/apps/<app>/), so every app can be installed as a separate PWA.')); ?></p>
        <p class="settings-hint"><?php p($l->t('Optional: an "apps" object with per-app overrides keyed by app id (e.g. "spreed" for Talk) is merged into the manifest of that app, e.g. "apps": { "spreed": { "name": "Talk", "icons": [...] } }.')); ?></p>

        <div class="pwa-field-group">
            <label for="pwa-custom-manifest"><?php p($l->t('Custom manifest JSON:')); ?></label>
            <textarea id="pwa-custom-manifest" rows="8" style="width: 100%; font-family: monospace;" placeholder='{ "name": "My App" }'><?php p($_['customManifest']); ?></textarea>
        </div>

        <div class="pwa-field-group">
            <label for="pwa-custom-sw"><?php p($l->t('Custom Service Worker JS:')); ?></label>
            <textarea id="pwa-custom-sw" rows="8" style="width: 100%; font-family: monospace;" placeholder="self.addEventListener('fetch', ...);"><?php p($_['customSw']); ?></textarea>
        </div>
    </div>

    <div style="margin-top: 20px;">
        <button id="pwa-save-btn" class="button primary"><?php p($l->t('Save changes')); ?></button>
        <span id="pwa-save-msg" style="margin-left: 10px; font-weight: bold;"></span>
    </div>
</div>
