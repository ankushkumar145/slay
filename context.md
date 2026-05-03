# SLAY Project Context

## Product

SLAY is a playful dating-profile assistant. The goal is to make the app feel useful, cute, sharp, and confidence-building without becoming bloated. The core user journey is:

1. Sign up or log in.
2. Create a dating profile base.
3. Answer a short set of personality/signal questions.
4. Generate a dating kit with scores, bio rewrites, prompts, advice, and exports.
5. Use Love Guru chat as a separate feature for reply help, date prep, and mixed-signal advice.

The app should feel like a proper mobile app, not a single long scrolling page. Current navigation uses bottom tabs:

- Home
- Chat
- Analyze
- Me

The old disabled Kit tab was removed because the feature did not justify a permanent nav item.

## Current Architecture

The repo has two apps:

```text
backend/  Laravel API
mobile/   Expo React Native client
```

Backend responsibilities:

- Auth
- User profile persistence
- Question list
- Dating kit generation
- Love Guru chat
- Groq integration

Mobile responsibilities:

- Auth screens
- Profile setup
- Dashboard
- Question flow
- Results display
- Love Guru chat
- Copy/share interactions
- Local web persistence for token and latest result

## Backend Notes

Framework:

- Laravel 10
- Sanctum token auth
- SQLite for local development

Important files:

- `backend/routes/api.php`
- `backend/app/Http/Controllers/AuthController.php`
- `backend/app/Http/Controllers/UserProfileController.php`
- `backend/app/Http/Controllers/SlayProfileController.php`
- `backend/app/Http/Controllers/LoveGuruController.php`
- `backend/app/Services/GroqProfileService.php`
- `backend/app/Services/LoveGuruService.php`
- `backend/app/Models/UserProfile.php`

Important routes:

- `POST /api/auth/register`
- `POST /api/auth/login`
- `GET /api/auth/me`
- `POST /api/auth/logout`
- `GET /api/profile`
- `PUT /api/profile`
- `GET /api/questions`
- `POST /api/analyze`
- `POST /api/guru/chat`

Groq env:

```env
GROQ_API_KEY=
GROQ_BASE_URL=https://api.groq.com/openai/v1
GROQ_MODEL=openai/gpt-oss-20b
```

The app has fallback logic, so analysis/chat should still return a structured response when the AI call fails or is unavailable.

## Mobile Notes

Framework:

- Expo
- React Native
- React Native Web
- `@expo/vector-icons`
- `expo-clipboard`

Important files:

- `mobile/App.js`
- `mobile/src/api.js`
- `mobile/src/constants.js`
- `mobile/src/utils.js`
- `mobile/src/theme/styles.js`
- `mobile/src/components/AppHeader.js`
- `mobile/src/components/BottomTabs.js`
- `mobile/src/screens/AuthScreen.js`
- `mobile/src/screens/DashboardScreen.js`
- `mobile/src/screens/ProfileScreen.js`
- `mobile/src/screens/QuizScreen.js`
- `mobile/src/screens/ResultsScreen.js`
- `mobile/src/screens/GuruScreen.js`

API URL behavior:

- Web/iOS default: `http://127.0.0.1:8000/api`
- Android emulator default: `http://10.0.2.2:8000/api`
- Override with `EXPO_PUBLIC_API_URL`

Local persistence:

- `slay_token`
- `slay_last_result`

These are stored only on web through `localStorage`.

## UI Direction

The latest direction from product feedback:

- Reduce bloat.
- Avoid long full-scroll pages where everything feels stacked.
- Keep separate screens for auth, profile, dashboard, question flow, results, and Love Guru.
- Bottom tabs should be clear and useful.
- UI should be sharp, intuitive, and cute.
- Avoid overly large typography and oversized cards.
- Avoid "AI slop" feel: no random decorative panels, no huge empty hero sections, no confusing disabled tabs.
- Prefer compact rows, clear controls, good hierarchy, useful icons, and direct labels.

Recent UI changes:

- Dashboard became a compact workbench-style screen.
- Bottom nav became a cleaner app bar.
- Loading screen got its own centered panel.
- Cards, shadows, and radii were reduced for a sharper look.
- Icons use `@expo/vector-icons`; `lucide-react-native` was removed because it caused Metro `.mjs` import issues.

## Design Principles

Use these principles for future UI work:

- Each tab should have a clear reason to exist.
- One screen should usually have one primary action.
- Keep results scannable: show the key output first, then details.
- Use icons to support meaning, not decorate randomly.
- Make empty/loading/error states feel designed.
- Keep copy short and confident.
- Make the app feel playful through tone, color, and small details, not through oversized layouts.

## Known Development Commands

Backend:

```powershell
cd backend
php artisan serve
php artisan test
php artisan migrate
```

Mobile:

```powershell
cd mobile
npm start
npm run web
npx expo export --platform web --output-dir export-check
```

Physical phone:

```powershell
cd backend
php artisan serve --host=0.0.0.0 --port=8000
```

```powershell
cd mobile
$env:EXPO_PUBLIC_API_URL="http://YOUR_PC_IP:8000/api"
npm start
```

## Current Verification

Recent checks passed:

- Laravel tests previously passed.
- Expo web export passed after UI changes.

When running Expo export in this environment, Metro may need permission to spawn worker processes. If export creates an output folder like `export-check`, remove it after verification unless the user explicitly wants to keep it.

## Product Next Steps

Good next improvements:

- Improve onboarding with a short, friendly first-run profile setup.
- Save Love Guru chat history per user.
- Make analysis results persistent on the backend, not only local web storage.
- Add a "latest kit" detail screen if it becomes a real feature.
- Add profile completeness and better empty states.
- Add loading states for analyze and Love Guru that match the new splash style.
- Add validation and friendlier error messages around auth/profile.
- Add more realistic question categories and tune scoring.

## Constraints And Preferences

- Do not commit real secrets.
- Keep `backend/.env` private.
- Keep `.env.example` minimal and useful.
- Prefer small, focused UI improvements over large redesigns.
- Avoid adding new dependencies unless they clearly improve the app.
- Use existing files and patterns before creating new abstractions.
