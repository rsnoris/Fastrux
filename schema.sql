-- Fastrux MySQL Schema
-- Run once to initialise the database:
--   mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS fastrux CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
--   mysql -u root -p fastrux < schema.sql

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ── Users ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id            VARCHAR(36)   NOT NULL,
    first_name    VARCHAR(100)  NOT NULL DEFAULT '',
    last_name     VARCHAR(100)  NOT NULL DEFAULT '',
    email         VARCHAR(255)  NOT NULL,
    company       VARCHAR(255)  NOT NULL DEFAULT '',
    role          ENUM('shipper','customer','driver','owner_operator','corporate_staff','admin')
                                NOT NULL DEFAULT 'shipper',
    password_hash VARCHAR(255)  NOT NULL,
    status        ENUM('active','suspended','deleted') NOT NULL DEFAULT 'active',
    last_login_at DATETIME      NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Driver applications ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS driver_applications (
    id                   VARCHAR(36)    NOT NULL,
    user_id              VARCHAR(36)    NULL,
    status               ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    first_name           VARCHAR(100)   NOT NULL DEFAULT '',
    last_name            VARCHAR(100)   NOT NULL DEFAULT '',
    email                VARCHAR(255)   NOT NULL DEFAULT '',
    phone                VARCHAR(50)    NOT NULL DEFAULT '',
    dob                  DATE           NULL,
    address              TEXT,
    license_number       VARCHAR(100)   NOT NULL DEFAULT '',
    license_expiry       DATE           NULL,
    years_experience     TINYINT UNSIGNED NULL,
    van_make             VARCHAR(100)   NOT NULL DEFAULT '',
    van_model            VARCHAR(100)   NOT NULL DEFAULT '',
    van_year             YEAR           NULL,
    van_color            VARCHAR(50)    NOT NULL DEFAULT '',
    van_reg              VARCHAR(50)    NOT NULL DEFAULT '',
    van_type             VARCHAR(100)   NOT NULL DEFAULT '',
    insurance_expiry     DATE           NULL,
    mot_expiry           DATE           NULL,
    cargo_length         DECIMAL(8,2)   NULL,
    cargo_width          DECIMAL(8,2)   NULL,
    cargo_height         DECIMAL(8,2)   NULL,
    payload_kg           DECIMAL(10,2)  NULL,
    volume_m3            DECIMAL(10,2)  NULL,
    ext_length           DECIMAL(8,2)   NULL,
    ext_width            DECIMAL(8,2)   NULL,
    ext_height           DECIMAL(8,2)   NULL,
    tail_lift            TINYINT(1)     NOT NULL DEFAULT 0,
    availability         JSON           NULL,
    work_type            VARCHAR(100)   NOT NULL DEFAULT '',
    operating_areas      TEXT,
    notes                TEXT,
    telegram_chat_id     VARCHAR(100)   NOT NULL DEFAULT '',
    photo_front_paths    JSON           NULL,
    photo_side_paths     JSON           NULL,
    photo_interior_paths JSON           NULL,
    doc_licence_paths    JSON           NULL,
    doc_insurance_paths  JSON           NULL,
    doc_mot_paths        JSON           NULL,
    created_at           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Driver locations (real-time GPS) ────────────────────────────
CREATE TABLE IF NOT EXISTS driver_locations (
    driver_id  VARCHAR(36)    NOT NULL,
    lat        DECIMAL(10,8)  NOT NULL,
    lng        DECIMAL(11,8)  NOT NULL,
    status     ENUM('available','busy','offline') NOT NULL DEFAULT 'offline',
    updated_at DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (driver_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Quote requests ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS quotes (
    id          VARCHAR(36)   NOT NULL,
    user_id     VARCHAR(36)   NULL,
    first_name  VARCHAR(100)  NOT NULL DEFAULT '',
    last_name   VARCHAR(100)  NOT NULL DEFAULT '',
    company     VARCHAR(255)  NOT NULL DEFAULT '',
    email       VARCHAR(255)  NOT NULL DEFAULT '',
    phone       VARCHAR(50)   NOT NULL DEFAULT '',
    service     VARCHAR(100)  NOT NULL DEFAULT '',
    origin      VARCHAR(500)  NOT NULL DEFAULT '',
    destination VARCHAR(500)  NOT NULL DEFAULT '',
    weight_kg   VARCHAR(100)  NOT NULL DEFAULT '',
    volume_m3   VARCHAR(100)  NOT NULL DEFAULT '',
    notes       TEXT,
    status      ENUM('pending','quoted','accepted','rejected') NOT NULL DEFAULT 'pending',
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Load requests ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS loads (
    id                  VARCHAR(50)    NOT NULL,
    assigned_driver_id  VARCHAR(36)    NULL,
    status              ENUM('open','matched','in_transit','completed','cancelled') NOT NULL DEFAULT 'open',
    pickup_address      TEXT           NOT NULL,
    pickup_lat          DECIMAL(10,8)  NULL,
    pickup_lng          DECIMAL(11,8)  NULL,
    delivery_address    TEXT           NOT NULL,
    delivery_lat        DECIMAL(10,8)  NULL,
    delivery_lng        DECIMAL(11,8)  NULL,
    cargo_description   TEXT           NOT NULL,
    weight_kg           DECIMAL(10,2)  NULL,
    volume_m3           DECIMAL(10,2)  NULL,
    requires_tail_lift  TINYINT(1)     NOT NULL DEFAULT 0,
    scheduled_date      DATE           NULL,
    contact_name        VARCHAR(100)   NOT NULL DEFAULT '',
    contact_phone       VARCHAR(50)    NOT NULL DEFAULT '',
    notes               TEXT,
    telegram_sent_at    DATETIME       NULL,
    sms_sent_at         DATETIME       NULL,
    created_at          DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Contact submissions ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS contacts (
    id         VARCHAR(36)   NOT NULL,
    first_name VARCHAR(100)  NOT NULL DEFAULT '',
    last_name  VARCHAR(100)  NOT NULL DEFAULT '',
    email      VARCHAR(255)  NOT NULL DEFAULT '',
    phone      VARCHAR(50)   NOT NULL DEFAULT '',
    subject    VARCHAR(500)  NOT NULL DEFAULT '',
    message    TEXT          NOT NULL,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Newsletter subscribers ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id            VARCHAR(36)   NOT NULL,
    email         VARCHAR(255)  NOT NULL,
    subscribed_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── KYC / profile data ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS kyc_data (
    id         INT           NOT NULL AUTO_INCREMENT,
    user_id    VARCHAR(36)   NOT NULL,
    section    VARCHAR(50)   NOT NULL,
    data       JSON          NOT NULL,
    updated_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_section (user_id, section)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── App configuration (Telegram, Twilio) ────────────────────────
CREATE TABLE IF NOT EXISTS app_config (
    config_key   VARCHAR(100)  NOT NULL,
    config_value TEXT          NOT NULL,
    updated_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (config_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Audit log ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS audit_log (
    id          BIGINT        NOT NULL AUTO_INCREMENT,
    user_id     VARCHAR(36)   NULL,
    action      VARCHAR(100)  NOT NULL,
    entity_type VARCHAR(50)   NULL,
    entity_id   VARCHAR(100)  NULL,
    details     JSON          NULL,
    ip_address  VARCHAR(45)   NULL,
    user_agent  VARCHAR(500)  NULL,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_user_id    (user_id),
    INDEX idx_action     (action),
    INDEX idx_entity     (entity_type, entity_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
