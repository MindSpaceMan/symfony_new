# 📱 Phone Verification API

Backend test project for Indigolab (Middle PHP Developer). This service provides user registration and authentication via phone number confirmation codes.

---

## 🚀 Stack

- **PHP 8.3** (CLI Alpine)
- **Symfony 7.2**
- **Doctrine DBAL**
- **PostgreSQL**
- **Redis** (optional)
- **Docker / Docker Compose**
- **Swagger (NelmioApiDocBundle)**

---

## 📦 Installation

```bash
git clone https://github.com/yourname/phone-verification-api.git
cd phone-verification-api
cp .env.dist .env
docker-compose up --build
```

App will be available at `http://localhost:8337`

Swagger docs: `http://localhost:8337/api/doc`

---

## ⚙️ Environment Variables

```env
POSTGRES_VERSION=16
POSTGRES_DB=app
POSTGRES_USER=app
POSTGRES_PASSWORD=!ChangeMe!
POSTGRES_HOST=database
XDEBUG_CLIENT_HOST=host.docker.internal
XDEBUG_IDEKEY=PHPSTORM
```

---

## 📚 API Endpoints

### 1. Request Confirmation Code

`POST /api/request-code`

```json
{
  "phone": "+1234567890"
}
```

✅ Returns code (in real app you'd send via SMS).

Responses:
- `200 OK` — Code returned
- `429 Too Many Requests` — Throttling limit reached
- `400 Bad Request` — Invalid phone input

### 2. Verify Confirmation Code

`POST /api/verify-code`

```json
{
  "phone": "+1234567890",
  "code": "1234"
}
```

Responses:
- `200 OK` — User authorized
- `201 Created` — User registered
- `400 Bad Request` — Invalid or expired code

---

## 🧠 Logic Summary

- 4-digit code generated (`0000`–`9999`)
- One code per minute per phone
- Limit: 3 codes per 15 min → blocks for 1 hour
- Code validity: 5 minutes
- If user exists — logs in
- If user doesn’t exist — registers and binds phone
- Unverified numbers stored in `pending_phones`

---

## 🐋 Docker Services

- `sio_test`: PHP + Symfony
- `database`: PostgreSQL 16-alpine
- (Optional) `redis`: for caching rate limits

---

## ✅ Tests (optional)

```bash
docker-compose exec sio_test ./bin/phpunit
```

---

## 👨‍💻 Author

Middle Backend Developer Test — Indigolab
Feel free to ping me at [your.email@domain.com] or open issues if anything’s unclear.

---

## 📘 License

MIT
Запустить - make init
php init_db.php
смотреть файл requests.http или просто через постман тестировать.