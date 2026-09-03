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
        return $this->dataAccessService->resources('/api/header-highlights') ?? [];
    }

    /**
     * Keyed by the block the back office filled, 1 being the tall block of the layout, and
     * ordered by it.
     */
    public function getDesktops(): array
    {
        return $this->blocksOf('desktop');
    }

    /**
     * Keyed like getDesktops(), and a block the mobile tab leaves empty falls back to its
     * desktop counterpart.
     *
     * The two tabs are filled independently, so the collections line up on the block number and
     * never on their rank: a mobile block alone in the collection is block 2's, not block 1's.
     */
    public function getMobiles(): array
    {
        return $this->blocksOf('mobile') + $this->getDesktops();
    }

    private function blocksOf(string $displayType): array
    {
        $blocks = [];

        foreach ($this->getImages() as $image) {
            if ($displayType !== ($image['headerHighlights']['displayType'] ?? null)) {
                continue;
            }

            $blocks[(int) ($image['headerHighlights']['imageBlock'] ?? 0)] = $image;
        }

        ksort($blocks);

        return $blocks;
    }
}
