<?php

namespace HeaderHighlights\Controller;

use HeaderHighlights\HeaderHighlights;
use HeaderHighlights\Model\HeaderHighlights as HeaderHighlightsModel;
use HeaderHighlights\Form\HeaderHighlightsDesktopImageForm;
use HeaderHighlights\Form\HeaderHighlightsMobileImageForm;
use HeaderHighlights\Model\HeaderHighlightsImage;
use HeaderHighlights\Model\HeaderHighlightsImageQuery;
use Exception;
use HeaderHighlights\Model\HeaderHighlightsQuery;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Event\File\FileCreateOrUpdateEvent;
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

            $locale = $this->getCurrentEditionLocale();

            for ($i = 1; $i <=3; $i++) {
                $this->saveHeaderHighlight($eventDispatcher, $formData, $locale, $i,$displayType);
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
    private function saveHeaderHighlight( EventDispatcherInterface $eventDispatcher,
        $formData,
        $locale,
        $idx,
        $displayType
    ) {

        $headerHighlights = HeaderHighlightsQuery::create()
            ->filterByDisplayType($displayType)
            ->filterByImageBlock($idx)
            ->findOne();
        $headerHighlightsImage = HeaderHighlightsImageQuery::create()
            ->filterByHeaderHighlightsId($headerHighlights->getId())
            ->findOneOrCreate();

        /** @var UploadedFile $fileBeingUploaded */
        $fileBeingUploaded = $formData['image' . $idx];


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
            try {
                $eventDispatcher->dispatch(
                    $fileCreateOrUpdateEvent,
                    $eventNameImage
                );
            } catch (\Exception $e) {
                // On IMAGE_UPDATE, if image file has been deleted it will throw an error
                $eventDispatcher->dispatch(
                    $fileCreateOrUpdateEvent,
                    TheliaEvents::IMAGE_SAVE
                );
            }

            $langs = LangQuery::create()->find();

            foreach ($langs as $lang) {
                $fileCreateOrUpdateEvent->getModel()->setLocale($lang->getLocale())->setTitle('')->save();
            }
        }

        $headerHighlights = $headerHighlightsImage->getHeaderHighlights();

        $headerHighlights
            ->setImageBlock($formData['image_block' . $idx])
            ->setCategoryId($formData['category' . $idx])
            ->setLocale($locale)
            ->setCallToAction($formData['call_to_action' . $idx])
            ->setUrl($formData['url' . $idx])
            ->setTitle($formData['title' . $idx])
            ->setDisplayType($formData['display_type' . $idx])
            ->setCatchphrase($formData['catchphrase' . $idx])
            ->save();

        $headerHighlightsImage
            ->setLocale($locale)
            ->setTitle($formData['title' . $idx])
            ->save();

        $this->emptyImageCache($headerHighlightsImage->getId());
    }

    private function emptyImageCache($imageId)
    {
        $cacheDir = THELIA_WEB_DIR."legacy-image-library".DS.'headerHighlights_image_'.$imageId;

        $fs = new Filesystem();
        if (is_dir($cacheDir)){
            $fs->remove($cacheDir);
        }
    }
}
