<?php

namespace HeaderHighlights\Api\Resource;

use ApiPlatform\Metadata\ApiResource;
use HeaderHighlights\Model\Map\HeaderHighlightsTableMap;
use Propel\Runtime\Map\TableMap;
use Symfony\Component\Serializer\Annotation\Groups;
use Thelia\Api\Bridge\Propel\Attribute\Relation;
use Thelia\Api\Resource\AbstractTranslatableResource;
use Thelia\Api\Resource\Category;
use Thelia\Api\Resource\I18nCollection;

class HeaderHighlights extends AbstractTranslatableResource
{
    #[Groups([HeaderHighlightsImage::GROUP_READ])]
    public ?int $id = null;

    #[Groups([HeaderHighlightsImage::GROUP_READ])]
    #[Relation(targetResource: Category::class)]
    public ?Category $category = null;
    #[Groups([HeaderHighlightsImage::GROUP_READ])]
    public ?string $imageBlock = null;

    #[Groups([HeaderHighlightsImage::GROUP_READ])]
    public ?string $displayType = null;

    public ?\DateTime $createdAt = null;

    public ?\DateTime $updatedAt = null;

    #[Groups([HeaderHighlightsImage::GROUP_READ])]
    public I18nCollection $i18ns;

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): HeaderHighlights
    {
        $this->category = $category;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): HeaderHighlights
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getDisplayType(): ?string
    {
        return $this->displayType;
    }

    public function setDisplayType(?string $displayType): HeaderHighlights
    {
        $this->displayType = $displayType;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): HeaderHighlights
    {
        $this->id = $id;
        return $this;
    }

    public function getImageBlock(): ?string
    {
        return $this->imageBlock;
    }

    public function setImageBlock(?string $imageBlock): HeaderHighlights
    {
        $this->imageBlock = $imageBlock;
        return $this;
    }


    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): HeaderHighlights
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public static function getPropelRelatedTableMap(): ?TableMap
    {
        return new HeaderHighlightsTableMap();
    }

    public static function getI18nResourceClass(): string
    {
        return HeaderHighlightsI18n::class;
    }
}
