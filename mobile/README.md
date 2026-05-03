# SLAY Mobile

React Native app for the SLAY attraction optimization engine.

## Run

```bash
npm start
```

For Android emulator, the app uses:

```text
http://10.0.2.2:8000/api
```

For iOS simulator/web, it uses:

```text
http://127.0.0.1:8000/api
```

For a physical phone on the same Wi-Fi, start Laravel on your LAN and pass your PC IP:

```bash
cd ../backend
php artisan serve --host=0.0.0.0 --port=8000
```

```bash
set EXPO_PUBLIC_API_URL=http://YOUR_PC_IP:8000/api
npm start
```
