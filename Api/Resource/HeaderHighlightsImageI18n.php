<?php

namespace HeaderHighlights\Api\Resource;

use Symfony\Component\Serializer\Annotation\Groups;
use Thelia\Api\Resource\I18n;

class HeaderHighlightsImageI18n extends I18n
{
    #[Groups([HeaderHighlightsImage::GROUP_READ])]
    public ?string $title = null;

    #[Groups([HeaderHighlightsImage::GROUP_READ])]
    public ?string $description = null;

    #[Groups([HeaderHighlightsImage::GROUP_READ])]
    public ?string $chapo = null;

    #[Groups([HeaderHighlightsImage::GROUP_READ])]
    public ?string $postscriptum = null;

    public function getChapo(): ?string
    {
        return $this->chapo;
    }

    public function setChapo(?string $chapo): HeaderHighlightsImageI18n
    {
        $this->chapo = $chapo;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): HeaderHighlightsImageI18n
    {
        $this->description = $description;
        return $this;
    }

    public function getPostscriptum(): ?string
    {
        return $this->postscriptum;
    }

    public function setPostscriptum(?string $postscriptum): HeaderHighlightsImageI18n
    {
        $this->postscriptum = $postscriptum;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): HeaderHighlightsImageI18n
    {
        $this->title = $title;
        return $this;
    }
}
