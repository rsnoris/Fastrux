-- =============================================================================
-- Fastrux — Digital Wallet System Database Schema
-- =============================================================================
-- Production-grade schema supporting:
--   • Multi-currency wallets with states (active, frozen, closed)
--   • Double-entry accounting ledger (immutable)
--   • Peer-to-peer transfers and external payments
--   • Invoice lifecycle with partial payments
--   • Tokenised payment methods (PCI-DSS Req 3.3 — no raw PANs stored)
--   • Idempotency keys for safe retries
--   • Full audit trail
--   • Multi-tenant support
--   • KYC/KYB placeholder columns
--   • Webhook delivery tracking
-- =============================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ---------------------------------------------------------------------------
-- Table: tenants  (multi-tenant support)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tenants (
    id           VARCHAR(36)  NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    name         VARCHAR(200) NOT NULL,
    plan         ENUM('starter','business','enterprise') NOT NULL DEFAULT 'starter',
    status       ENUM('active','suspended','closed')     NOT NULL DEFAULT 'active',
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Table: users
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id                VARCHAR(36)   NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    tenant_id         VARCHAR(36)   NULL,
    email             VARCHAR(320)  NOT NULL,
    password_hash     VARCHAR(255)  NOT NULL COMMENT 'Bcrypt/Argon2id hash — never store plaintext',
    role              ENUM(
                        'shipper','customer','driver','owner_operator',
                        'corporate_staff','admin','super_admin',
                        'insurance_company','trucking_company','gas_station','hotel'
                      )             NOT NULL DEFAULT 'shipper',
    status            ENUM('active','suspended','pending') NOT NULL DEFAULT 'active',
    -- KYC/KYB placeholders
    kyc_status        ENUM('not_started','pending','verified','failed') NOT NULL DEFAULT 'not_started',
    kyb_status        ENUM('not_started','pending','verified','failed') NOT NULL DEFAULT 'not_started',
    kyc_provider_ref  VARCHAR(100)  NULL COMMENT 'Reference ID from KYC provider (e.g. Jumio, Onfido)',
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_email (email),
    KEY idx_users_tenant (tenant_id),
    CONSTRAINT fk_users_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Table: wallets
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS wallets (
    id          VARCHAR(36)   NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    wallet_id   VARCHAR(20)   NOT NULL COMMENT 'Human-readable: WAL-XXXXXXXXXXXX',
    user_id     VARCHAR(36)   NOT NULL,
    tenant_id   VARCHAR(36)   NULL,
    currency    CHAR(3)       NOT NULL DEFAULT 'USD' COMMENT 'ISO 4217',
    balance     DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    status      ENUM('active','frozen','closed') NOT NULL DEFAULT 'active',
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wallet_id      (wallet_id),
    UNIQUE KEY uq_wallet_user_currency (user_id, currency) COMMENT 'One wallet per currency per user',
    KEY idx_wallets_user         (user_id),
    KEY idx_wallets_tenant       (tenant_id),
    CONSTRAINT fk_wallets_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE RESTRICT,
    CONSTRAINT fk_wallets_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL,
    CONSTRAINT chk_wallet_balance CHECK (balance >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Table: payment_methods  (tokenised — PCI-DSS Req 3.3)
-- NEVER stores raw PAN, CVV, or full expiry.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payment_methods (
    id               VARCHAR(36)  NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    user_id          VARCHAR(36)  NOT NULL,
    type             ENUM('card','ach','wire','paypal') NOT NULL DEFAULT 'card',
    -- Provider token (Stripe PaymentMethod ID, Adyen token, etc.)
    provider         ENUM('stripe','adyen','plaid','internal') NOT NULL DEFAULT 'stripe',
    provider_token   VARCHAR(255) NOT NULL   COMMENT 'Processor token — not raw card data',
    -- Safe display metadata
    card_brand       VARCHAR(20)  NULL       COMMENT 'visa, mastercard, amex, discover, …',
    card_last4       CHAR(4)      NULL       COMMENT 'Last 4 digits for display only',
    card_exp_month   TINYINT      NULL       COMMENT '1-12',
    card_exp_year    SMALLINT     NULL       COMMENT '4-digit year',
    billing_name     VARCHAR(200) NULL,
    billing_address  VARCHAR(500) NULL,
    is_default       TINYINT(1)   NOT NULL DEFAULT 0,
    status           ENUM('active','expired','removed') NOT NULL DEFAULT 'active',
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_pm_user (user_id),
    CONSTRAINT fk_pm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Table: transactions  (immutable financial events)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS transactions (
    id               VARCHAR(36)   NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    transaction_id   VARCHAR(20)   NOT NULL COMMENT 'Human-readable: TXN-XXXXXXXXXXXX',
    wallet_id        VARCHAR(36)   NOT NULL,
    user_id          VARCHAR(36)   NOT NULL,
    type             ENUM(
                       'deposit','withdrawal','transfer_out','transfer_in',
                       'payment','card_payment','invoice_payment','invoice_receipt',
                       'refund','adjustment'
                     ) NOT NULL,
    amount           DECIMAL(18,2) NOT NULL,
    currency         CHAR(3)       NOT NULL DEFAULT 'USD',
    status           ENUM('pending','completed','failed','reversed') NOT NULL DEFAULT 'pending',
    description      VARCHAR(500)  NULL,
    reference        VARCHAR(100)  NULL  COMMENT 'payment_id, invoice_id, or external ref',
    metadata         JSON          NULL  COMMENT 'Arbitrary key-value pairs',
    idempotency_key  VARCHAR(128)  NULL,
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tx_id             (transaction_id),
    UNIQUE KEY uq_tx_idempotency    (idempotency_key),
    KEY idx_tx_wallet               (wallet_id),
    KEY idx_tx_user                 (user_id),
    KEY idx_tx_status               (status),
    KEY idx_tx_created              (created_at),
    CONSTRAINT fk_tx_wallet         FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE RESTRICT,
    CONSTRAINT fk_tx_user           FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Table: ledger_entries  (double-entry accounting — immutable)
-- Every financial event produces exactly one debit and one credit row.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ledger_entries (
    id           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    entry_id     VARCHAR(20)      NOT NULL COMMENT 'Human-readable: LED-XXXXXXXXXXXX',
    type         ENUM('debit','credit') NOT NULL,
    account      VARCHAR(200)     NOT NULL COMMENT 'e.g. user:USR-XXX, system:external_card',
    amount       DECIMAL(18,2)    NOT NULL,
    currency     CHAR(3)          NOT NULL DEFAULT 'USD',
    tx_ref       VARCHAR(100)     NOT NULL COMMENT 'Transaction ID this entry belongs to',
    description  VARCHAR(500)     NULL,
    created_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_entry_id     (entry_id),
    KEY idx_led_account        (account),
    KEY idx_led_tx_ref         (tx_ref),
    KEY idx_led_created        (created_at),
    CONSTRAINT chk_led_amount  CHECK (amount > 0)
    -- No UPDATE or DELETE privileges should be granted on this table
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Immutable double-entry accounting ledger. No row may be updated or deleted.';

-- ---------------------------------------------------------------------------
-- Table: invoices
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS invoices (
    id               VARCHAR(36)   NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    invoice_id       VARCHAR(20)   NOT NULL COMMENT 'Human-readable: INV-XXXXXXXXXXXX',
    issuer_user_id   VARCHAR(36)   NOT NULL,
    payer_user_id    VARCHAR(36)   NOT NULL,
    tenant_id        VARCHAR(36)   NULL,
    description      VARCHAR(500)  NULL,
    currency         CHAR(3)       NOT NULL DEFAULT 'USD',
    total_amount     DECIMAL(18,2) NOT NULL,
    amount_paid      DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    amount_due       DECIMAL(18,2) NOT NULL,
    status           ENUM('draft','pending','partial','paid','overdue','cancelled') NOT NULL DEFAULT 'pending',
    due_date         DATE          NULL,
    cancel_reason    VARCHAR(500)  NULL,
    idempotency_key  VARCHAR(128)  NULL,
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_invoice_id        (invoice_id),
    UNIQUE KEY uq_invoice_idem      (idempotency_key),
    KEY idx_inv_issuer              (issuer_user_id),
    KEY idx_inv_payer               (payer_user_id),
    KEY idx_inv_status              (status),
    KEY idx_inv_due_date            (due_date),
    CONSTRAINT fk_inv_issuer        FOREIGN KEY (issuer_user_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_inv_payer         FOREIGN KEY (payer_user_id)  REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_inv_tenant        FOREIGN KEY (tenant_id)      REFERENCES tenants(id) ON DELETE SET NULL,
    CONSTRAINT chk_inv_amounts      CHECK (amount_paid >= 0 AND amount_due >= 0 AND total_amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Table: invoice_line_items
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS invoice_line_items (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    invoice_id   VARCHAR(36)     NOT NULL,
    description  VARCHAR(500)    NOT NULL,
    quantity     DECIMAL(10,4)   NOT NULL,
    unit_price   DECIMAL(18,2)   NOT NULL,
    line_total   DECIMAL(18,2)   NOT NULL,
    created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_line_invoice (invoice_id),
    CONSTRAINT fk_line_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    CONSTRAINT chk_line_qty    CHECK (quantity > 0),
    CONSTRAINT chk_line_price  CHECK (unit_price >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Table: invoice_payments  (payments made against a specific invoice)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS invoice_payments (
    id               VARCHAR(36)   NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    invoice_id       VARCHAR(36)   NOT NULL,
    payer_user_id    VARCHAR(36)   NOT NULL,
    transaction_id   VARCHAR(36)   NULL COMMENT 'Linked wallet transaction',
    payment_method   ENUM('wallet','card','ach','wire') NOT NULL DEFAULT 'wallet',
    amount           DECIMAL(18,2) NOT NULL,
    currency         CHAR(3)       NOT NULL DEFAULT 'USD',
    status           ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'completed',
    idempotency_key  VARCHAR(128)  NULL,
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_invpay_idem    (idempotency_key),
    KEY idx_invpay_invoice       (invoice_id),
    KEY idx_invpay_payer         (payer_user_id),
    CONSTRAINT fk_invpay_invoice FOREIGN KEY (invoice_id)     REFERENCES invoices(id)     ON DELETE RESTRICT,
    CONSTRAINT fk_invpay_payer   FOREIGN KEY (payer_user_id)  REFERENCES users(id)        ON DELETE RESTRICT,
    CONSTRAINT fk_invpay_tx      FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Table: idempotency_keys  (prevent duplicate API operations)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS idempotency_keys (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `key`        VARCHAR(128)    NOT NULL,
    response     JSON            NOT NULL,
    created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at   DATETIME        NOT NULL DEFAULT (DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 24 HOUR)),
    UNIQUE KEY uq_idempotency_key (`key`),
    KEY idx_idem_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Table: audit_log  (immutable financial audit trail)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_log (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    audit_id     VARCHAR(20)     NOT NULL COMMENT 'AUD-XXXXXXXX',
    timestamp    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    action       VARCHAR(100)    NOT NULL COMMENT 'e.g. wallet.created, invoice.paid',
    user_id      VARCHAR(36)     NULL,
    entity_type  VARCHAR(50)     NULL,
    entity_id    VARCHAR(100)    NULL,
    details      VARCHAR(1000)   NULL,
    ip_address   VARCHAR(45)     NULL,
    user_agent   VARCHAR(200)    NULL,
    UNIQUE KEY uq_audit_id   (audit_id),
    KEY idx_audit_user       (user_id),
    KEY idx_audit_entity     (entity_type, entity_id),
    KEY idx_audit_action     (action),
    KEY idx_audit_timestamp  (timestamp)
    -- No UPDATE or DELETE privileges should be granted on this table
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Immutable audit log. No row may be updated or deleted.';

-- ---------------------------------------------------------------------------
-- Table: webhooks  (event delivery tracking)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS webhooks (
    id           VARCHAR(36)   NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    user_id      VARCHAR(36)   NOT NULL,
    tenant_id    VARCHAR(36)   NULL,
    url          VARCHAR(2000) NOT NULL,
    events       JSON          NOT NULL COMMENT 'Array of event names to subscribe to',
    secret_hash  VARCHAR(255)  NOT NULL COMMENT 'HMAC signing key — stored as Argon2id hash',
    status       ENUM('active','paused','failed') NOT NULL DEFAULT 'active',
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_wh_user   (user_id),
    KEY idx_wh_tenant (tenant_id),
    CONSTRAINT fk_wh_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT fk_wh_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Table: webhook_deliveries  (per-event delivery attempts)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS webhook_deliveries (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    webhook_id    VARCHAR(36)     NOT NULL,
    event_type    VARCHAR(100)    NOT NULL,
    payload       JSON            NOT NULL,
    attempt_count TINYINT         NOT NULL DEFAULT 0,
    last_status   SMALLINT        NULL COMMENT 'HTTP response code',
    last_error    VARCHAR(500)    NULL,
    delivered_at  DATETIME        NULL,
    next_retry_at DATETIME        NULL,
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_whdel_webhook    (webhook_id),
    KEY idx_whdel_event      (event_type),
    KEY idx_whdel_retry      (next_retry_at),
    CONSTRAINT fk_whdel_webhook FOREIGN KEY (webhook_id) REFERENCES webhooks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Table: feature_flags  (gradual rollout)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS feature_flags (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)    NOT NULL,
    enabled     TINYINT(1)      NOT NULL DEFAULT 0,
    rollout_pct TINYINT         NOT NULL DEFAULT 0 COMMENT '0-100 percent rollout',
    tenant_ids  JSON            NULL COMMENT 'Null = all tenants',
    user_ids    JSON            NULL COMMENT 'Null = all users',
    description VARCHAR(500)    NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_flag_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Table: disputes  (chargeback / dispute tracking)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS disputes (
    id               VARCHAR(36)   NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    transaction_id   VARCHAR(36)   NOT NULL,
    raised_by        VARCHAR(36)   NOT NULL,
    reason           ENUM(
                       'duplicate','fraudulent','product_not_received',
                       'product_unacceptable','credit_not_processed','general'
                     ) NOT NULL DEFAULT 'general',
    status           ENUM('open','under_review','resolved_win','resolved_loss','closed') NOT NULL DEFAULT 'open',
    amount           DECIMAL(18,2) NOT NULL,
    currency         CHAR(3)       NOT NULL DEFAULT 'USD',
    notes            TEXT          NULL,
    resolved_at      DATETIME      NULL,
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_dispute_tx     (transaction_id),
    KEY idx_dispute_user   (raised_by),
    KEY idx_dispute_status (status),
    CONSTRAINT fk_dispute_tx   FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE RESTRICT,
    CONSTRAINT fk_dispute_user FOREIGN KEY (raised_by)      REFERENCES users(id)        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Table: refunds
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS refunds (
    id               VARCHAR(36)   NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    original_tx_id   VARCHAR(36)   NOT NULL,
    refund_tx_id     VARCHAR(36)   NULL COMMENT 'The new credit transaction',
    requested_by     VARCHAR(36)   NOT NULL,
    amount           DECIMAL(18,2) NOT NULL,
    currency         CHAR(3)       NOT NULL DEFAULT 'USD',
    status           ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
    reason           VARCHAR(500)  NULL,
    idempotency_key  VARCHAR(128)  NULL,
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_refund_idem   (idempotency_key),
    KEY idx_refund_orig_tx      (original_tx_id),
    KEY idx_refund_by           (requested_by),
    CONSTRAINT fk_refund_orig   FOREIGN KEY (original_tx_id) REFERENCES transactions(id) ON DELETE RESTRICT,
    CONSTRAINT fk_refund_new    FOREIGN KEY (refund_tx_id)   REFERENCES transactions(id) ON DELETE SET NULL,
    CONSTRAINT fk_refund_user   FOREIGN KEY (requested_by)   REFERENCES users(id)        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Table: reconciliation_reports  (daily settlement)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reconciliation_reports (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    report_date     DATE            NOT NULL,
    tenant_id       VARCHAR(36)     NULL,
    currency        CHAR(3)         NOT NULL DEFAULT 'USD',
    opening_balance DECIMAL(18,2)   NOT NULL,
    closing_balance DECIMAL(18,2)   NOT NULL,
    total_deposits  DECIMAL(18,2)   NOT NULL DEFAULT 0.00,
    total_withdrawals DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    total_transfers DECIMAL(18,2)   NOT NULL DEFAULT 0.00,
    total_fees      DECIMAL(18,2)   NOT NULL DEFAULT 0.00,
    transaction_count INT           NOT NULL DEFAULT 0,
    status          ENUM('draft','finalised') NOT NULL DEFAULT 'draft',
    generated_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_recon_date_tenant_currency (report_date, tenant_id, currency),
    KEY idx_recon_date   (report_date),
    KEY idx_recon_tenant (tenant_id),
    CONSTRAINT fk_recon_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================================
-- Default feature flags
-- =============================================================================
INSERT IGNORE INTO feature_flags (name, enabled, rollout_pct, description) VALUES
  ('wallet_multi_currency',  0,   0, 'Enable multi-currency wallet support'),
  ('p2p_transfers',          1, 100, 'Enable peer-to-peer wallet transfers'),
  ('invoice_partial_payment',1, 100, 'Allow partial invoice payments'),
  ('ach_withdrawals',        0,   0, 'Enable ACH bank withdrawals'),
  ('fraud_detection_v2',     0,  10, 'Fraud detection v2 — gradual rollout');
