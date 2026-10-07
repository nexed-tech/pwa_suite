# pwa_suite

# PWA Suite & Customizer for Nextcloud

Turns any Nextcloud 34/35 instance into a fully installable and customizable Progressive Web App (PWA), configured straight from the admin settings.

## Features
* **Dynamic manifest:** change the app name, interface colors and background colors.
* **Per-app PWAs:** pages of a Nextcloud app link `manifest.json?app=<app>`, which gets `id` and `start_url` set to `/apps/<app>/`. Each app (Calendar, Mail, Talk, …) can therefore be installed as its own PWA, also via Edge/Chrome's `WebAppInstallForceList` policy, instead of all apps collapsing into one. This also applies to the expert-mode custom manifest.
* **Automated Service Worker:** registered transparently, without touching core files.
* **Native integration:** settings panel built into Nextcloud's Theming admin section.
* **Translatable:** English source strings, Spanish included (`l10n/`).

## 🛠️ Maintenance and contributions

This is a personal project maintained on a voluntary basis.

- **Update cycle:** I update the app periodically, in step with updating my own Nextcloud instance (usually for stability, when that version reaches end of life).
- **Version support:** if a new Nextcloud version comes out (e.g. Nextcloud 36, 37…) and you need compatibility before I update my server, **pull requests are very welcome**. If you test the app and confirm compatibility, I'll gladly merge the change and publish a new release.
- If you verify that versions older than 34 are compatible, let me know and I'll lower the minimum (it started at 34 because that's the version I use and tested with).

## License
AGPL-3.0

```
pwa_suite/
├── LICENSE
├── README.md
├── appinfo/
│   ├── info.xml
│   └── routes.php
├── css/
│   └── admin-style.css
├── js/
│   ├── admin-script.js
│   └── pwa-register.js
├── l10n/
│   ├── es.js
│   └── es.json
├── lib/
│   ├── AppInfo/
│   │   └── Application.php
│   ├── Controller/
│   │   ├── AdminController.php
│   │   └── PwaController.php
│   └── Settings/
│       └── AdminSettings.php
└── templates/
    └── admin.php

```
