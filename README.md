# Laravel Template — Deploy to Render (Docker)

A minimal **Laravel** API with **no database**. Items are stored in Laravel's *file cache*
(`storage/framework/cache`), which is temporary on Render, so data resets on every restart or redeploy.
Configuration comes from **environment variables** (`.env` locally, the Render dashboard in production).

Part of the *Intro to DevOps* course. Sister projects with the same API:
[FastAPI](https://github.com/Abdalaziz-Tello/fastapi_example_to_mock_piplines) ·
[NestJS](https://github.com/Abdalaziz-Tello/nestjs_template_for_pipeliens_session)

## Requirements

- PHP **8.3+** and Composer (locally)
- Render deploys it with the included **`Dockerfile`**, because Render has no native PHP runtime

## Run locally

```bash
composer install
cp .env.example .env
php artisan key:generate   # fills APP_KEY in .env
php artisan serve          # http://localhost:8000
```

Open:

- **API:** http://localhost:8000
- **API docs (Scramble):** http://localhost:8000/docs
- **OpenAPI schema:** http://localhost:8000/openapi.json

## Endpoints

| Method | Path          | Description                                  |
|--------|---------------|----------------------------------------------|
| GET    | `/`           | Welcome message (from `WELCOME_MESSAGE`)     |
| GET    | `/health`     | Health check                                 |
| GET    | `/config`     | Shows the config the server loaded (no secrets) |
| GET    | `/items`      | List items                                   |
| GET    | `/items/{id}` | Get one item                                 |
| POST   | `/items`      | Create an item                               |
| PATCH  | `/items/{id}` | Partially update                             |
| DELETE | `/items/{id}` | Delete an item                               |

```bash
curl -X POST http://localhost:8000/items \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"name": "Book", "price": 12.5}'
```

## Environment variables

| Variable          | Example              | Notes                                         |
|-------------------|----------------------|-----------------------------------------------|
| `APP_KEY`         | `base64:...`         | Generate with `php artisan key:generate --show`. **Secret** |
| `APP_NAME`        | `Laravel Template`   |                                               |
| `APP_ENV`         | `production`         | `local` on your machine                       |
| `APP_DEBUG`       | `false`              | **Must be `false` in production** (otherwise errors leak code paths) |
| `APP_URL`         | `https://<service>.onrender.com` |                                   |
| `APP_VERSION`     | `0.1.0`              |                                               |
| `WELCOME_MESSAGE` | `Hello from Render!` | Returned by `GET /`                           |
| `API_SECRET`      | `some-long-random-string` | Only reported as `api_secret_configured: true/false` |
| `PORT`            | `10000`              | **Render sets this automatically**            |

No database settings are needed: sessions, cache and queue default to `file` / `sync`.
In code, read settings with `config('app.welcome_message')`, never `env(...)`, because the
container runs `php artisan config:cache` on start.
**Never commit `.env`**, only `.env.example`.

## Test

```bash
php artisan test
./vendor/bin/pint --test   # code style
```

## Run with Docker (optional, same as Render)

```bash
docker build -t laravel-template .
docker run --rm -p 10000:10000 -e APP_KEY=$(php artisan key:generate --show) laravel-template
# open http://localhost:10000
```

## Deploy to Render

1. Push this repo to **your own public GitHub repo**.
2. On [render.com](https://render.com) → **New → Web Service** → connect the repo.
3. Settings:

   | Field             | Value                         |
   |-------------------|-------------------------------|
   | Language          | **Docker** (Render detects the `Dockerfile`) |
   | Instance Type     | Free                          |
   | Health Check Path | `/health`  (Advanced)         |

4. **Environment** → add `APP_KEY`, `APP_NAME`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`,
   `APP_VERSION`, `WELCOME_MESSAGE`, `API_SECRET`.
5. **Deploy** and open `https://<your-service>.onrender.com/config`. You should see `app_key_configured: true`.

> Free instances sleep after ~15 min without traffic; the first request afterwards takes up to a minute.
