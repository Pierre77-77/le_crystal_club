# Le Crystal Bar

Site vitrine mobile-first en HTML/CSS pour Le Crystal.

## Lancer en local

Ouvrir directement `docs/index.html` dans un navigateur, ou lancer un petit serveur local :

```powershell
cd docs
python -m http.server 8080
```

Ouvrir ensuite : http://127.0.0.1:8080

## Aperçu en ligne (GitHub Pages)

Le dossier `docs/` est configuré comme racine GitHub Pages (Réglages du dépôt → Pages → Source : branche `main` / dossier `docs`). Cela permet aux collaborateurs de visualiser le site avant la mise en ligne définitive chez OVH.

⚠️ GitHub Pages ne sert que du contenu statique : `envoyer-privatisation.php` ne fonctionnera pas dans cet aperçu (formulaire non fonctionnel), il faudra tester l'envoi de mail directement sur l'hébergement OVH.

## Structure

- `docs/` : **racine web publique**, tout ce qui doit être accessible aux internautes (utilisée aussi comme racine GitHub Pages).
  - `index.html` et autres pages `.html` : contenu du site.
  - `assets/css/styles.css` : charte graphique et responsive mobile-first.
  - `assets/logo/NEW_LOGO_CRYSTAL.webp` : logo principal du site.
  - `assets/img/`, `assets/galerie/`, `assets/video/` : médias du site (images au format WebP, vidéos en MP4).
  - `assets/js/galeries.js` : données des galeries photo par soirée.
  - `envoyer-privatisation.php` : traitement du formulaire de demande de privatisation.
- `config/` et `security/` : **hors de la racine web**, contiennent les identifiants SMTP et la logique d'envoi de mail. Ne doivent jamais être placés dans le dossier pointé par le domaine sur l'hébergement.
  - Ces dossiers sont mutualisés avec d'autres sites sur le même compte OVH : les fichiers propres au Crystal Bar vivent dans les sous-dossiers `config/lecrystalbar/`, `security/lecrystalbar/` et `logs/lecrystalbar/`.

## Déploiement (OVH)

Structure FTP cible sur le compte d'hébergement mutualisé (racine du compte) :

```
/ (racine FTP)
├── lecrystalbar/        ← contenu de docs/ (dossier racine du domaine lecrystalbar.com)
├── config/lecrystalbar/ ← contenu de config/lecrystalbar/
├── security/lecrystalbar/ ← contenu de security/lecrystalbar/
├── logs/lecrystalbar/   ← contenu de logs/lecrystalbar/
└── ... (autres dossiers d'autres sites : events/, www/, redirection_events/...)
```

1. Vider le dossier `lecrystalbar/` existant (actuellement une installation WordPress) après sauvegarde, puis uploader le contenu de `docs/` à la place.
2. Uploader `config/lecrystalbar/`, `security/lecrystalbar/` et `logs/lecrystalbar/` dans les dossiers partagés `config/`, `security/` et `logs/` déjà présents à la racine du compte (sans toucher aux sous-dossiers des autres sites).
3. Éditer `security/lecrystalbar/smtp_secret.php` sur le serveur avec le vrai mot de passe SMTP (ce fichier n'est jamais présent dans Git).