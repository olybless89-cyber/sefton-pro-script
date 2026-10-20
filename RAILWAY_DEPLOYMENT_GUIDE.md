# Railway Deployment Guide

A fresh deploy needs three things: the app service, a MySQL service, and the
app's variables. Everything else (tables, default settings, theme, admin login)
is created automatically on start-up.

---

## 1. Create the services

1. Railway → **New Project** → **Deploy from GitHub repo** → pick this repo.
2. In the same project: **Create** → **Database** → **Add MySQL**.
   Keep the service name **`MySQL`** (the variables below refer to it by name).

## 2. Set the app's variables

Open the **app service** (not MySQL) → **Variables** → **RAW Editor**, and paste
the contents of `.env.railway.example`. Then fill in:

| Variable | What to put |
|---|---|
| `APP_NAME` | Your bank/brand name (used as the site name on first start) |
| `APP_KEY` | Run `echo "base64:$(openssl rand -base64 32)"` and paste the result |
| `ADMIN_EMAIL` | The email you want to log in to the admin panel with |
| `ADMIN_PASSWORD` | The admin password |

Or do it all from a Mac terminal (edit the values first):

```bash
railway link    # choose the project, then the app service
railway variables \
  --set 'APP_NAME=Your Bank Name' \
  --set "APP_KEY=base64:$(openssl rand -base64 32)" \
  --set 'APP_ENV=production' \
  --set 'APP_DEBUG=false' \
  --set 'APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}' \
  --set 'ADMIN_EMAIL=admin@yourbank.com' \
  --set 'ADMIN_PASSWORD=choose-a-strong-password' \
  --set 'DB_CONNECTION=mysql' \
  --set 'DB_HOST=${{MySQL.MYSQLHOST}}' \
  --set 'DB_PORT=${{MySQL.MYSQLPORT}}' \
  --set 'DB_DATABASE=${{MySQL.MYSQLDATABASE}}' \
  --set 'DB_USERNAME=${{MySQL.MYSQLUSER}}' \
  --set 'DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}'
railway domain
```

Keep the single quotes around `${{...}}` so the shell doesn't touch them.

## 3. Check it

- Deploy log should show `==> Database is ready.`, migrations running, and
  `Admin login set for ...`.
- Site: `https://<your-domain>/`
- Admin: `https://<your-domain>/admin` (log in with ADMIN_EMAIL / ADMIN_PASSWORD)

`ADMIN_PASSWORD` is re-applied on every restart. To change the password from
the admin panel instead, delete the `ADMIN_PASSWORD` variable after first login.

## 4. Branding

- **Name, title, contact email**: admin panel → Settings.
- **Logo / favicon**: replace these two files in the repo and push
  (uploads through the admin panel are lost on redeploy because the container
  has no persistent volume):
  - `storage/app/public/photos/default-branding-logo.png` (708×179 PNG)
  - `storage/app/public/photos/default-branding-favicon.png` (square PNG)

## 5. Custom domain

```bash
railway domain yourdomain.com
railway variables --set 'APP_URL=https://yourdomain.com'
```

Add the DNS records it prints at your registrar. HTTPS is issued automatically.

## Troubleshooting

| Symptom | Cause / fix |
|---|---|
| `Connection refused ... table_schema = forge` | `DB_*` variables missing on the **app** service. Set them as in step 2. |
| `DB_HOST` shows blank in `railway variables` | No service named `MySQL`. Rename it, or change `MySQL.` in the references. |
| 500 error on every page | `APP_KEY` missing. The log prints a line to set one. |
| Push to GitHub doesn't deploy | Railway's GitHub app can't see this repo. Reconnect under service **Settings → Source**, or press **Cmd+K → Deploy Latest Commit**. |
