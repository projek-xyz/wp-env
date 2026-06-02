<?php

declare(strict_types=1);

namespace IntegrationTests\BlankOption;

use Blank_Option\Plugin;
use Blank_Option\Updater;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Integration tests for the Updater class.
 */
class UpdaterTest extends TestCase
{
    public function mockUpdateResponse(array $response): array
    {
        $body = json_decode($response['body'], true);

        // Mock release data with a newer version
        $body[static::PACKAGE_NAME]['version'] = '0.0.2';

        $response['body'] = json_encode($body);

        return $response;
    }

    /**
     * Verifies that the update logic correctly identifies if an update is available.
     *
     * This scenario is necessary to ensure the blank option update mechanism
     * properly interfaces with WordPress's internal update transient system.
     */
    #[Test]
    #[Group('update')]
    public function shouldHandleUpdateChecksCorrectly()
    {
        \add_filter('http_response', [$this, 'mockUpdateResponse']);

        $updater = new Updater(Plugin::instance());
        $basename = \plugin_basename(static::package('entrypoint'));

        // Act: Call get_updates
        $update = $updater->check_updates(false, ['Version' => '0.0.1'], $basename);

        $this->assertNotFalse($update);

        \remove_filter('http_response', [$this, 'mockUpdateResponse']);
    }
}
