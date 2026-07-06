<?php

namespace HeaderHighlights\Api\Resource;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use HeaderHighlights\Model\Map\HeaderHighlightsImageTableMap;
use Propel\Runtime\Map\TableMap;
use Symfony\Component\Serializer\Annotation\Groups;
use Thelia\Api\Bridge\Propel\Attribute\Relation;
use Thelia\Api\Bridge\Propel\Filter\BooleanFilter;
use Thelia\Api\Bridge\Propel\Filter\OrderFilter;
use Thelia\Api\Bridge\Propel\Filter\SearchFilter;
use Thelia\Api\Bridge\Propel\State\PropelCollectionProvider;
use Thelia\Api\Resource\AbstractTranslatableResource;
use Thelia\Api\Resource\I18nCollection;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/header-highlights',
            name: 'api_header_highlights_get_collection',
            provider: PropelCollectionProvider::class,
        ),
    ],
    normalizationContext: ['groups' => [self::GROUP_READ]]
)]
#[ApiFilter(
    filterClass: SearchFilter::class,
    properties: [
        'id',
        'products.id',
        'title',
        'headerHighlights.displayType' => [
            'strategy' => 'exact',
            'fieldPath' => 'headerhighlightsimage_headerhighlights.displayType',
        ],
        'createdAt',
        'updatedAt',
    ]
)]
#[ApiFilter(
    filterClass: BooleanFilter::class,
    properties: [
        'visible',
    ]
)]
#[ApiFilter(
    filterClass: OrderFilter::class,
    properties: [
        'headerHighlights.imageBlock' => [
            'strategy' => 'exact',
            'fieldPath' => 'headerhighlightsimage_headerhighlights.imageBlock',
        ]
    ]
)]
class HeaderHighlightsImage extends AbstractTranslatableResource
{
    public const ROUTE_NAME_GET_COLLECTION = 'api_header_highlights_get_collection';
    public const GROUP_READ = 'header:highlights:image:read';

    #[Groups([self::GROUP_READ])]
    public ?int $id = null;

    #[Relation(targetResource: HeaderHighlights::class)]
    #[Groups([self::GROUP_READ])]
    public ?HeaderHighlights $headerHighlights = null;

    public string $file;

    #[Groups([self::GROUP_READ])]
    public bool $visible = true;

    #[Groups([self::GROUP_READ])]
    public ?int $position = null;

    public ?\DateTime $createdAt = null;

    #[Groups([self::GROUP_READ])]
    public ?\DateTime $updatedAt = null;

    #[Groups([self::GROUP_READ])]
    public ?string $fileUrl;

    #[Groups([self::GROUP_READ])]
    public ?string $fileUrlMobile = null;

    #[Groups([self::GROUP_READ])]
    public ?string $originalFileUrl;

    #[Groups([self::GROUP_READ])]
    public I18nCollection $i18ns;

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): HeaderHighlightsImage
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function setFile(string $file): HeaderHighlightsImage
    {
        $this->file = $file;
        return $this;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): HeaderHighlightsImage
    {
        $this->id = $id;
        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): HeaderHighlightsImage
    {
        $this->position = $position;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): HeaderHighlightsImage
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): HeaderHighlightsImage
    {
        $this->visible = $visible;
        return $this;
    }

    public function getHeaderHighlights(): ?HeaderHighlights
    {
        return $this->headerHighlights;
    }

    public function setHeaderHighlights(?HeaderHighlights $headerHighlights): HeaderHighlightsImage
    {
        $this->headerHighlights = $headerHighlights;
        return $this;
    }

    public function getFileUrl(): ?string
    {
        return $this->fileUrl;
    }

    public function setFileUrl(?string $fileUrl): HeaderHighlightsImage
    {
        $this->fileUrl = $fileUrl;
        return $this;
    }

    public function getFileUrlMobile(): ?string
    {
        return $this->fileUrlMobile;
    }

    public function setFileUrlMobile(?string $fileUrlMobile): HeaderHighlightsImage
    {
        $this->fileUrlMobile = $fileUrlMobile;
        return $this;
    }

    public function getOriginalFileUrl(): ?string
    {
        return $this->originalFileUrl;
    }

    public function setOriginalFileUrl(?string $originalFileUrl): HeaderHighlightsImage
    {
        $this->originalFileUrl = $originalFileUrl;
        return $this;
    }

    public static function getPropelRelatedTableMap(): ?TableMap
    {
        return new HeaderHighlightsImageTableMap();
    }

    public static function getI18nResourceClass(): string
    {
        return HeaderHighlightsImageI18n::class;
    }
}
