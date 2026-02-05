-- V002: Create categories table
-- Date: 2026-02-05
-- Fitzgerald Key: colorHex identifica categorías por color (estándar AAC)

-- UP

CREATE TABLE categories (
    id            UUID         NOT NULL,
    name          VARCHAR(50)  NOT NULL,
    icon          VARCHAR(255) DEFAULT NULL,
    color_hex     VARCHAR(7)   NOT NULL DEFAULT '#6B7280',
    display_order INTEGER      NOT NULL DEFAULT 0,
    CONSTRAINT categories_pkey PRIMARY KEY (id)
);

CREATE UNIQUE INDEX uniq_categories_name ON categories (name);

-- DOWN
-- DROP TABLE IF EXISTS categories;
