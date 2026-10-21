<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeaderHighlights\SharedCache;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\SharedCache\SharedCacheInvalidator;

/**
 * Purges the pages that show the highlights when one of their rows is written or deleted.
 *
 * The configuration screen saves the models directly, so the Propel events of the four
 * tables are the only thing every write goes through. They are named by their string: the
 * classes Propel generates for them are not loaded before the kernel boots.
 */
final readonly class HeaderHighlightsPurgeListener implements EventSubscriberInterface
{
    private const TABLES = ['header_highlights', 'header_highlights_i18n', 'header_highlights_image', 'header_highlights_image_i18n'];

    public function __construct(
        private SharedCacheInvalidator $invalidator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        $events = [];

        foreach (self::TABLES as $table) {
            $events['propel.post.save.'.$table] = 'purge';
            $events['propel.post.delete.'.$table] = 'purge';
        }

        return $events;
    }

    public function purge(): void
    {
        $this->invalidator->purge([HeaderHighlightsSharedCache::KEY]);
    }
}
