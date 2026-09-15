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

## Déploiement (OVH)

1. Uploader le contenu de `docs/` dans le dossier configuré comme racine web du domaine (ex. `www/`).
2. Uploader `config/` et `security/` **au même niveau que `www/`, pas dedans** (ex. directement dans le dossier FTP racine du compte hébergement), afin qu'ils soient physiquement inaccessibles depuis le web, quelle que soit la configuration Apache.
3. Éditer `security/smtp_secret.php` sur le serveur avec le vrai mot de passe SMTP (ce fichier n'est jamais présent dans Git).