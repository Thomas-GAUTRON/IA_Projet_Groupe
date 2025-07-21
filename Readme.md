# IA_Projet_Groupe

## Présentation

**IA_Projet_Groupe** est une plateforme web complète d’analyse, de génération de résumés et de quiz interactifs à partir de documents PDF, exploitant l’intelligence artificielle (Google Gemini, Mistral, etc.).

- **Backend Python (Flask)** : Extraction, traitement IA, génération de PDF LaTeX (avec équations mathématiques).
- **Frontend PHP/JS** : Interface utilisateur moderne, upload, restitution dynamique, correction instantanée, téléchargement PDF.
- **Stockage cloud** : Supabase.

---

## Fonctionnalités principales

- **Téléversement de PDF** (multi-fichiers)
- **Extraction de texte** et traitement IA (résumé, quiz)
- **Quiz interactif** avec correction et explications
- **Affichage dynamique** (résumé, quiz, score)
- **Téléchargement PDF** (résumé ou quiz, rendu mathématique LaTeX)
- **Personnalisation avancée** (prompts, styles)
- **Stockage cloud** (Supabase)

---

## Architecture technique

```
[ Utilisateur ]
      |
      v
[ PHP/JS (website/) ] <----> [ Python/Flask (basic_2/) ]
      |                                 
      v                                 
[ Supabase (cloud) ]       
```

- **Frontend** : PHP (pages dynamiques), JS (quiz, interactions, appel API Flask)
- **Backend** : Flask (API, extraction, IA, génération PDF LaTeX)
- **PDF** : Génération serveur (rendu parfait des équations)
- **Stockage** : Supabase (résultats, quiz, logs)

---

## Structure du projet (2025)

```
IA_Projet_Groupe/
│
├── basic_2/                # Backend Python (Flask, IA, extraction PDF, génération PDF)
│   ├── app.py              # Serveur Flask, routes API, upload, génération PDF
│   ├── classes.py          # Gestion des PDF et résultats
│   ├── gemini_incl.py      # Prompts et appels à l’IA
│   ├── Prompt.txt          # Exemples de prompts IA
│   ├── requirements.txt    # Dépendances Python
│   ├── result_prep.py      # Utilitaires de post-traitement, génération PDF LaTeX
│   ├── ex.json             # Exemple de quiz généré
│
├── website/                # Frontend PHP/JS
│   ├── index.php           # Accueil, upload PDF
│   ├── quizz.php           # Affichage quiz dynamique
│   ├── form.php            # Formulaire d’upload
│   ├── load.php            # Envoi des fichiers au backend Flask
│   ├── dashboard.php       # Tableau de bord utilisateur
│   ├── login.php           # Authentification
│   ├── register.php        # Inscription
│   ├── oauth_callback.php  # OAuth callback
│   ├── change.php          # Script de modification
│   ├── begin_php.php       # Init session/config
│   ├── header.html         # En-tête HTML
│   ├── footer.html         # Pied de page HTML
│   ├── assets/
│   │   ├── css/
│   │   │   └── styles.css  # Styles principaux du site
│   │   └── js/
│   │       └── script.js   # Script JS principal (quiz, interactions, appel API)
│
├── config_example.env      # Exemple de configuration (à copier en .env)
└── Readme.md               # Documentation du projet
```

---

## Rôle des fichiers/dossiers principaux

| Élément                        | Rôle/Fonction principale                                      |
|------------------------------- |--------------------------------------------------------------|
| basic_2/app.py                 | Serveur Flask, upload, IA, génération PDF LaTeX               |
| basic_2/classes.py             | Gestion des PDF et résultats                                  |
| basic_2/gemini_incl.py         | Prompts et appels à l’IA                                      |
| basic_2/Prompt.txt             | Exemples de prompts IA                                        |
| basic_2/requirements.txt       | Dépendances Python                                            |
| basic_2/result_prep.py         | Post-traitement, génération PDF LaTeX                         |
| basic_2/ex.json                | Exemple de quiz généré                                        |
| basic_2/uploads/               | Stockage temporaire des PDF uploadés                          |
| basic_2/templates/             | Templates HTML Flask                                          |
| website/index.php              | Accueil, upload PDF                                           |
| website/quizz.php              | Affichage quiz dynamique                                      |
| website/form.php               | Formulaire d’upload PDF                                       |
| website/load.php               | Envoi des fichiers au backend Flask                           |
| website/dashboard.php          | Tableau de bord utilisateur                                   |
| website/login.php              | Authentification                                              |
| website/register.php           | Inscription                                                   |
| website/oauth_callback.php     | OAuth callback                                                |
| website/change.php             | Script de modification                                        |
| website/begin_php.php          | Init session/config                                           |
| website/header.html            | En-tête HTML                                                  |
| website/footer.html            | Pied de page HTML                                             |
| website/assets/css/styles.css  | Styles principaux du site                                     |
| website/assets/js/script.js    | Script JS principal (quiz, interactions, appel API)           |
| config_example.env             | Exemple de configuration globale (à copier en .env)           |
| Readme.md                      | Documentation du projet                                       |

---

## Installation & configuration

### Prérequis

- Python 3.10+
- PHP 8+
- Serveur web local (WAMP, XAMPP, etc.)
- Compte Supabase (base de données PostgreSQL)
- Clé API Google Gemini (ou autre IA compatible)
- **pdflatex** installé (pour la génération PDF LaTeX)

### Dépendances Python

Dans `basic_2/requirements.txt` :
```
Flask
PyPDF2
fpdf
langchain-google-genai
pdfkit
markdown
```
Installer avec :
```bash
pip install -r basic_2/requirements.txt
```

### Configuration

1. **Supabase**  
   Copier `config_example.env` en `.env` et renseigner :
   - `SUPABASE_URL` : URL de votre projet Supabase
   - `SUPABASE_KEY` : clé API (publique)
   - `SUPABASE_TABLE` : nom de la table (ex : documents)
   - `FLASK_URL` : URL de l’API Flask

2. **Backend Python**  
   - Placer vos clés API IA dans les variables d’environnement ou un fichier `.env` (pour Google Gemini).
   - Vérifier que `pdflatex` est installé et accessible dans le PATH.

3. **Lancement**
   - Démarrer le backend Flask :
     ```bash
     cd basic_2
     python app.py
     ```
   - Démarrer le serveur web (WAMP/XAMPP) et accéder à `http://localhost/IA_Projet_Groupe/website/index.php`

---

## Utilisation

- **Accueil** : Téléversez un ou plusieurs PDF, choisissez le type d’analyse (Résumé, Quiz, ou les deux).
- **Résumés** : Consultez les résumés générés (format texte enrichi, LaTeX).
- **Quiz** : Répondez aux quiz générés automatiquement, correction instantanée, explications détaillées, score affiché.
- **Téléchargement PDF** :
  - Résumés et quiz peuvent être exportés en PDF (rendu mathématique parfait via LaTeX).
  - Le bouton "Télécharger le quiz en PDF" appelle le backend Python qui compile le LaTeX et sert le PDF.
- **Personnalisation** : Modifiez les prompts IA (`Prompt.txt`), les styles CSS, selon vos besoins.

---

## Personnalisation avancée

- **Prompts IA** : Modifiez `Prompt.txt` ou les prompts dans `gemini_incl.py` pour adapter le style, la langue, la difficulté, etc.
- **Styles** : Adaptez `website/assets/css/styles.css` pour personnaliser l’UI.
- **Quiz JS** : Modifiez `website/assets/js/script.js` pour changer la logique ou l’affichage du quiz.

---

## Dépannage & FAQ

- **Problème d’API IA** : Vérifiez la clé dans `.env` ou les variables d’environnement.
- **Erreur Supabase** : Vérifiez l’URL, la clé et le nom de la table dans `.env`.
- **PDF non généré** : Vérifiez les logs Flask et la présence de `pdflatex`.
- **Upload ne fonctionne pas** : Vérifiez les droits sur le dossier `uploads/` et la configuration Flask.
- **Quiz ou résumé non affiché** : Vérifiez la communication entre le frontend (PHP) et le backend (Flask).

---

## Auteurs

- Projet réalisé par le groupe IA_Projet_Groupe
- Encadré par [Nom de l’enseignant ou du référent]

## Licence

Ce projet est open-source, sous licence MIT.