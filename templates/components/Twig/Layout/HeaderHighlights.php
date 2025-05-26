<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeaderHighlights\Twig\Layout;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use TwigEngine\Service\DataAccess\DataAccessService;

#[AsTwigComponent(name: 'HeaderHighlights', template: '@HeaderHighlightsModule/components/HeaderHighlights.html.twig')]
class HeaderHighlights
{
    public function __construct(private DataAccessService $dataAccessService)
    {
        $this->dataAccessService = $dataAccessService;
    }

    public function getImages(): array
    {
        return $this->dataAccessService->resources('/api/header-highlights');
    }
}
