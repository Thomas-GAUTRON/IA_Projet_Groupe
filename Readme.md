# IA_Projet_Groupe

## Présentation

Ce projet est une plateforme web d’analyse de documents PDF, permettant de générer automatiquement des résumés structurés et des quiz interactifs à partir de fichiers PDF. Il s’appuie sur Supabase pour le stockage des données et n8n pour l’automatisation des workflows d’analyse et de génération de contenu.

## Fonctionnalités principales

- **Upload de PDF** : L’utilisateur peut téléverser un document PDF via l’interface web.
- **Génération de résumé** : Un résumé pédagogique et structuré est généré automatiquement à partir du contenu du PDF.
- **Génération de quiz** : Un quiz interactif à choix multiples (QCM) est généré à partir du contenu du PDF.
- **Navigation web** : Accès rapide aux différentes sections (Accueil, Résumés, Quiz).
- **Correction automatique** : Les réponses du quiz sont corrigées instantanément côté client avec explications.

## Architecture technique

- **Frontend** :
  - HTML5, CSS3 (responsive, moderne)
  - JavaScript (validation, correction quiz)
  - PHP (affichage dynamique, récupération des données Supabase)
- **Backend & automatisation** :
  - [n8n](https://n8n.io/) pour l’orchestration des workflows (extraction texte, génération résumé/quiz, stockage Supabase)
  - [Supabase](https://supabase.com/) pour la base de données et l’API REST

## Section technique : Comment ça fonctionne ?

### 1. Téléversement et déclenchement du workflow
- L’utilisateur envoie un PDF via le formulaire web (`index.html`/`upload-pdf.php`).
- Le fichier est transmis à un webhook n8n (`upload-pdf`).

### 2. Extraction et traitement du contenu
- n8n extrait le texte du PDF.
- Selon la catégorie choisie (Résumé, Quiz, ou les deux), n8n :
  - Résume le texte via un agent IA (Google Gemini, Mistral, etc.)
  - Génère un quiz QCM structuré en HTML via un prompt dédié

### 3. Stockage et restitution
- Les résultats (résumé structuré, quiz HTML) sont stockés dans Supabase (table `test`).
- Les pages PHP (`resume.php`, `quizz.php`) récupèrent dynamiquement les données via l’API REST Supabase.
- Le quiz est affiché avec des balises HTML spécifiques pour permettre la correction automatique côté client.

### 4. Correction instantanée côté client
- Le script JS (`script.js`) gère la validation des réponses et l’affichage des explications/corrections sans recharger la page.
- Les blocs `.reponse_bon`, `.reponse_mauvais` et `.explication` sont affichés selon la réponse de l’utilisateur.

### 5. Automatisation et personnalisation
- Le workflow n8n (`General_workflow_1.json`) orchestre toutes les étapes : réception, extraction, génération IA, stockage, réponse à l’utilisateur.
- Les prompts IA sont personnalisables pour adapter le style des résumés ou la difficulté des quiz.

---

## Structure du projet

```
IA_Projet_Groupe/
  Readme.md
  n8n/
    General_workflow_1.json
  website/
    index.html
    quizz.php
    resume.php
    result.php
    upload-pdf.php
    begin_php.php
    header.html
    footer.html
    config.config
    assets/
      css/
        styles.css
      js/
        script.js
      images/
```

## Installation & lancement

1. **Prérequis**
   - PHP 8+
   - Serveur web local (WAMP, XAMPP, etc.)
   - Accès à Supabase (crédits dans `config.config`)
   - n8n installé et configuré

2. **Configuration**
   - Copier `config_example.config` en `config.config` et renseigner les clés Supabase et URLs n8n.

3. **Lancement**
   - Placer le dossier `website/` dans le répertoire web de votre serveur (ex : `www/` sous WAMP).
   - Démarrer le serveur web.
   - Accéder à `http://localhost/IA_Projet_Groupe/website/index.html`.
   - Démarrer n8n et activer le workflow `General_workflow_1.json`.

## Utilisation

- **Accueil** : Téléversez un PDF et choisissez le type d’analyse (Résumé, Quiz, ou les deux).
- **Résumés** : Consultez les résumés générés.
- **Quiz** : Répondez aux quiz générés automatiquement et obtenez votre score instantanément.

## Personnalisation

- Les styles sont modifiables dans `assets/css/styles.css`.
- Le workflow n8n peut être adapté pour d’autres types de documents ou d’analyses.


## Auteurs

- Projet réalisé par le groupe IA_Projet_Groupe
- Encadré par [Nom de l’enseignant ou du référent]

## Licence

Ce projet est open-source, sous licence MIT.