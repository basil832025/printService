# PrintService Technical Specification (TZ)

## 1. Project Goal

Build a self-hosted print queue platform similar to PrintNode:

- Cloud API receives print jobs from client systems via JSON API.
- Windows Agent (installed at customer site) pulls queued jobs and sends them to local printers.
- Secure binding between agent and tenant via API key.
- Must support old systems, including Windows 7 32-bit.

---

## 2. Scope

### In Scope (MVP)

1. Cloud API for print jobs lifecycle.
2. Multi-tenant architecture.
3. Agent registration + heartbeat.
4. Pull-based queue consumption by agent.
5. Printing mode: `raw` (text/ESC-POS/ZPL).
6. Status tracking, retries, failure handling.
7. Basic admin visibility (agents/jobs state).

### Out of Scope (Post-MVP)

1. Rich desktop UI for agent.
2. Advanced print preview.
3. WebSocket push from cloud to agent.
4. Complex report builder.

---

## 3. High-Level Architecture

## Components

1. **Cloud API (Laravel)**
   - Authenticates job submitters and agents.
   - Stores queue and job history.
   - Exposes endpoints for creating jobs and agent polling.

2. **Windows Agent (x86 build)**
   - Runs as background service/process.
   - Polls cloud for pending jobs.
   - Sends data to selected local printer.
   - Reports success/failure back to cloud.

3. **Database (MySQL)**
   - Stores tenants, agents, jobs, attempts, keys.

## Communication Model

- Pull model only: agent -> cloud polling.
- HTTPS + signed requests.
- Idempotent job submission.

---

## 4. Functional Requirements

## 4.1 Cloud API

1. Accept print jobs from authorized client.
2. Validate payload and enqueue job.
3. Return job ID and initial status.
4. Allow agent to reserve next job atomically.
5. Accept ack/fail updates from agent.
6. Apply retry policy for transient errors.
7. Provide heartbeat endpoint.

## 4.2 Windows Agent

1. Read secure API key/config.
2. Poll cloud endpoint on interval.
3. Reserve one job at a time (or small batch in future).
4. Resolve printer by selector/name.
5. Print raw payload.
6. Return status ack/fail with diagnostics.
7. Keep local log and optional offline buffer.

## 4.3 Multi-tenant

1. Jobs, agents, and keys belong to tenant.
2. Strict tenant isolation in all endpoints.

---

## 5. Non-Functional Requirements

1. **Reliability**: no job loss; at-least-once delivery.
2. **Security**: signed requests, key rotation, revocation.
3. **Performance**: queue retrieval under 500ms for normal load.
4. **Compatibility**: Windows 7 SP1 x86 supported.
5. **Observability**: logs, attempts history, heartbeat timestamps.

---

## 6. API Contract (v1)

Base path: `/api/print/v1`

## 6.1 Create Job

`POST /jobs`

Request:

```json
{
  "tenant_code": "client_a",
  "printer_selector": "cashdesk_1",
  "job_type": "raw",
  "content_type": "text/plain",
  "payload": "...base64 or plain text...",
  "copies": 1,
  "priority": 50,
  "idempotency_key": "order-12345-receipt"
}
```

Response:

```json
{
  "job_id": "b1c2d3e4",
  "status": "queued"
}
```

## 6.2 Poll Next Job

`GET /agents/next`

Headers:

- `X-Agent-Key`
- `X-Timestamp`
- `X-Signature`

Response (job exists):

```json
{
  "job_id": "b1c2d3e4",
  "printer_selector": "cashdesk_1",
  "job_type": "raw",
  "content_type": "text/plain",
  "payload": "...",
  "copies": 1,
  "reserved_until": "2026-03-17T15:04:05Z"
}
```

Response (empty):

```json
{
  "job": null
}
```

## 6.3 Ack Job

`POST /agents/{agent_id}/jobs/{job_id}/ack`

Request:

```json
{
  "printed_at": "2026-03-17T15:05:00Z",
  "device_info": "Win7-x86"
}
```

## 6.4 Fail Job

`POST /agents/{agent_id}/jobs/{job_id}/fail`

Request:

```json
{
  "failed_at": "2026-03-17T15:05:00Z",
  "error_code": "PRINTER_OFFLINE",
  "error_message": "Printer not reachable",
  "retryable": true
}
```

## 6.5 Heartbeat

`POST /agents/{agent_id}/heartbeat`

Request:

```json
{
  "agent_version": "1.0.0",
  "os": "Windows 7 SP1 x86",
  "hostname": "KASSA-PC-01",
  "printers": ["XP-58", "EPSON-TM-T20"]
}
```

---

## 7. Database Design (MVP)

## 7.1 print_tenants

- id (pk)
- code (unique)
- name
- is_active
- created_at, updated_at

## 7.2 print_agents

- id (pk)
- tenant_id (fk)
- name
- machine_uid (unique)
- status (online/offline)
- last_seen_at
- os_info
- version
- created_at, updated_at

## 7.3 print_api_keys

- id (pk)
- tenant_id (fk)
- agent_id (nullable fk)
- key_hash
- key_prefix
- scopes (json)
- expires_at
- revoked_at
- created_at, updated_at

## 7.4 print_jobs

- id (uuid or bigint)
- tenant_id (fk)
- source_system
- printer_selector
- job_type (`raw`)
- content_type
- payload (longtext)
- copies
- priority
- status (`queued`,`reserved`,`printing`,`printed`,`failed`,`retry_wait`)
- idempotency_key (nullable, indexed)
- reserved_by_agent_id (nullable fk)
- reserved_until (nullable datetime)
- attempts_count
- next_retry_at (nullable datetime)
- error_code (nullable)
- error_message (nullable)
- created_at, updated_at, printed_at

## 7.5 print_job_attempts

- id (pk)
- job_id (fk)
- agent_id (nullable fk)
- status (`success`,`fail`)
- error_code
- error_message
- created_at

---

## 8. Job Lifecycle

1. Job created -> `queued`.
2. Agent polls and atomically reserves -> `reserved` + `reserved_until`.
3. Agent prints -> temporary local state `printing`.
4. Agent ack -> `printed` + `printed_at`.
5. Agent fail:
   - retryable -> `retry_wait` + `next_retry_at`.
   - not retryable or max attempts reached -> `failed`.
6. Reaper process returns expired `reserved` jobs back to queue.

---

## 9. Security Model

1. API key shown once on issuance.
2. Store only hashed key server-side.
3. Every agent request signed with HMAC:
   - signature = HMAC_SHA256(secret, method + path + timestamp + body_hash)
4. Reject old timestamps (anti-replay, e.g., > 5 min).
5. TLS required.
6. Key revocation immediate.

---

## 10. Windows Agent Requirements

## 10.1 Runtime

- Target: .NET Framework 4.8
- Build: x86 (required for Win7 32-bit)
- TLS 1.2 enforced in code

## 10.2 Printing

- MVP mode: raw print via Winspool API.
- Printer selection by exact name or alias map.

## 10.3 Process Model

- Start with tray app + background worker.
- Option to run as service in next stage.
- Config file encrypted at rest (DPAPI where available).

## 10.4 Local Resilience

- Local rolling logs.
- Local retry for temporary network errors.
- If API unavailable, keep polling with exponential backoff.

---

## 11. Retry Policy

Default retry schedule for retryable failures:

1. 30 sec
2. 2 min
3. 5 min
4. 15 min

After max attempts -> `failed`.

---

## 12. Admin/Monitoring Requirements

1. View online/offline agents.
2. View queue by status/tenant/agent.
3. Inspect attempts and error logs per job.
4. Manual requeue failed job.
5. Revoke compromised agent key.

---

## 13. Delivery Plan

## Phase A (Backend MVP)

1. Migrations + models.
2. Auth middleware for keys/signatures.
3. API endpoints for jobs + agent flow.
4. Queue reservation logic + retry worker.

## Phase B (Windows Agent MVP)

1. Config + key bootstrap.
2. Poll next job.
3. Raw print implementation.
4. Ack/fail + heartbeat.
5. Packaging for Win7 x86.

## Phase C (Pilot)

1. Deploy cloud API.
2. Install agent at 1-2 pilot locations.
3. Measure success rate and latency.
4. Fix operational issues.

---

## 14. Acceptance Criteria (MVP)

1. Job can be created via API and gets `queued` status.
2. Agent receives only its tenant jobs.
3. Successful print transitions to `printed`.
4. Failed print stores error and retries by policy.
5. No duplicate processing for same idempotency key.
6. Agent works on Windows 7 x86 test machine.
7. Admin can see job history and agent heartbeat.

---

## 15. Open Questions

1. Should MVP support `pdf` now or in phase 2?
2. Is one agent allowed to serve multiple printers in MVP?
3. Do we need strict FIFO or priority-first queue?
4. Required retention for logs/jobs (e.g., 90 days)?

---

## 16. Recommended MVP Defaults

1. Start with `raw` only.
2. One tenant + one agent + one primary printer per pilot location.
3. Priority queue with FIFO inside same priority.
4. Retention 90 days for jobs and attempts.
