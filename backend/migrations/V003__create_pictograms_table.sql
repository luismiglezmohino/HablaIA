-- V003: Create pictograms table
-- Date: 2026-02-05
-- Cada pictograma pertenece a una categoría y tiene un ID ARASAAC único

-- UP

CREATE TABLE pictograms (
    id          UUID         NOT NULL,
    arasaac_id  INTEGER      NOT NULL,
    category_id UUID         NOT NULL,
    label       VARCHAR(100) NOT NULL,
    image_path  VARCHAR(255) NOT NULL,
    CONSTRAINT pictograms_pkey PRIMARY KEY (id),
    CONSTRAINT fk_pictograms_category FOREIGN KEY (category_id)
        REFERENCES categories (id) ON DELETE CASCADE
);

CREATE UNIQUE INDEX uniq_pictograms_arasaac_id ON pictograms (arasaac_id);
CREATE INDEX idx_pictograms_category ON pictograms (category_id);
CREATE INDEX idx_pictograms_label ON pictograms (label);

-- DOWN
-- DROP TABLE IF EXISTS pictograms;
