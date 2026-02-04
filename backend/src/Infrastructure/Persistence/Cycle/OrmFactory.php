<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cycle;

use App\Infrastructure\Persistence\Exception\InvalidOrmConfigurationException;
use Cycle\Annotated\Entities;
use Cycle\Annotated\Locator\TokenizerEntityLocator;
use Cycle\Database\DatabaseManager;
use Cycle\ORM\Factory;
use Cycle\ORM\ORM;
use Cycle\ORM\Schema as OrmSchema;
use Cycle\ORM\SchemaInterface;
use Cycle\Schema;
use Spiral\Attributes\AttributeReader;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;

/**
 * Factory for creating Cycle ORM instance with entity schema.
 *
 * Cycle ORM requires a compiled schema to work. This factory:
 * 1. Scans entity directory for PHP classes with Cycle attributes
 * 2. Compiles the schema from those entities
 * 3. Creates the ORM instance with the compiled schema
 *
 * @see https://cycle-orm.dev/docs/advanced-schema-builder
 */
final class OrmFactory
{
    /**
     * Creates a fully configured Cycle ORM instance.
     *
     * @param DatabaseManager $dbal       The database manager with connection config
     * @param string          $entityPath Absolute path to the Entity directory
     *
     * @return ORM Configured ORM instance ready for use
     */
    public static function create(DatabaseManager $dbal, string $entityPath): ORM
    {
        if (empty($entityPath)) {
            throw InvalidOrmConfigurationException::emptyEntityPath();
        }

        if (str_contains($entityPath, '..')) {
            throw InvalidOrmConfigurationException::pathTraversalDetected();
        }

        if (!is_dir($entityPath)) {
            throw InvalidOrmConfigurationException::directoryNotFound($entityPath);
        }

        $realPath = realpath($entityPath);
        if ($realPath === false) {
            throw InvalidOrmConfigurationException::pathCannotBeResolved($entityPath);
        }

        $schema = self::buildSchema($dbal, $realPath);

        return new ORM(
            factory: new Factory($dbal),
            schema: $schema
        );
    }

    /**
     * Builds the ORM schema by scanning entities and compiling their definitions.
     *
     * The schema compilation pipeline:
     * - ResetTables: Clears existing table definitions
     * - Configurator: Reads Cycle attributes from entity classes
     * - ValidateEntities: Ensures entities are properly defined
     * - RenderTables: Generates table structures from entities
     * - RenderRelations: Sets up relationships (BelongsTo, HasMany, etc.)
     * - RenderModifiers: Applies column modifiers
     * - ForeignKeys: Creates FK constraints
     * - GenerateTypecast: Sets up type casting for columns
     *
     * @param DatabaseManager $dbal       Database manager for schema registry
     * @param string          $entityPath Path to scan for entity classes
     *
     * @return SchemaInterface Compiled schema ready for ORM
     */
    private static function buildSchema(DatabaseManager $dbal, string $entityPath): SchemaInterface
    {
        // Find all PHP files in the entity directory
        $finder = (new Finder())
            ->files()
            ->in($entityPath)
            ->name('*.php');

        // Tokenizer locates classes in found files
        $classLocator = new ClassLocator($finder);

        // EntityLocator wraps ClassLocator for Cycle ORM
        $entityLocator = new TokenizerEntityLocator($classLocator);

        // AttributeReader parses PHP 8 attributes from classes
        $reader = new AttributeReader();

        // Compile schema through the generator pipeline
        $schemaArray = (new Schema\Compiler())->compile(
            new Schema\Registry($dbal),
            [
                new Schema\Generator\ResetTables(),             // Clear existing
                new Entities($entityLocator, $reader),          // Read attributes
                new Schema\Generator\ValidateEntities(),        // Validate
                new Schema\Generator\RenderTables(),            // Generate tables
                new Schema\Generator\RenderRelations(),         // Setup relations
                new Schema\Generator\RenderModifiers(),         // Apply modifiers
                new Schema\Generator\ForeignKeys(),             // Create FKs
                new Schema\Generator\GenerateTypecast(),        // Type casting
            ]
        );

        return new OrmSchema($schemaArray);
    }
}
