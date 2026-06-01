<?php

declare(strict_types=1);

namespace IntegrationTests;

use Fixtures\PackageTestHelper;
use Override;

/**
 * Base Test Case for integration tests using real WordPress core.
 */
abstract class BaseTestCase extends \WP_UnitTestCase_Base
{
    use PackageTestHelper;

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function expectDeprecated()
    {
        // This method is just overriding parent's one
        // by removing the use of `PHPUnit\Util\Test::parseTestMethodAnnotations`
        // And keep the rest

        add_action('deprecated_function_run', [$this, 'deprecated_function_run'], 10, 3);
        add_action('deprecated_argument_run', [$this, 'deprecated_function_run'], 10, 3);
        add_action('deprecated_class_run', [$this, 'deprecated_function_run'], 10, 3);
        add_action('deprecated_file_included', [$this, 'deprecated_function_run'], 10, 4);
        add_action('deprecated_hook_run', [$this, 'deprecated_function_run'], 10, 4);
        add_action('doing_it_wrong_run', [$this, 'doing_it_wrong_run'], 10, 3);

        add_action('deprecated_function_trigger_error', '__return_false');
        add_action('deprecated_argument_trigger_error', '__return_false');
        add_action('deprecated_class_trigger_error', '__return_false');
        add_action('deprecated_file_trigger_error', '__return_false');
        add_action('deprecated_hook_trigger_error', '__return_false');
        add_action('doing_it_wrong_trigger_error', '__return_false');
    }
}
