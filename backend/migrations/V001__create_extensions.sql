-- V001: Create PostgreSQL extensions
-- Date: 2026-02-05

-- UP

-- uuid-ossp: genera UUIDs nativos en PostgreSQL (uuid_generate_v4()).
-- Actualmente los UUIDs se generan en PHP (Symfony Uid), pero se mantiene
-- por si en el futuro se necesitan defaults UUID a nivel de base de datos.
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- pg_trgm: búsqueda de texto por trigramas (fuzzy search).
-- Permite crear índices GIN para acelerar búsquedas LIKE '%texto%'.
-- Actualmente no se usa (el índice en label es btree), pero se mantiene
-- preparado para cuando se optimice la búsqueda de pictogramas.
CREATE EXTENSION IF NOT EXISTS "pg_trgm";

-- DOWN
-- DROP EXTENSION IF EXISTS "pg_trgm";
-- DROP EXTENSION IF EXISTS "uuid-ossp";
