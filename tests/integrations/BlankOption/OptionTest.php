<?php

declare(strict_types=1);

namespace IntegrationTests\BlankOption;

use Blank_Option\Option;
use Blank_Option\Plugin;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Integration tests for the Option class.
 */
#[Group('option')]
class OptionTest extends TestCase
{
    private function option(): Option
    {
        return new Option(Plugin::instance());
    }

    /**
     * Verifies that options are correctly persisted and retrieved from the WordPress database.
     */
    #[Test]
    #[Group('database')]
    public function shouldPersistOptionToDatabase()
    {
        $option = $this->option(); // Initialize the static key

        $this->assertTrue($option->is_empty());

        // Assert: Retrieves default value if option not exists
        $this->assertEquals('default_value', $option->get('test_integration_key', 'default_value'));

        // Act: Set a value using our abstraction
        $option->set('test_integration_key', 'it_works');

        // Assert: Retrieve it via the class to verify the internal cache/logic
        $this->assertEquals('it_works', $option->get('test_integration_key'));
    }

    /**
     * Verifies that options are correctly persisted and retrieved from the cache buffer.
     */
    #[Test]
    #[Group('database')]
    public function shouldCacheTheValueToOptionInstance()
    {
        $option = $this->option(); // Initialize the static key

        // Assert: Retrieves value from previous set
        $this->assertEquals('it_works', $option->get('test_integration_key', 'default_value'));

        // Act: Set a value using our abstraction
        $option->set('test_integration_key', 'its_changed');

        // Assert: Retrieve it via the class to verify the internal cache/logic
        $this->assertEquals('its_changed', $option->get('test_integration_key'));
    }
}
