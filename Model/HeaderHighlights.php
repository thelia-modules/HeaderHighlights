<?php

namespace HeaderHighlights\Model;

use HeaderHighlights\Model\Base\HeaderHighlights as BaseHeaderHighlights;
use Thelia\Model\CategoryQuery;
use Thelia\Model\LangQuery;

/**
 * Skeleton subclass for representing a row from the 'header_highlights' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class HeaderHighlights extends BaseHeaderHighlights
{
    public function createEmptyHeaderHighlights(int $index, string $displayType): self
    {
        $locales = LangQuery::create()->filterByActive(true)->find();
        $categoryId = CategoryQuery::create()->findOne()->getId();

        $this
            ->setCategoryId($categoryId)
            ->setDisplayType($displayType);

        foreach ($locales as $locale) {
            $this
                ->setLocale($locale->getLocale())
                ->setTitle('')
                ->setCallToAction('')
                ->setUrl('');
        }

        return $this;
    }
}
