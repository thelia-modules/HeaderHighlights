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

namespace HeaderHighlights\Twig;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Thelia\Api\Service\DataAccess\DataAccessService;

#[AsTwigComponent(name: 'HeaderHighlights', template: '@HeaderHighlightsModule/components/HeaderHighlights.html.twig')]
class HeaderHighlights
{
    #[ExposeInTemplate()]
    public array $desktops = [];

    #[ExposeInTemplate()]
    public array $mobiles = [];

    public function __construct(private DataAccessService $dataAccessService)
    {
        $this->dataAccessService = $dataAccessService;
    }

    public function getImages(): array
    {
        return $this->dataAccessService->resources('/api/header-highlights');
    }

    public function getDesktops(): array
    {
        return array_filter($this->getImages(), function ($image) {
            return $image['headerHighlights']['displayType'] == 'desktop';
        });
    }

    public function getMobiles(): array
    {
        $mobiles = array_filter($this->getImages(), function ($image) {
            return $image['headerHighlights']['displayType'] == 'mobile';
        });

        if (empty($mobiles)) {
            $mobiles = $this->getDesktops();
        }

        return $mobiles;
    }
}
