---
name: postgresql
description: PostgreSQL database configuration and best practices
license: MIT
compatibility: opencode
metadata:
  type: database
  engine: postgresql
---

# SKILL: PostgreSQL

## 🛠 Tech Stack
- **Database:** PostgreSQL 16
- **ORM:** Cycle ORM (ver skill `cycle-orm`)
- **Migrations:** SQL nativas gestionadas por Cycle ORM

## ⚡ Arquitectura & Logs
1.  **Connection Pooling:** Usar PgBouncer en producción.
2.  **Indexes:** Crear índices estratégicos para consultas frecuentes.
3.  **Logs:** Configurar logs de PostgreSQL en formato JSON.

## ✅ Patrones (Snippets Reales)
### A. Configuración de Conexión segura
```yaml
# config/packages/doctrine.yaml (Symfony)
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
        options:
            # SSL/TLS para conexiones seguras
            1009: '%env(resolve:DATABASE_SSL_CA)%'
```

### B. Migraciones SQL (Cycle ORM)
```sql
-- migrations/V001__initial_schema.sql
CREATE TABLE categories (
    id UUID PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(50),
    color_hex VARCHAR(7) NOT NULL DEFAULT '#6B7280',
    display_order INT NOT NULL DEFAULT 0,
    CONSTRAINT uq_categories_name UNIQUE (name)
);

CREATE INDEX idx_categories_display_order ON categories(display_order);
```

> **Nota:** Este proyecto usa Cycle ORM, no Doctrine. Ver skill `cycle-orm` para patrones de mapeo.