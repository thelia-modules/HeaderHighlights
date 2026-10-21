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

namespace HeaderHighlights\Tests\Integration\SharedCache;

use HeaderHighlights\Model\HeaderHighlights as HeaderHighlightsModel;
use HeaderHighlights\Model\HeaderHighlightsImage;
use HeaderHighlights\SharedCache\HeaderHighlightsSharedCache;
use Thelia\Core\SharedCache\SharedCacheContributorInterface;
use Thelia\Core\SharedCache\SharedCacheInvalidationListener;
use Thelia\Core\SharedCache\SharedCacheKeyCollector;
use Thelia\Core\SharedCache\SharedCachePurgerInterface;
use Thelia\Test\IntegrationTestCase;

/**
 * The highlights do not keep a page out of the shared cache of the public pages, and a
 * change of the highlights purges the pages that show them.
 */
final class HeaderHighlightsSharedCacheTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!interface_exists(SharedCacheContributorInterface::class)) {
            self::markTestSkipped('The core of this shop predates the shared cache of the public pages.');
        }
    }

    public function testAPageThatReadsTheHighlightsCanStillBeSharedAndIsTaggedWithThem(): void
    {
        $collector = static::getContainer()->get(SharedCacheKeyCollector::class);
        $collector->reset();

        $collector->record(HeaderHighlightsSharedCache::RESOURCE);

        self::assertTrue($collector->isComplete());
        self::assertSame([HeaderHighlightsSharedCache::KEY], $collector->keys());
    }

    public function testSavingAHighlightPurgesThePagesThatShowIt(): void
    {
        $purger = $this->recordingPurger();

        $highlight = (new HeaderHighlightsModel())->setDisplayType('desktop')->setImageBlock('1');
        $highlight->setLocale('en_US')->setTitle('Autumn sale');
        $highlight->save($this->getPropelConnection());
        static::getContainer()->get(SharedCacheInvalidationListener::class)->flush();

        self::assertSame([[HeaderHighlightsSharedCache::KEY]], $purger->calls);
    }

    public function testDeletingTheImageOfAHighlightPurgesThePagesThatShowIt(): void
    {
        $purger = $this->recordingPurger();
        $highlight = (new HeaderHighlightsModel())->setDisplayType('mobile')->setImageBlock('2');
        $highlight->save($this->getPropelConnection());
        $image = (new HeaderHighlightsImage())->setHeaderHighlightsId((int) $highlight->getId())->setFile('probe.webp');
        $image->save($this->getPropelConnection());
        static::getContainer()->get(SharedCacheInvalidationListener::class)->flush();
        $purger->calls = [];

        $image->delete($this->getPropelConnection());
        static::getContainer()->get(SharedCacheInvalidationListener::class)->flush();

        self::assertSame([[HeaderHighlightsSharedCache::KEY]], $purger->calls);
    }

    /**
     * @return SharedCachePurgerInterface&object{calls: list<list<string>>}
     */
    private function recordingPurger(): SharedCachePurgerInterface
    {
        $purger = new class implements SharedCachePurgerInterface {
            /** @var list<list<string>> */
            public array $calls = [];

            public function purge(array $keys): void
            {
                $this->calls[] = $keys;
            }
        };

        static::getContainer()->set(SharedCachePurgerInterface::class, $purger);

        return $purger;
    }
}
