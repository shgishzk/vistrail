# Vistrail

# Vistrail Production Build & Deployment Guide (Vite + Laravel)

This document describes how to prepare and deploy the Vistrail application to production using Laravel and Vite.

---

## 📦 Overview

- Vue, Tailwind CSS, and other frontend assets are compiled using `npm run build` via Vite.
- Compiled assets are output to `public/build/`.
- Laravel's `@vite()` helper automatically references the correct files in production.
- The Vite Dev Server (port 5173) is not used in production.

---

## 🛠️ Install

Run the following command to install the application. Make sure you have Docker and Docker Compose installed.

```bash
$ make install
```

For development, you can use the following command to seed the database with test data:

```bash
$ make seed
```

## ✅ Environment Variables (.env)

Set the following in your `.env` file:

```env
APP_ENV=production
APP_URL=https://your-domain.com

# The following should be removed or commented out in production:
# VITE_DEV_SERVER_URL=http://localhost:5173
```

## 🌐 Other Languages

- 🇯🇵 [日本語 README](README.ja.md)