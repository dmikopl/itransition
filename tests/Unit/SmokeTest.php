<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase
{
    public function testPhpVersionMeetsRequirement(): void
    {
        self::assertGreaterThanOrEqual(80400, \PHP_VERSION_ID);
    }
}
