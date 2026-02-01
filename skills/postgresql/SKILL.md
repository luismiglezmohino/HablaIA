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
- **Database:** PostgreSQL 15+
- **Migrations:** Doctrine Migrations (Symfony) o herramientas nativas

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

### B. Migraciones con Doctrine
```php
// migrations/Version20240101000000.php
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240101000000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (
            id UUID PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            created_at TIMESTAMP NOT NULL
        )');
        
        $this->addSql('CREATE INDEX idx_users_email ON users(email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
```