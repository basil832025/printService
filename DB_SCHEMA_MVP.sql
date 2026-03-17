-- PrintService MVP schema (MySQL 8+)

CREATE TABLE IF NOT EXISTS print_tenants (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(64) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS print_agents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  machine_uid VARCHAR(190) NOT NULL UNIQUE,
  status ENUM('online','offline') NOT NULL DEFAULT 'offline',
  os_info VARCHAR(255) NULL,
  version VARCHAR(64) NULL,
  last_seen_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_print_agents_tenant
    FOREIGN KEY (tenant_id) REFERENCES print_tenants(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS print_api_keys (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  agent_id BIGINT UNSIGNED NULL,
  key_prefix VARCHAR(20) NOT NULL,
  key_hash CHAR(64) NOT NULL,
  scopes JSON NULL,
  expires_at TIMESTAMP NULL,
  revoked_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_print_api_keys_tenant (tenant_id),
  INDEX idx_print_api_keys_agent (agent_id),
  CONSTRAINT fk_print_api_keys_tenant
    FOREIGN KEY (tenant_id) REFERENCES print_tenants(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_print_api_keys_agent
    FOREIGN KEY (agent_id) REFERENCES print_agents(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS print_jobs (
  id CHAR(36) PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  source_system VARCHAR(64) NULL,
  printer_selector VARCHAR(255) NOT NULL,
  job_type ENUM('raw') NOT NULL DEFAULT 'raw',
  content_type VARCHAR(100) NOT NULL,
  payload LONGTEXT NOT NULL,
  copies INT UNSIGNED NOT NULL DEFAULT 1,
  priority INT UNSIGNED NOT NULL DEFAULT 50,
  status ENUM('queued','reserved','printing','printed','failed','retry_wait') NOT NULL DEFAULT 'queued',
  idempotency_key VARCHAR(190) NULL,
  reserved_by_agent_id BIGINT UNSIGNED NULL,
  reserved_until TIMESTAMP NULL,
  attempts_count INT UNSIGNED NOT NULL DEFAULT 0,
  next_retry_at TIMESTAMP NULL,
  error_code VARCHAR(100) NULL,
  error_message TEXT NULL,
  printed_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX idx_print_jobs_tenant_status_priority (tenant_id, status, priority, created_at),
  INDEX idx_print_jobs_retry (status, next_retry_at),
  INDEX idx_print_jobs_reserved_until (reserved_until),
  INDEX idx_print_jobs_idempotency (tenant_id, idempotency_key),
  CONSTRAINT fk_print_jobs_tenant
    FOREIGN KEY (tenant_id) REFERENCES print_tenants(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_print_jobs_reserved_agent
    FOREIGN KEY (reserved_by_agent_id) REFERENCES print_agents(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS print_job_attempts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id CHAR(36) NOT NULL,
  agent_id BIGINT UNSIGNED NULL,
  status ENUM('success','fail') NOT NULL,
  error_code VARCHAR(100) NULL,
  error_message TEXT NULL,
  created_at TIMESTAMP NULL,
  INDEX idx_print_job_attempts_job (job_id),
  INDEX idx_print_job_attempts_agent (agent_id),
  CONSTRAINT fk_print_job_attempts_job
    FOREIGN KEY (job_id) REFERENCES print_jobs(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_print_job_attempts_agent
    FOREIGN KEY (agent_id) REFERENCES print_agents(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
