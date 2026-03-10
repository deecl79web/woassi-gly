# 🚀 Guide de déploiement — Woassi GLY
## Vercel (frontend + API PHP) + Supabase (base de données PostgreSQL)

---

## 📋 Vue d'ensemble de l'architecture

```
┌─────────────────────────────────────────────────────────┐
│                    INTERNET                             │
│                                                         │
│  Visiteur ──► Vercel ──────────────────────────────────►│
│               │  public/index.html  (HTML/CSS/JS)       │
│               │  api/index.php      (API PHP)           │
│               │                         │               │
│               │                         ▼               │
│               │                    Supabase             │
│               │                    PostgreSQL           │
│               │                    (table inscriptions) │
└─────────────────────────────────────────────────────────┘
```

---

## ÉTAPE 1 — Configurer Supabase (la base de données)

### 1.1 Créer un compte Supabase
1. Va sur **https://supabase.com**
2. Clique sur **"Start your project"** (bouton vert)
3. Connecte-toi avec ton compte **GitHub** (recommandé) ou email
4. Clique sur **"New project"**

### 1.2 Créer le projet
Remplis le formulaire :
- **Organization** : sélectionne ou crée une organisation
- **Name** : `woassi-gly` (ou ce que tu veux)
- **Database Password** : crée un mot de passe fort — **⚠️ note-le immédiatement**, tu en auras besoin
- **Region** : choisis **West EU (Ireland)** ou **South America (São Paulo)** selon ta localisation
- Clique sur **"Create new project"**

> ⏳ Attends 1–2 minutes que le projet soit initialisé.

### 1.3 Créer la table inscriptions
1. Dans le menu gauche, clique sur **"SQL Editor"** (icône `</>`  )
2. Clique sur **"New query"**
3. **Copie-colle exactement ce SQL** :

```sql
CREATE TABLE IF NOT EXISTS inscriptions (
    id         SERIAL PRIMARY KEY,
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    nom        TEXT NOT NULL,
    prenom     TEXT NOT NULL,
    telephone  TEXT NOT NULL,
    superficie TEXT NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_ins_date ON inscriptions(created_at DESC);
```

4. Clique sur **"Run"** (bouton vert, ou `Ctrl+Entrée`)
5. Tu dois voir **"Success. No rows returned"** — la table est créée ✅

### 1.4 Récupérer les informations de connexion
1. Dans le menu gauche, clique sur ⚙️ **"Project Settings"**
2. Clique sur **"Database"** dans le sous-menu
3. Descends jusqu'à la section **"Connection parameters"**
4. Tu verras ces informations — **note-les toutes** :

| Paramètre | Exemple | Ce que tu notes |
|-----------|---------|-----------------|
| Host | `db.xxxxxxxxxxxx.supabase.co` | C'est ton `DB_HOST` |
| Database name | `postgres` | C'est ton `DB_NAME` |
| Port | `5432` | C'est ton `DB_PORT` |
| User | `postgres` | C'est ton `DB_USER` |
| Password | *(celui créé à l'étape 1.2)* | C'est ton `DB_PASSWORD` |

> ⚠️ **Important** : N'utilise pas la "Connection string" complète, mais bien les paramètres séparés.

---

## ÉTAPE 2 — Préparer le projet pour Vercel

### 2.1 Structure du projet (vérification)
Ton projet doit avoir exactement cette structure :
```
woassi-gly/
├── api/
│   └── index.php        ← API PHP (connexion à Supabase)
├── public/
│   └── index.html       ← Interface web (avec génération PDF)
├── setup.sql            ← Script SQL (déjà exécuté sur Supabase)
└── vercel.json          ← Configuration Vercel
```

### 2.2 Vérifier vercel.json
Le fichier `vercel.json` doit contenir :
```json
{
  "functions": {
    "api/index.php": {
      "runtime": "vercel-php@0.7.2"
    }
  },
  "rewrites": [
    { "source": "/api/(.*)", "destination": "/api/index.php" },
    { "source": "/(.*)",     "destination": "/public/index.html" }
  ]
}
```
> Ce fichier est déjà correct dans ton projet ✅

### 2.3 Mettre le projet sur GitHub
Si ton projet n'est pas encore sur GitHub :

```bash
# Dans le dossier de ton projet
git init
git add .
git commit -m "Initial commit — Woassi GLY"

# Sur GitHub.com, crée un nouveau dépôt vide nommé "woassi-gly"
# Puis connecte-le :
git remote add origin https://github.com/TON_USERNAME/woassi-gly.git
git branch -M main
git push -u origin main
```

---

## ÉTAPE 3 — Déployer sur Vercel

### 3.1 Créer un compte Vercel
1. Va sur **https://vercel.com**
2. Clique sur **"Sign Up"**
3. Connecte-toi avec **GitHub** (fortement recommandé — permet le déploiement automatique)

### 3.2 Importer le projet
1. Depuis le dashboard Vercel, clique sur **"Add New… → Project"**
2. Dans la section **"Import Git Repository"**, tu vois tes dépôts GitHub
3. Trouve **"woassi-gly"** et clique sur **"Import"**

### 3.3 Configurer les variables d'environnement ⭐ (étape critique)
Avant de cliquer sur "Deploy", il faut ajouter les variables de connexion à Supabase.

Dans la page de configuration du projet, descends jusqu'à **"Environment Variables"** et ajoute **une par une** :

| Name (clé) | Value (valeur) |
|------------|----------------|
| `DB_HOST` | `db.xxxxxxxxxxxx.supabase.co` *(ton host Supabase)* |
| `DB_NAME` | `postgres` |
| `DB_USER` | `postgres` |
| `DB_PASSWORD` | *(ton mot de passe Supabase)* |
| `DB_PORT` | `5432` |
| `ADMIN_PASSWORD` | *(un mot de passe fort pour l'admin)* |

Pour chaque variable :
1. Tape le **Name**
2. Tape la **Value**
3. Assure-toi que les 3 environnements sont cochés : `Production`, `Preview`, `Development`
4. Clique sur **"Add"**

### 3.4 Lancer le déploiement
1. Clique sur le bouton **"Deploy"**
2. Attends 1–3 minutes — tu vois les logs en temps réel
3. Si tout est vert ✅, Vercel affiche **"Congratulations!"** avec l'URL de ton site

> Ton site est maintenant en ligne sur une URL du type : `https://woassi-gly-xxxxx.vercel.app`

---

## ÉTAPE 4 — Tester le déploiement

### 4.1 Test du formulaire public
1. Va sur ton URL Vercel
2. Remplis le formulaire d'inscription avec des données de test
3. Clique sur **"Soumettre ma demande"**
4. Tu dois voir le message de confirmation vert ✅

### 4.2 Test de l'interface admin
1. Clique sur **"🔐 Espace admin"**
2. Entre le mot de passe que tu as défini dans `ADMIN_PASSWORD`
3. Tu dois voir le tableau de bord avec l'inscription de test ✅

### 4.3 Test de l'export PDF
1. Depuis l'interface admin, clique sur **"Exporter PDF"** (bouton bleu)
2. Un fichier PDF nommé `woassi-gly-inscrits-AAAA-MM-JJ.pdf` doit se télécharger
3. Ouvre-le — il contient le tableau complet des inscrits ✅

### 4.4 Vérifier dans Supabase
1. Retourne sur Supabase → **"Table Editor"**
2. Clique sur la table `inscriptions`
3. Tu vois tes données de test apparaître ✅

---

## ÉTAPE 5 — Résolution des problèmes courants

### ❌ Erreur "Non autorisé 403" sur l'API
**Cause** : La variable `ADMIN_PASSWORD` n'est pas définie sur Vercel.
**Solution** : Aller dans Vercel → Project → Settings → Environment Variables → ajouter `ADMIN_PASSWORD`.

### ❌ Erreur de connexion à la base de données
**Cause** : Mauvaises valeurs dans `DB_HOST` ou `DB_PASSWORD`.
**Solution** :
1. Vérifie dans Supabase → Settings → Database → Connection parameters
2. Mets à jour les variables dans Vercel → Settings → Environment Variables
3. **Redéploie** : Vercel → Deployments → clic sur les 3 points → "Redeploy"

### ❌ L'API retourne du HTML au lieu de JSON
**Cause** : Erreur PHP non capturée.
**Solution** : Vérifie les logs Vercel (Functions → View logs) pour voir l'erreur exacte.

### ❌ Le PDF ne se génère pas
**Cause** : Les bibliothèques jsPDF ne se chargent pas (réseau).
**Solution** : Ouvre la console du navigateur (F12) et vérifie s'il y a des erreurs de chargement de scripts.

### ❌ Erreur "sslmode=require" 
**Cause** : Supabase exige SSL — déjà configuré dans le code PHP ✅ (ne rien changer).

---

## 🔁 Workflow de mise à jour

Pour modifier ton site après déploiement :
```bash
# 1. Fais tes modifications en local
# 2. Commit et push
git add .
git commit -m "Description de la modification"
git push

# Vercel redéploie automatiquement en quelques secondes ✅
```

---

## 🔐 Bonnes pratiques de sécurité

- **Ne jamais** mettre les mots de passe directement dans le code
- Utilise un `ADMIN_PASSWORD` d'au moins 12 caractères (ex : `Woassi@2025!`)
- Dans Supabase, active **Row Level Security (RLS)** si tu exposes l'API publiquement
- Pour la production, considère HTTPS uniquement (Vercel le fait automatiquement ✅)

---

## 📊 Ce que fait le PDF exporté

Le bouton **"Exporter PDF"** dans l'espace admin génère un document PDF professionnel qui contient :
- En-tête sombre aux couleurs Woassi GLY
- Compteur total des inscrits
- Tableau complet : N°, Nom, Prénom, Téléphone, Superficie, Date d'inscription
- Pied de page avec numérotation des pages
- Nom de fichier automatique : `woassi-gly-inscrits-AAAA-MM-JJ.pdf`
- La génération est entièrement côté navigateur (pas de serveur) — fonctionne instantanément

---

*Guide rédigé pour le projet Woassi GLY — Mars 2026*
