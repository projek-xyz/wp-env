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
    /**
     * Verifies that options are correctly persisted and retrieved from the WordPress database.
     *
     * This is the fundamental purpose of the Option class. We must ensure that values saved
     * via our abstraction layer are actually stored in the standard WP options table
     * and can be retrieved correctly, maintaining data integrity.
     */
    #[Test]
    public function shouldPersistOptionToDatabase()
    {
        $plugin = Plugin::instance();
        new Option($plugin); // Initialize the static key

        // Act: Set a value using our abstraction
        Option::set('test_integration_key', 'it_works');

        // Assert: Retrieve it via the class to verify the internal cache/logic
        $this->assertEquals('it_works', Option::get('test_integration_key'));

        // Verify: Check the raw WordPress database to ensure it's physically stored correctly
        $raw_db_values = get_option('blank-option');
        $this->assertIsArray(
            $raw_db_values,
            'Options should be stored as an array in the database.'
        );
        $this->assertEquals(
            'it_works',
            $raw_db_values['test_integration_key'],
            'The stored value should match the value we set.'
        );
    }
}
