# Le Crystal Bar

Site vitrine mobile-first en HTML/CSS pour Le Crystal.

## Lancer en local

Ouvrir directement `index.html` dans un navigateur, ou lancer un petit serveur local :

```powershell
python -m http.server 8080
```

Ouvrir ensuite : http://127.0.0.1:8080

## Structure

- `index.html` et autres pages `.html` : contenu du site.
- `assets/css/styles.css` : charte graphique et responsive mobile-first.
- `assets/logo/NEW_LOGO_CRYSTAL.webp` : logo principal du site.
- `assets/img/`, `assets/galerie/`, `assets/video/` : médias du site (images au format WebP, vidéos en MP4).
- `assets/js/galeries.js` : données des galeries photo par soirée.
- `envoyer-privatisation.php` : traitement du formulaire de demande de privatisation.

## Déploiement

Copier le contenu du dossier sur un hébergement web supportant PHP (nécessaire pour `envoyer-privatisation.php`).