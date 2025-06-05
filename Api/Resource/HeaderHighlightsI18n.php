<?php

namespace HeaderHighlights\Api\Resource;

use Symfony\Component\Serializer\Annotation\Groups;
use Thelia\Api\Resource\I18n;

class HeaderHighlightsI18n extends I18n
{
    #[Groups([HeaderHighlightsImage::GROUP_READ])]
    public ?string $title = null;
    #[Groups([HeaderHighlightsImage::GROUP_READ])]
    public ?string $callToAction = null;

    #[Groups([HeaderHighlightsImage::GROUP_READ])]
    public ?string $url = null;

    #[Groups([HeaderHighlightsImage::GROUP_READ])]
    public ?string $catchphrase = null;

    public function getCallToAction(): ?string
    {
        return $this->callToAction;
    }

    public function setCallToAction(?string $callToAction): HeaderHighlightsI18n
    {
        $this->callToAction = $callToAction;
        return $this;
    }

    public function getCatchphrase(): ?string
    {
        return $this->catchphrase;
    }

    public function setCatchphrase(?string $catchphrase): HeaderHighlightsI18n
    {
        $this->catchphrase = $catchphrase;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): HeaderHighlightsI18n
    {
        $this->title = $title;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): HeaderHighlightsI18n
    {
        $this->url = $url;
        return $this;
    }
}
