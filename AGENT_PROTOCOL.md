# Windows Agent Protocol (MVP)

## 1. Purpose

Define the runtime protocol between Windows Agent and Cloud API for print queue processing.

---

## 2. Agent Startup Flow

1. Load local config:
   - `agent_key`
   - `api_base_url`
   - polling settings
2. Validate clock drift (NTP recommended).
3. Send initial heartbeat.
4. Enter polling loop.

---

## 3. Request Signing

All agent requests must include:

- `X-Agent-Key`: public token/prefix for lookup
- `X-Timestamp`: UNIX epoch seconds
- `X-Signature`: HMAC-SHA256 hex

Signature payload:

`METHOD + "\n" + PATH + "\n" + X-Timestamp + "\n" + SHA256(BODY)`

Server rejects if:

- timestamp older than allowed skew (default 300s)
- signature mismatch
- key revoked/expired

---

## 4. Polling Loop

Default intervals:

- when job exists: immediate next poll
- when no job: sleep 2s
- on network error: exponential backoff 2s -> 5s -> 10s -> 30s (max)

Algorithm:

1. `GET /agents/next`
2. If `job = null`, sleep and continue.
3. If job exists, mark local state `printing`.
4. Resolve printer.
5. Print payload.
6. On success -> `POST ack`.
7. On failure -> `POST fail`.

---

## 5. Print Job Handling

## 5.1 Supported job type (MVP)

- `raw` only.

## 5.2 Raw print behavior

1. Open selected printer by name/alias.
2. Send payload bytes directly via Winspool.
3. Repeat for `copies`.

## 5.3 Printer selector resolution

Priority:

1. exact local printer name match
2. alias map in config
3. default printer (optional fallback)

If none resolved -> fail with `PRINTER_NOT_FOUND`.

---

## 6. Ack/Fail Rules

## Ack payload

```json
{
  "printed_at": "2026-03-17T15:05:00Z",
  "device_info": "Windows 7 SP1 x86"
}
```

## Fail payload

```json
{
  "failed_at": "2026-03-17T15:05:00Z",
  "error_code": "PRINTER_OFFLINE",
  "error_message": "The printer is offline.",
  "retryable": true
}
```

Recommended `error_code` values:

- `PRINTER_NOT_FOUND`
- `PRINTER_OFFLINE`
- `PRINT_WRITE_ERROR`
- `INVALID_PAYLOAD`
- `UNKNOWN_ERROR`

---

## 7. Heartbeat

Heartbeat interval: every 30s.

Payload:

```json
{
  "agent_version": "1.0.0",
  "os": "Windows 7 SP1 x86",
  "hostname": "KASSA-PC-01",
  "printers": ["XP-58", "EPSON-TM-T20"]
}
```

---

## 8. Local Storage and Logs

Minimum local files:

- `agent.log` (rolling)
- `state.json` (last heartbeat, last poll, last error)
- optional `offline_queue.db` (future)

Log each step with correlation fields:

- `job_id`
- `attempt`
- `printer_selector`
- `duration_ms`

---

## 9. Windows 7 x86 Compatibility Notes

1. Build target must be `x86`.
2. Runtime: .NET Framework 4.8 (or 4.7.2 if required by environment).
3. Force TLS 1.2 in runtime configuration/code.
4. Avoid dependencies requiring .NET 6+ runtime.

---

## 10. Failure Strategy

1. If poll endpoint unavailable -> local backoff.
2. If ack/fail endpoint unavailable after print:
   - keep in local memory queue for resend (short-term)
   - retry send with backoff
3. On agent restart:
   - recover pending unsent acknowledgements if persisted.

---

## 11. Security Recommendations

1. Store secret encrypted using DPAPI (when available).
2. Restrict log output (do not log raw payload for sensitive docs).
3. Rotate agent keys periodically.
4. Pin API host from config and reject unexpected redirects.
