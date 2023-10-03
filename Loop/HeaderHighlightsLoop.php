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

namespace HeaderHighlights\Loop;

use HeaderHighlights\Model\HeaderHighlightsImageQuery;
use HeaderHighlights\Model\Map\HeaderHighlightsImageI18nTableMap;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Exception\PropelException;
use Thelia\Core\Event\Image\ImageEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Template\Element\BaseI18nLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Model\Lang;
use Thelia\Model\LangQuery;


/**
 * @method getLocale()
 * @method getLangId()
 * @method getDisplayType()
 */
class HeaderHighlightsLoop extends BaseI18nLoop implements PropelSearchLoopInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getArgDefinitions(): ArgumentCollection
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('lang_id', Lang::getDefaultLanguage()->getId()),
            Argument::createAlphaNumStringTypeArgument('display_type', null, true)
        );
    }

    /**
     * @param LoopResult $loopResult
     * @return LoopResult
     * @throws PropelException
     */
    public function parseResults(LoopResult $loopResult): LoopResult
    {
        foreach ($loopResult->getResultDataCollection() as $headerHighlights)
        {
            $loopResultRow = new LoopResultRow($headerHighlights);

            $fileUrl = $originalFileUrl = null;

            if (! empty($headerHighlights->getFile())) {
                $imgSourcePath = $headerHighlights->getUploadDir() . DS . $headerHighlights->getFile();

                $event = new ImageEvent();
                $event->setSourceFilepath($imgSourcePath)
                    ->setCacheSubdirectory('carousel');

                // Dispatch image processing event
                $this->dispatcher->dispatch($event, TheliaEvents::IMAGE_PROCESS);

                $fileUrl = $event->getFileUrl();
                $originalFileUrl = $event->getOriginalFileUrl();
            }

            $loopResultRow
                ->set('ID', $headerHighlights->getId())
                ->set('TITLE', $headerHighlights->getVirtualColumn("i18n_TITLE"))
                ->set('CATEGORY', $headerHighlights->getCategoryId())
                ->set('CTA', $headerHighlights->getVirtualColumn("i18n_CALL_TO_ACTION"))
                ->set('CATCHPHRASE', $headerHighlights->getVirtualColumn("i18n_DESCRIPTION"))
                ->set('URL', $headerHighlights->getVirtualColumn("i18n_URL"))
                ->set('IMAGE_URL', $fileUrl)
                ->set('ORIGINAL_IMAGE_URL', $originalFileUrl)
                ->set('IMAGE_BLOCK', $headerHighlights->getImageBlock());

            $this->addOutputFields($loopResultRow, $headerHighlights);

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }


    /**
     * this method returns a Propel ModelCriteria.
     *
     * @return ModelCriteria
     */
    public function buildModelCriteria(): ModelCriteria
    {
        $query = HeaderHighlightsImageQuery::create();

        $this->configureI18nProcessing(
            $query,
            [
                'TITLE',
                'CHAPO',
                'DESCRIPTION',
                'POSTSCRIPTUM',
                'CALL_TO_ACTION',
                'URL'
            ]
        );

        $query
            ->filterByDisplayType($this->getDisplayType())
            ->orderByImageBlock();

        return $query;
    }
}
