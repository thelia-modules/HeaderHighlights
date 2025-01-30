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

use HeaderHighlights\Model\HeaderHighlights as HeaderHighlightsModel;
use HeaderHighlights\Model\HeaderHighlightsQuery;
use HeaderHighlights\Model\Map\HeaderHighlightsImageTableMap;
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
use Thelia\Type\EnumType;
use Thelia\Type\TypeCollection;


/**
 * @method getLocale()
 * @method getLangId()
 * @method getDisplayType()
 * @method getUseTheliaLibrary()
 * @method getWidth()
 * @method getHeight()
 * @method getResizeMode()
 * @method getFormat()
 */
class HeaderHighlightsLoop extends BaseI18nLoop implements PropelSearchLoopInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getArgDefinitions(): ArgumentCollection
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('width'),
            Argument::createIntTypeArgument('height'),
            Argument::createIntTypeArgument('lang_id', Lang::getDefaultLanguage()->getId()),
            Argument::createAlphaNumStringTypeArgument('display_type', null, true),
            Argument::createBooleanTypeArgument('use_thelia_library', false),
            new Argument(
                'resize_mode',
                new TypeCollection(
                    new EnumType(['crop', 'borders', 'none'])
                ),
                'none'
            ),
            Argument::createAlphaNumStringTypeArgument('format')
        );
    }

    /**
     * @param LoopResult $loopResult
     * @return LoopResult
     * @throws PropelException
     */
    public function parseResults(LoopResult $loopResult): LoopResult
    {
        /** @var HeaderHighlightsModel $headerHighlights */
        foreach ($loopResult->getResultDataCollection() as $headerHighlights)
        {
            $loopResultRow = new LoopResultRow($headerHighlights);

            $fileUrl = $originalFileUrl = null;

            $headerHighlightsImage = $headerHighlights->getHeaderHighlightsImages()->getFirst();

            try {
                if (!!$headerHighlightsImage && !$this->getUseTheliaLibrary() && !empty($headerHighlightsImage->getFile())) {
                    $imgSourcePath = $headerHighlightsImage->getUploadDir() . DS . $headerHighlightsImage->getFile();

                    $event = new ImageEvent();

                    switch ($this->getResizeMode()) {
                        case 'crop':
                            $resize_mode = \Thelia\Action\Image::EXACT_RATIO_WITH_CROP;
                            break;
                        case 'borders':
                            $resize_mode = \Thelia\Action\Image::EXACT_RATIO_WITH_BORDERS;
                            break;
                        case 'none':
                        default:
                            $resize_mode = \Thelia\Action\Image::KEEP_IMAGE_RATIO;
                    }

                    $width = $this->getWidth();
                    $height = $this->getHeight();
                    $format = $this->getFormat();

                    if (null !== $width) {
                        $event->setWidth($width);
                    }

                    if (null !== $height) {
                        $event->setHeight($height);
                    }

                    $event->setResizeMode($resize_mode);

                    if (null !== $format) {
                        $event->setFormat($format);
                    }

                    $event->setSourceFilepath($imgSourcePath)
                        ->setCacheSubdirectory('carousel');

                    // Dispatch image processing event
                    $this->dispatcher->dispatch($event, TheliaEvents::IMAGE_PROCESS);

                    $fileUrl = $event->getFileUrl();
                    $originalFileUrl = $event->getOriginalFileUrl();
                }

            } catch (\Exception $e) {

            }

            $loopResultRow
                ->set('ID', $headerHighlights->getId())
                ->set('IMAGE_ID', $headerHighlightsImage?->getId())
                ->set('UPDATE_AT', $headerHighlightsImage?->getUpdatedAt()?->format('Y-m-d'))
                ->set('TITLE', $headerHighlights->getVirtualColumn("i18n_TITLE"))
                ->set('CATEGORY', $headerHighlights->getCategoryId())
                ->set('CTA', $headerHighlights->getVirtualColumn("i18n_CALL_TO_ACTION"))
                ->set('CATCHPHRASE', $headerHighlights->getVirtualColumn("i18n_CATCHPHRASE"))
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
     * @throws PropelException
     */
    public function buildModelCriteria(): ModelCriteria
    {
        $query = HeaderHighlightsQuery::create();

        $this->configureI18nProcessing(
            $query,
            [
                'TITLE',
                'CALL_TO_ACTION',
                'URL',
                "CATCHPHRASE"
            ]
        );

        $query
            ->filterByDisplayType($this->getDisplayType())
            ->orderByImageBlock();

        return $query;
    }
}
