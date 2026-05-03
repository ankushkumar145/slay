# SLAY

SLAY is a playful dating-profile assistant. It helps a user create a profile, answer quick personality signals, generate a dating profile kit, and chat with a Love Guru AI for replies, date prep, and mixed-signal advice.

The project is split into:

- `backend` - Laravel API with auth, profile storage, analysis, and Groq-powered AI responses.
- `mobile` - Expo React Native app for web, Android, and iOS.

## Features

- Email/password auth with Laravel Sanctum tokens.
- User profile setup for age, dating goal, target match, current bio, and export style.
- Quick analysis flow that scores attraction signals and generates a dating kit.
- Results screen with optimized bio, platform exports, suggestions, and copy/share actions.
- Love Guru chat feature powered by Groq.
- Bottom-tab mobile app structure: Home, Chat, Analyze, and Me.
- Offline-ish preview fallback for questions if the backend is not running.

## Requirements

- PHP 8.1+
- Composer
- Node.js 18+
- npm
- Expo CLI through `npx`
- Groq API key

## Backend Setup

```powershell
cd backend
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Open `backend/.env` and set:

```env
DB_CONNECTION=sqlite
GROQ_API_KEY=your_groq_key_here
GROQ_BASE_URL=https://api.groq.com/openai/v1
GROQ_MODEL=openai/gpt-oss-20b
```

Create the SQLite database file if it does not exist:

```powershell
New-Item -ItemType File -Force database/database.sqlite
php artisan migrate
```

Start the API:

```powershell
php artisan serve
```

By default the API runs at:

```text
http://127.0.0.1:8000/api
```

## Mobile Setup

In a second terminal:

```powershell
cd mobile
npm install
npm start
```

For web:

```powershell
npm run web
```

For Android emulator, the app uses:

```text
http://10.0.2.2:8000/api
```

For web and iOS simulator, the app uses:

```text
http://127.0.0.1:8000/api
```

For a physical phone on the same Wi-Fi, start Laravel on your LAN:

```powershell
cd backend
php artisan serve --host=0.0.0.0 --port=8000
```

Then start Expo with your computer IP:

```powershell
cd mobile
$env:EXPO_PUBLIC_API_URL="http://YOUR_PC_IP:8000/api"
npm start
```

## Useful Commands

Backend tests:

```powershell
cd backend
php artisan test
```

Mobile web export check:

```powershell
cd mobile
npx expo export --platform web --output-dir export-check
```

## API Overview

Public routes:

- `POST /api/auth/register`
- `POST /api/auth/login`
- `GET /api/questions`
- `POST /api/analyze`
- `POST /api/guru/chat`

Authenticated routes:

- `GET /api/auth/me`
- `POST /api/auth/logout`
- `GET /api/profile`
- `PUT /api/profile`

## Environment

Backend env values used by the app:

```env
APP_NAME=SLAY
APP_ENV=local
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
GROQ_API_KEY=
GROQ_BASE_URL=https://api.groq.com/openai/v1
GROQ_MODEL=openai/gpt-oss-20b
```

Mobile env override:

```env
EXPO_PUBLIC_API_URL=http://127.0.0.1:8000/api
```

## Troubleshooting

- If the mobile app says the backend is offline, make sure `php artisan serve` is running.
- If Android cannot reach the API, use `10.0.2.2:8000` for the emulator or your LAN IP for a real phone.
- If AI results fail, confirm `GROQ_API_KEY` is set in `backend/.env`.
- If database errors appear, run `php artisan migrate`.

## Project Structure

```text
slayy/
  backend/
    app/
    database/
    routes/api.php
    .env.example
  mobile/
    src/
      components/
      screens/
      theme/
      api.js
    App.js
    package.json
```

## Notes

This is a local-first development app. Do not commit real `.env` secrets. Keep Groq keys in `backend/.env` only.
