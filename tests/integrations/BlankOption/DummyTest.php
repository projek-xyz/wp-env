<?php

declare(strict_types=1);

namespace IntegrationTests\BlankOption;

use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;

/**
 * Unit tests for the blank's `blank-option.php`.
 */
#[RunClassInSeparateProcess]
class DummyTest extends TestCase
{
    #[Test]
    public function shouldBeTrue()
    {
        $this->assertTrue(true);
    }
}
