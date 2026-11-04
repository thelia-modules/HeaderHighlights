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

namespace HeaderHighlights\Tests\Integration;

use HeaderHighlights\HeaderHighlights;
use PHPUnit\Framework\Attributes\Test;
use Thelia\Core\Translation\Translator;
use Thelia\Model\ModuleQuery;
use Thelia\Test\IntegrationTestCase;

/**
 * The PHP strings of the module are read from the catalogue the core registers for it.
 */
final class TranslationDomainTest extends IntegrationTestCase
{
    #[Test]
    public function theTranslationDomainIsTheOneTheCoreRegisters(): void
    {
        $module = ModuleQuery::create()->findOneByCode('HeaderHighlights');

        self::assertNotNull($module);
        self::assertSame($module->getTranslationDomain(), HeaderHighlights::TRANSLATION_DOMAIN);
    }

    #[Test]
    public function theToolsMenuEntryIsTranslatedInFrench(): void
    {
        self::assertSame(
            'Modifier vos encarts d\'accueil',
            Translator::getInstance()->trans('Edit your Header', [], HeaderHighlights::TRANSLATION_DOMAIN, 'fr_FR'),
        );
    }
}
