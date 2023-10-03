<?php

namespace HeaderHighlights\Hook;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class FrontHook extends BaseHook
{
    public function addHeaderJs(HookRenderEvent $event)
    {
        $js = $this->addJS('header_highlights/assets/dist/js/app.js');
        $event->add($js);
    }

    public function addHeaderCss(HookRenderEvent $event)
    {
        $css = $this->addCSS('header_highlights/assets/dist/css/app.css');
        $event->add($css);
    }
}