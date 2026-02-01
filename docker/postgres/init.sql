-- =============================================================================
-- HablaIA - Inicialización PostgreSQL
-- =============================================================================
-- Este script se ejecuta automáticamente al crear el contenedor

-- -----------------------------------------------------------------------------
-- Extensiones
-- -----------------------------------------------------------------------------
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";  -- Para búsqueda de texto

-- -----------------------------------------------------------------------------
-- Mensaje de confirmación
-- -----------------------------------------------------------------------------
DO $$
BEGIN
    RAISE NOTICE 'HablaIA: Base de datos inicializada correctamente';
END $$;
