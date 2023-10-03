<?php

namespace HeaderHighlights\Model;

use HeaderHighlights\HeaderHighlights;
use HeaderHighlights\Model\Base\HeaderHighlightsImage as BaseHeaderHighlightsImage;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Thelia\Files\FileModelInterface;
use Thelia\Files\FileModelParentInterface;
use Thelia\Model\CategoryQuery;
use Thelia\Model\LangQuery;

/**
 * Skeleton subclass for representing a row from the 'header_highlights_image' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class HeaderHighlightsImage extends BaseHeaderHighlightsImage implements FileModelInterface
{

    public function setParentId($parentId): HeaderHighlightsImage|static
    {
        return $this;
    }

    public function getParentId(): int
    {
        return $this->getId();
    }

    public function getParentFileModel(): FileModelParentInterface|static
    {
        return new static();
    }

    public function getUpdateFormId(): string
    {
        return 'header_highlights_loop';
    }

    public function getUploadDir(): string
    {
        $headerHighlights = new HeaderHighlights();

        return $headerHighlights->getUploadDir();
    }

    public function getRedirectionUrl(): string
    {
        return '/admin/module/HeaderHighlights';
    }

    public function getQueryInstance(): HeaderHighlightsImageQuery|ModelCriteria
    {
        return HeaderHighlightsImageQuery::create();
    }

    public function createEmptyImage(int $index, string $displayType): self
    {
        $locales = LangQuery::create()->filterByActive(true)->find();
        $categoryId = CategoryQuery::create()->findOne()->getId();

        $this
            ->setImageBlock($index)
            ->setCategoryId($categoryId)
            ->setCallToAction('')
            ->setUrl('')
            ->setDisplayType($displayType);

        foreach ($locales as $locale) {
            $this
                ->setLocale($locale->getLocale())
                ->setTitle('')
                ->setDescription('');
        }

        return $this;
    }
}
