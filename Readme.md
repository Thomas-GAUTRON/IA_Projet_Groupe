# IA_Projet_Groupe

## Présentation

**IA_Projet_Groupe** est une plateforme web complète permettant d’analyser des documents PDF, de générer automatiquement des résumés pédagogiques et des quiz interactifs à partir de leur contenu, grâce à l’intelligence artificielle (Google Gemini, Mistral, etc.).  
Le projet combine un backend Python (Flask), une interface web PHP/JS, l’automatisation via n8n, et le stockage des résultats sur Supabase.

---

## Fonctionnalités principales

- **Téléversement de PDF** : Interface web pour uploader un ou plusieurs fichiers PDF.
- **Extraction et traitement IA** : Extraction du texte, génération de résumés et de quiz QCM via des prompts IA personnalisés.
- **Restitution dynamique** : Résumés et quiz affichés dynamiquement, correction instantanée côté client.
- **Téléchargement** : Export du quiz au format PDF.
- **Automatisation** : Orchestration complète via n8n (workflows personnalisables).
- **Stockage cloud** : Résultats sauvegardés et consultables via Supabase.
- **Personnalisation** : Prompts IA, styles, et workflows adaptables.

---

## Architecture technique

### 1. Frontend (website/)

- **PHP** : Pages dynamiques (index.php, result.php, resume.php, quizz.php, call_flask.php…)
- **HTML/CSS** : Interface responsive (`assets/css/styles.css`)
- **JavaScript** : Correction automatique des quiz (`assets/js/script.js`)
- **Templates** : header.html, footer.html

### 2. Backend Python (basic/)

- **Flask** : API pour l’upload, l’extraction, l’appel à l’IA et le rendu de templates (`app.py`)
- **Traitement PDF** : Extraction du texte via PyPDF2 (`classes.py`)
- **Génération IA** : Prompts et appels à Google Gemini/Mistral (`gemini_incl.py`, `Prompt.txt`)
- **Quiz JS** : Interface quiz autonome (`quiz_part/` : index.html, quiz.js, quiz.json, styles.css)

### 3. Automatisation & Stockage

- **n8n/** : Workflows d’automatisation (extraction, génération, stockage, notifications…)
- **Supabase** : Stockage des résultats (résumés, quiz, métadonnées)

---

## Schéma de fonctionnement

1. **Upload** : L’utilisateur charge un ou plusieurs PDF via l’interface web.
2. **Traitement** :
   - Le backend Python extrait le texte, prépare les données.
   - L’IA génère un résumé et/ou un quiz selon l’option choisie.
3. **Automatisation** :
   - n8n orchestre l’envoi, la génération, le stockage et la restitution.
4. **Restitution** :
   - Les résultats sont affichés dynamiquement (résumé, quiz interactif, score, explications).
   - Possibilité de télécharger le quiz en PDF.
5. **Stockage** :
   - Les résultats sont sauvegardés sur Supabase et consultables à tout moment.

---

## Structure du projet

```
IA_Projet_Groupe/
│
├── basic/                # Backend Python (Flask, IA, extraction PDF)
│   ├── app.py
│   ├── classes.py
│   ├── gemini_incl.py
│   ├── Prompt.txt
│   ├── requirements.txt
│   ├── result_prep.py
│   ├── ex.json
│   ├── program.py
│   ├── test.py
│   ├── templates/
│   │   ├── index.html
│   │   └── result.html
│   └── quiz_part/
│       ├── index.html
│       ├── quiz.js
│       ├── quiz2.js
│       ├── quiz.json
│       └── styles.css
│
├── n8n/                  # Workflows d’automatisation
│   └── General_workflow_1.json
│
├── website/              # Frontend PHP/JS
│   ├── index.php
│   ├── result.php
│   ├── result2.php
│   ├── resume.php
│   ├── quizz.php
│   ├── call_flask.php
│   ├── all_generate.php
│   ├── change.php
│   ├── begin_php.php
│   ├── header.html
│   ├── footer.html
│   ├── config_example.config
│   ├── conf.config
│   └── assets/
│       ├── css/
│       │   └── styles.css
│       └── js/
│           └── script.js
│
└── Readme.md
```

---

## Installation & configuration

### Prérequis

- Python 3.10+
- PHP 8+
- Serveur web local (WAMP, XAMPP, etc.)
- n8n (auto-hébergé ou cloud)
- Compte Supabase (base de données PostgreSQL)
- Clé API Google Gemini (ou autre IA compatible)

### Dépendances Python

Dans `basic/requirements.txt` :
```
Flask
PyPDF2
```
Installer avec :
```bash
pip install -r basic/requirements.txt
```

### Configuration

1. **Supabase & n8n**  
   Copier `website/config_example.config` en `website/conf.config` et renseigner :
   - `supabase_url` : URL de votre projet Supabase
   - `supabase_key` : clé API (publique)
   - `table_name` : nom de la table (ex : documents)
   - `n8n_webhook_url_*` : URLs de vos webhooks n8n

2. **Backend Python**  
   - Placer vos clés API IA dans les variables d’environnement ou un fichier `.env` (pour Google Gemini).

3. **Lancement**
   - Démarrer le backend Flask :
     ```bash
     cd basic
     python app.py
     ```
   - Démarrer le serveur web (WAMP/XAMPP) et accéder à `http://localhost/IA_Projet_Groupe/website/index.php`
   - Démarrer n8n et activer le workflow `General_workflow_1.json`

---

## Utilisation

- **Accueil** : Téléversez un ou plusieurs PDF, choisissez le type d’analyse (Résumé, Quiz, ou les deux).
- **Résumés** : Consultez les résumés générés (format texte enrichi, markdown).
- **Quiz** : Répondez aux quiz générés automatiquement, correction instantanée, explications détaillées, score affiché.
- **Téléchargement** : Exportez le quiz au format PDF.
- **Personnalisation** : Modifiez les prompts IA (`Prompt.txt`), les styles CSS, ou les workflows n8n selon vos besoins.

---

## Exemples de formats générés

### Résumé (extrait)
```
---ABSTRACT START---
Le document traite des principes fondamentaux de l’optique, notamment la réfraction, la réflexion et la vitesse de la lumière...
---ABSTRACT END---
```

### Quiz (extrait de quiz.json)
```json
{
    "courseTitle": "Cours de Physique - Optique",
    "quiz": [
        {
            "question": "Qu'est-ce que la réfraction ?",
            "choices": [
                "Changement de direction de la lumière",
                "Absorption de lumière",
                "Diffusion",
                "Réflexion"
            ],
            "answer": "Changement de direction de la lumière",
            "explanation": "La réfraction est le changement de direction d'une onde lorsqu'elle passe d'un milieu à un autre."
        }
    ]
}
```

---

## Personnalisation avancée

- **Prompts IA** : Modifiez `Prompt.txt` ou les prompts dans `gemini_incl.py` pour adapter le style, la langue, la difficulté, etc.
- **Workflows n8n** : Personnalisez `n8n/General_workflow_1.json` pour ajouter des étapes (notifications, stockage avancé, etc.).
- **Styles** : Adaptez `website/assets/css/styles.css` et `basic/quiz_part/styles.css` pour personnaliser l’UI.
- **Quiz JS** : Modifiez `basic/quiz_part/quiz.js` pour changer la logique ou l’affichage du quiz.

---

## Dépannage

- **Problème d’API IA** : Vérifiez la clé dans `.env` ou les variables d’environnement.
- **Erreur Supabase** : Vérifiez l’URL, la clé et le nom de la table dans `conf.config`.
- **n8n** : Assurez-vous que le workflow est actif et que les webhooks sont accessibles.
- **PDF non traité** : Vérifiez les logs Flask et la compatibilité du PDF.

---

## Auteurs

- Projet réalisé par le groupe IA_Projet_Groupe
- Encadré par [Nom de l’enseignant ou du référent]

## Licence

Ce projet est open-source, sous licence MIT.