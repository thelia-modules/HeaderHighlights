<?php

namespace HeaderHighlights\Controller;

use HeaderHighlights\HeaderHighlights;
use HeaderHighlights\Form\HeaderHighlightsDesktopImageForm;
use HeaderHighlights\Form\HeaderHighlightsMobileImageForm;
use HeaderHighlights\Model\HeaderHighlightsImage;
use HeaderHighlights\Model\HeaderHighlightsImageQuery;
use Exception;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Event\File\FileCreateOrUpdateEvent;
use Thelia\Core\Event\File\FileDeleteEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Template\ParserContext;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\LangQuery;

#[Route('/admin/module/HeaderHighlights', name: 'headerHighlights_config_')]
class ConfigurationController extends BaseAdminController
{
    #[Route('/update/{displayType}', name: 'update', methods: 'POST')]
    public function updateImage(
        EventDispatcherInterface $eventDispatcher,
        ParserContext $parserContext,
        RequestStack $requestStack,
        TheliaFormFactory $formFactory,
        $displayType
    ): RedirectResponse|Response|null {
        $clazz = match ($displayType) {
            'mobile' => HeaderHighlightsMobileImageForm::class,
            'desktop' => HeaderHighlightsDesktopImageForm::class,
            default => throw new \InvalidArgumentException('Unknown form type : ' . $displayType),
        };

        $form = $formFactory->createForm($clazz);

        try {
            $formData = $this->validateForm($form)->getData();

            $images = HeaderHighlightsImageQuery::create()->filterByDisplayType($displayType)->find();

            $locale = $this->getCurrentEditionLocale();

            /** @var HeaderHighlightsImage $carousel */
            foreach ($images as $image) {
                $this->headerHighlights($eventDispatcher, $requestStack, $image, $locale, $formData);
            }

            // If some images are missing, recreate them
            for ($idx = 1; $idx <= HeaderHighlights::IMAGE_COUNT; $idx++) {
                if (0 === HeaderHighlightsImageQuery::create()
                        ->filterByDisplayType($displayType)
                        ->filterByImageBlock($idx)
                        ->count()
                ) {
                    (new HeaderHighlightsImage())->createEmptyImage($idx, $displayType)->save();
                }
            }

            return $this->generateSuccessRedirect($form);
        } catch (FormValidationException $e) {
            $error_message = $this->createStandardFormValidationErrorMessage($e);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }

        $form->setErrorMessage($error_message);

        $parserContext
            ->addForm($form)
            ->setGeneralError($error_message);

        return $this->generateErrorRedirect($form);
    }

    /**
     * @throws PropelException
     */
    private function headerHighlights(
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack,
        HeaderHighlightsImage $headerHighlightsImage,
        $locale,
        $formData
    )
    {
        $id = $headerHighlightsImage->getId();

        /** @var UploadedFile $fileBeingUploaded */
        $fileBeingUploaded = $formData['image' . $id];

        if (null !== $fileBeingUploaded) {
            $fileCreateOrUpdateEvent = new FileCreateOrUpdateEvent(1);
            $fileCreateOrUpdateEvent->setModel($headerHighlightsImage);
            $fileCreateOrUpdateEvent->setUploadedFile($fileBeingUploaded);


            if (empty($headerHighlightsImage->getFile())){
                $eventNameImage = TheliaEvents::IMAGE_SAVE;
            }

            if (!empty($headerHighlightsImage->getFile())){
                $fileCreateOrUpdateEvent->setOldModel($headerHighlightsImage);
                $eventNameImage = TheliaEvents::IMAGE_UPDATE;
            }
            $eventDispatcher->dispatch(
                $fileCreateOrUpdateEvent,
                $eventNameImage
            );

            $langs = LangQuery::create()->find();

            foreach ($langs as $lang) {
                $fileCreateOrUpdateEvent->getModel()->setLocale($lang->getLocale())->setTitle('')->save();
            }
        }

        $headerHighlightsImage
            ->setImageBlock($formData['image_block' . $id])
            ->setCategoryId($formData['category' . $id])
            ->setLocale($locale)
            ->setCallToAction($formData['call_to_action' . $id])
            ->setUrl($formData['url' . $id])
            ->setTitle($formData['title' . $id])
            ->setDescription($formData['catchphrase' . $id])
            ->setDisplayType($formData['display_type' . $id])
            ->save();
    }
}
