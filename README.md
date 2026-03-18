# PrintService

Cloud print queue backend + client cabinet for Windows agent integration.

## What is implemented

- Multi-tenant print queue API (`/api/print/v1/...`)
- Client self-registration (creates tenant + owner user)
- Personal cabinet pages:
  - `/dashboard`
  - `/cabinet/agents`
  - `/cabinet/jobs`
  - `/cabinet/api-keys`
- API key generation/revocation for Windows app
- Agent endpoints: poll, ack, fail, heartbeat

## Local setup

1. Configure `.env` database settings.
2. Run:

```bash
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run build
```

3. Open `http://printservice.test`.

## Main docs

- `PRINTSERVICE_TECHNICAL_SPEC.md`
- `OPENAPI_PRINTSERVICE_V1.yaml`
- `DB_SCHEMA_MVP.sql`
- `AGENT_PROTOCOL.md`
- `API_TEST_EXAMPLES.md`
- agent_id — ID конкретного Windows-агента в вашей системе.
  - Это “кто именно печатает”.
  - Нужен в URL типа /agents/{agent_id}/... для ack/fail/heartbeat.
  - Обычно привязан к одной машине/установке.
- agent_key — секретный ключ агента (API key), как пароль для этого агента.
  - Передается в X-Agent-Key.
  - Этим же ключом считается HMAC-подпись X-Signature.
  - Если ключ сменили — старый агент перестанет авторизоваться.
- tenant_code — код клиента/компании (арендатора).
  - Определяет, в чьей “зоне” создавать/читать задания печати.
  - Нужен, чтобы один сервис обслуживал много клиентов отдельно.
- exp — expiration time (время истечения кода активации).
  - Unix timestamp или ISO-время.
  - После этого момента activation code недействителен.
- jti — unique token ID (уникальный ID конкретного activation code).
  - Нужен для одноразовости и защиты от повторного использования (replay).
  - Сервер может пометить jti как “already used”.
Если коротко:
- agent_id = “какой агент”
- agent_key = “секрет этого агента”
- tenant_code = “какой клиент”
- exp = “до какого времени код валиден”
- jti = “уникальный номер этого кода” (чтобы нельзя было применить повторно)