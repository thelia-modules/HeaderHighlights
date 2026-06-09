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

namespace HeaderHighlights\Hook;

use HeaderHighlights\Form\HeaderHighlightsDesktopImageForm;
use HeaderHighlights\Form\HeaderHighlightsMobileImageForm;
use HeaderHighlights\Model\HeaderHighlightsQuery;
use HeaderHighlights\Services\ImageService;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Parser\ParserResolver;
use Thelia\Model\LangQuery;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ConfigHook extends BaseHook
{
    public function __construct(
        private readonly TheliaFormFactory $formFactory,
        private readonly ImageService $imageService,
        ?EventDispatcherInterface $dispatcher = null,
        ?ParserResolver $parserResolver = null,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    public static function getSubscribedHooks(): array
    {
        return [
            'module.configuration' => [
                ['type' => 'back', 'method' => 'onModuleConfiguration'],
            ],
            'module.config-js' => [
                ['type' => 'back', 'method' => 'onModuleConfigJs'],
            ],
        ];
    }

    public function onModuleConfiguration(HookRenderEvent $event): void
    {
        $request = $this->getRequest();
        $locale = $request?->getSession()?->get('thelia.current.lang')?->getLocale() ?? 'en_US';
        $tab = $request?->query->get('tab', 'desktop') ?? 'desktop';

        $imagesDesktop = $this->buildImagesData('desktop', $locale);
        $imagesMobile = $this->buildImagesData('mobile', $locale);

        $formDesktop = $this->formFactory->createForm(HeaderHighlightsDesktopImageForm::class);
        $formMobile = $this->formFactory->createForm(HeaderHighlightsMobileImageForm::class);

        $event->add($this->render('HeaderHighlights/module_configuration.html.twig', [
            'tab' => $tab,
            'images_desktop' => $imagesDesktop,
            'images_mobile' => $imagesMobile,
            'form_desktop' => $formDesktop->createView()->getView(),
            'form_mobile' => $formMobile->createView()->getView(),
            'image_count' => \HeaderHighlights\HeaderHighlights::IMAGE_COUNT,
        ]));
    }

    public function onModuleConfigJs(HookRenderEvent $event): void
    {
        $event->add($this->render('HeaderHighlights/module_configuration_js.html.twig', []));
    }

    private function buildImagesData(string $displayType, string $locale): array
    {
        $query = HeaderHighlightsQuery::create();

        $query->useHeaderHighlightsI18nQuery()
            ->filterByLocale($locale)
            ->endUse()
            ->withColumn('header_highlights_i18n.TITLE', 'i18n_TITLE')
            ->withColumn('header_highlights_i18n.CALL_TO_ACTION', 'i18n_CALL_TO_ACTION')
            ->withColumn('header_highlights_i18n.URL', 'i18n_URL')
            ->withColumn('header_highlights_i18n.CATCHPHRASE', 'i18n_CATCHPHRASE');

        $rows = $query
            ->filterByDisplayType($displayType)
            ->orderByImageBlock()
            ->find();

        $images = [];
        foreach ($rows as $headerHighlights) {
            $headerHighlightsImage = $headerHighlights->getHeaderHighlightsImages()->getFirst();

            [$fileUrl, $originalFileUrl] = $this->imageService->imageProcess(
                headerHighlightsImage: $headerHighlightsImage,
                useTheliaLibrary: false,
                resizeMode: 'none',
                width: null,
                height: null,
                format: null,
            );

            $block = $headerHighlights->getImageBlock();
            $images[$block] = [
                'ID' => $headerHighlights->getId(),
                'TITLE' => $headerHighlights->getVirtualColumn('i18n_TITLE') ?? '',
                'CATEGORY' => $headerHighlights->getCategoryId(),
                'CTA' => $headerHighlights->getVirtualColumn('i18n_CALL_TO_ACTION') ?? '',
                'URL' => $headerHighlights->getVirtualColumn('i18n_URL') ?? '',
                'CATCHPHRASE' => $headerHighlights->getVirtualColumn('i18n_CATCHPHRASE') ?? '',
                'IMAGE_URL' => $fileUrl ?? '',
                'ORIGINAL_IMAGE_URL' => $originalFileUrl ?? '',
                'IMAGE_BLOCK' => $block,
            ];
        }

        // Fill missing positions with empty data
        for ($position = 1; $position <= \HeaderHighlights\HeaderHighlights::IMAGE_COUNT; $position++) {
            if (!isset($images[$position])) {
                $images[$position] = [
                    'ID' => '',
                    'TITLE' => '',
                    'CATEGORY' => '',
                    'CTA' => '',
                    'URL' => '',
                    'CATCHPHRASE' => '',
                    'IMAGE_URL' => '',
                    'ORIGINAL_IMAGE_URL' => '',
                    'IMAGE_BLOCK' => $position,
                ];
            }
        }

        ksort($images);

        return $images;
    }
}
