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

        // Assert: Ensure the option is not exists and returns default value
        $this->assertEquals('default_value', $option->get('test_integration_key', 'default_value'));

        // Act: Set a new value
        $option->set('test_integration_key', 'it_works');

        // Assert: Retrieve it via the class to verify the internal logic
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

        // Act: Set a initial value
        $option->set('test_integration_key', 'it_works');

        // Assert: Retrieves the initial value
        $this->assertEquals('it_works', $option->get('test_integration_key'));

        // Act: Update the value which should also reset the instance cache
        $option->set('test_integration_key', 'its_changed');

        // Assert: Retrieve the new value directly from database
        $this->assertEquals('its_changed', $option->get('test_integration_key'));
    }
}
