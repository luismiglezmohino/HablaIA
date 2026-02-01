<?php

declare(strict_types=1);

namespace App\Domain\Shared\Exception;

use Exception;

/**
 * Base exception for all domain-specific exceptions.
 *
 * All domain exceptions should extend this class to allow
 * catching domain exceptions at application boundaries.
 */
abstract class DomainException extends Exception
{
}
