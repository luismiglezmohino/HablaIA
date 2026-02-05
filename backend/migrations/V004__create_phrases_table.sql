-- V004: Create phrases table
-- Date: 2026-02-05
-- Almacena frases generadas por IA a partir de secuencias de pictogramas

-- UP

CREATE TABLE phrases (
    id            UUID                           NOT NULL,
    sequence_hash VARCHAR(64)                    NOT NULL,
    pictogram_ids JSON                           NOT NULL,
    variations    JSON                           NOT NULL,
    created_at    TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    CONSTRAINT phrases_pkey PRIMARY KEY (id)
);

CREATE UNIQUE INDEX uniq_phrases_sequence_hash ON phrases (sequence_hash);

-- DOWN
-- DROP TABLE IF EXISTS phrases;
