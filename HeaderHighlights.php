<?php

namespace HeaderHighlights;

use HeaderHighlights\Model\HeaderHighlights as HeaderHighlightsModel;
use HeaderHighlights\Model\HeaderHighlightsImage;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Propel;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\Finder\Finder;
use Thelia\Core\Template\TemplateDefinition;
use Thelia\Core\Install\Database;
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
            ->exclude([__DIR__.'/I18n/*', __DIR__.'/Config/**/*.php', __DIR__.'/Tests/*', __DIR__.'/HeaderHighlights.php'])
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
    public function update($currentVersion, $newVersion, ?ConnectionInterface $con = null): void
    {
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

    public function postActivation(?ConnectionInterface $con = null): void
    {
        // The install script starts with DROP TABLE, so it only runs when the tables are missing.
        // header_highlights_image is the probe: Config/update/1.1.0.sql creates header_highlights
        // on its own, even for a module that was never activated.
        // The database is asked rather than an "is_initialized" flag in the module configuration:
        // a shop whose tables were created another way, or whose configuration was lost, would
        // otherwise have every highlight wiped by a mere reactivation.
        $con ??= Propel::getWriteConnection('TheliaMain');

        if (!$this->hasTable($con, 'header_highlights_image')) {
            (new Database($con))->insertSql(null, [__DIR__.'/Config/TheliaMain.sql']);

            /** @todo => in SQL file
            foreach (['mobile', 'desktop'] as $displayType) {
                
                for ($idx = 1; $idx <= self::IMAGE_COUNT; $idx++) {
                    $emptyHeaderHighLight = (new HeaderHighlightsModel())->createEmptyHeaderHighlights($idx, $displayType);
                    $emptyHeaderHighLight->save();
                    (new HeaderHighlightsImage())->createEmptyImage($emptyHeaderHighLight->getId())->save();
                }
                
            }
            **/
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

    public function getHooks(): array
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

    private function hasTable(ConnectionInterface $con, string $table): bool
    {
        $statement = $con->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
        $statement->execute(['table' => $table]);

        return (int) $statement->fetchColumn() > 0;
    }
}
