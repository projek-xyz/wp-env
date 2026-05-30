<?php

declare(strict_types=1);

namespace IntegrationTests\BlankOption;

use Blank_Option\Option;
use Blank_Option\Plugin;
use PHPUnit\Framework\Attributes\Test;

/**
 * Integration tests for the Option class.
 */
class OptionTest extends TestCase
{
    #[Test]
    public function should_persist_option_to_database()
    {
        $plugin = Plugin::instance();
        new Option($plugin); // Initialize the static key

        // Act: Set a value
        Option::set('test_integration_key', 'it_works');

        // Assert: Retrieve it via the class
        $this->assertEquals('it_works', Option::get('test_integration_key'));

        // Verify: Check the raw WordPress database
        $raw_db_values = get_option('blank-option');
        $this->assertIsArray($raw_db_values);
        $this->assertEquals('it_works', $raw_db_values['test_integration_key']);
    }
}
