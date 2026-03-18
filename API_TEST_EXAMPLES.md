# API Test Examples (Local)

Base URL:

`http://printservice.test/api/print/v1`

Agent key for local bootstrap:

`psk_local_dev_change_me`

## 1) Create Job

```bash
curl -X POST "http://printservice.test/api/print/v1/jobs" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "tenant_code": "default",
    "printer_selector": "cashdesk_1",
    "job_type": "raw",
    "content_type": "text/plain",
    "payload": "Hello printer\\n",
    "copies": 1,
    "priority": 50,
    "idempotency_key": "order-100500-receipt"
  }'
```

## 2) Poll Next Job (signed)

Signature formula:

`HMAC_SHA256(agent_key, METHOD + "\n" + PATH + "\n" + TIMESTAMP + "\n" + SHA256(BODY))`

Example helper in PHP:

```bash
php -r "
\$key='psk_local_dev_change_me';
\$method='GET';
\$path='/api/print/v1/agents/next';
\$timestamp=time();
\$body='';
\$payloadHash=hash('sha256', \$body);
\$canonical=strtoupper(\$method)."\n".\$path."\n".\$timestamp."\n".\$payloadHash;
\$sig=hash_hmac('sha256', \$canonical, \$key);
echo \$timestamp."\n".\$sig;
"
```

Then use returned `timestamp` and `signature`:

```bash
curl -X GET "http://printservice.test/api/print/v1/agents/next" \
  -H "Accept: application/json" \
  -H "X-Agent-Key: psk_local_dev_change_me" \
  -H "X-Timestamp: <timestamp>" \
  -H "X-Signature: <signature>"
```
