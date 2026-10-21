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

use Thelia\Core\SharedCache\SharedCacheContributorInterface;

/**
 * The highlights are the same for every visitor: a page that reads them through
 * `/api/header-highlights` may still be kept by the shared cache of the public pages,
 * tagged with the key a change of the highlights purges.
 */
final readonly class HeaderHighlightsSharedCache implements SharedCacheContributorInterface
{
    public const KEY = 'header_highlights';

    public const RESOURCE = '/api/header-highlights';

    public function readableResources(): array
    {
        return [self::RESOURCE => [self::KEY]];
    }

    public function shareableViews(): array
    {
        return [];
    }
}
