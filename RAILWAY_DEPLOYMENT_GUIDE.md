# Sefton Pro - Railway Deployment & Auto-Deploy Guide

This project is fully containerized and pre-configured for **Railway** with **Continuous Auto-Deployment**.

---

## 1. How Auto-Deploy Works on Railway
Whenever you push code changes to your GitHub repository (`git push origin main`), Railway automatically detects the new commit, builds the Docker container, executes database migrations, and updates your live site with zero downtime.

---

## 2. Step-by-Step Setup Instructions

### Step A: Push this Project to GitHub
1. Create a new repository on [GitHub](https://github.com/new) (e.g. `seftonpro`). It can be Public or Private.
2. In your local terminal inside `C:\Users\hp\Downloads\seftonpro`, run:
   ```bash
   git remote add origin https://github.com/YOUR_GITHUB_USERNAME/seftonpro.git
   git push -u origin main
   ```

---

### Step B: Create Project on Railway
1. Go to [railway.app](https://railway.app/) and sign in.
2. Click **"New Project"** -> **"Deploy from GitHub repo"**.
3. Select your `seftonpro` repository.
4. Railway will automatically detect the `Dockerfile` and `railway.json`.

---

### Step C: Add a MySQL Database on Railway
1. Inside your Railway project canvas, click **"Create"** or **"+"** -> **"Database"** -> **"Add MySQL"**.
2. Railway will provision a managed MySQL database in seconds.

---

### Step D: Set Up Environment Variables
1. Click on your `seftonpro` service box in Railway.
2. Navigate to the **"Variables"** tab.
3. Click **"RAW Editor"** (top right of Variables tab).
4. Copy and paste the contents from `.env.railway.example`:
   ```env
   APP_NAME="Sefton Pro"
   APP_ENV=production
   APP_KEY=base64:HZYKNCrnaonG7t1vjlDN3HBXitaRjzny2KMNPNQLK88=
   APP_DEBUG=false
   APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}
   LOG_CHANNEL=stack
   LOG_LEVEL=error
   DB_CONNECTION=mysql
   DB_HOST=${{MySQL.MYSQLHOST}}
   DB_PORT=${{MySQL.MYSQLPORT}}
   DB_DATABASE=${{MySQL.MYSQLDATABASE}}
   DB_USERNAME=${{MySQL.MYSQLUSER}}
   DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
   BROADCAST_DRIVER=log
   CACHE_DRIVER=file
   FILESYSTEM_DRIVER=local
   QUEUE_CONNECTION=sync
   SESSION_DRIVER=file
   SESSION_LIFETIME=120
   ```
   *(Notice: Railway automatically substitutes `${{MySQL.MYSQL...}}` with the live database credentials!)*

---

### Step E: Generate a Public Domain
1. In your `seftonpro` service, go to **"Settings"** tab.
2. Scroll to the **"Networking"** section.
3. Click **"Generate Domain"** (e.g. `seftonpro-production.up.railway.app`).
4. Railway will deploy and your application is live!

---

## 3. Auto-Deployment in Action
From now on, whenever you make changes:
```bash
git add .
git commit -m "Update feature XYZ"
git push origin main
```
Railway will automatically detect the commit, trigger a fresh build, run migrations, and re-deploy!
