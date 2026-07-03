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

namespace HeaderHighlights\Hook\Theme;

use Thelia\Api\Service\DataAccess\DataAccessService;
use Thelia\Core\Hook\Theme\ThemeHookInterface;
use Twig\Environment;

final readonly class HeaderHighlightsThemeHook implements ThemeHookInterface
{
    public function __construct(
        private Environment $twig,
        private DataAccessService $dataAccessService,
    ) {
    }

    public function supports(string $hookName): bool
    {
        return 'layout.header.bottom' === $hookName;
    }

    public function render(string $hookName, array $parameters): string
    {
        if ([] === $this->dataAccessService->resources('/api/header-highlights')) {
            return '';
        }

        return $this->twig->render('@HeaderHighlightsModule/theme-hook/header_highlights.html.twig');
    }
}
