<?php

namespace HeaderHighlights;

use HeaderHighlights\Model\HeaderHighlights as HeaderHighlightsModel;
use HeaderHighlights\Model\HeaderHighlightsImage;
use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\Finder\Finder;
use Thelia\Core\Template\TemplateDefinition;
use Thelia\Install\Database;
use Thelia\Model\Base\CategoryQuery;
use Thelia\Model\ConfigQuery;
use Thelia\Model\LangQuery;
use Thelia\Module\BaseModule;

class HeaderHighlights extends BaseModule
{
    /** @var string */
    public const DOMAIN_NAME = 'headerHighlights';

    const IMAGE_COUNT = 3;

    /**
     * Defines how services are loaded in your modules
     *
     * @param ServicesConfigurator $servicesConfigurator
     */
    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([THELIA_MODULE_DIR . ucfirst(self::getModuleCode()). "/I18n/*"])
            ->autowire(true)
            ->autoconfigure(true);
    }

    /**
     * Execute sql files in Config/update/ folder named with module version (ex: 1.0.1.sql).
     *
     * @param $currentVersion
     * @param $newVersion
     * @param ConnectionInterface|null $con
     */
    public function update($currentVersion, $newVersion, ConnectionInterface $con = null): void
    {
        if ($newVersion === '1.0.1') {
            self::setConfigValue('is_initialized', 1);
        }

        $finder = Finder::create()
            ->name('*.sql')
            ->depth(0)
            ->sortByName()
            ->in(__DIR__.DS.'Config'.DS.'update');

        $database = new Database($con);

        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            if (version_compare($currentVersion, $file->getBasename('.sql'), '<')) {
                $database->insertSql(null, [$file->getPathname()]);
            }
        }
    }

    public function postActivation(ConnectionInterface $con = null): void
    {
        if (! self::getConfigValue('is_initialized')) {
            $database = new Database($con);
            $database->insertSql(null, [__DIR__ . "/Config/TheliaMain.sql"]);
            self::setConfigValue('is_initialized', 1);


            foreach (['mobile', 'desktop'] as $displayType) {
                for ($idx = 1; $idx <= self::IMAGE_COUNT; $idx++) {
                    $emptyHeaderHighLight = (new HeaderHighlightsModel())->createEmptyHeaderHighlights($idx, $displayType);
                    $emptyHeaderHighLight->save();
                    (new HeaderHighlightsImage())->createEmptyImage($emptyHeaderHighLight->getId())->save();
                }
            }
        }
    }

    public function getUploadDir(): string
    {
        $uploadDir = ConfigQuery::read('images_library_path');

        if ($uploadDir === null) {
            $uploadDir = THELIA_LOCAL_DIR.'media'.DS.'images';
        } else {
            $uploadDir = THELIA_ROOT.$uploadDir;
        }

        return $uploadDir.DS.self::DOMAIN_NAME;
    }

    public function getHooks()
    {
        return [
            [
                'type' => TemplateDefinition::FRONT_OFFICE,
                'code' => 'header.js',
                'title' => [
                    'en_US' => 'header js',
                    'fr_FR' => 'Js pour header',
                ],
                'block' => false,
                'active' => true,
            ],
            [
                'type' => TemplateDefinition::FRONT_OFFICE,
                'code' => 'header.css',
                'title' => [
                    'en_US' => 'header css',
                    'fr_FR' => 'css pour l\'header',
                ],
                'block' => false,
                'active' => true,
            ],
            [
                'type' => TemplateDefinition::FRONT_OFFICE,
                'code' => 'header.html',
                'title' => [
                    'en_US' => 'html header',
                    'fr_FR' => 'header html',
                ],
                'block' => false,
                'active' => true,
            ],
        ];
    }

}
