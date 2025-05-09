<?php

namespace HeaderHighlights\Services;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Action\Image;
use Thelia\Core\Event\Image\ImageEvent;
use Thelia\Core\Event\TheliaEvents;

class ImageService
{

    private EventDispatcherInterface $dispatcher;


    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }


    public function imageProcess($headerHighlightsImage,$useTheliaLibrary,$resizeMode,$width,$height,$format): array
    {
        $fileUrl = $originalFileUrl = null;

        try {
            if (!!$headerHighlightsImage && !$useTheliaLibrary && !empty($headerHighlightsImage->getFile())) {
                $imgSourcePath = $headerHighlightsImage->getUploadDir() . DS . $headerHighlightsImage->getFile();

                $event = new ImageEvent();

                switch ($resizeMode) {
                    case 'crop':
                        $resize_mode = Image::EXACT_RATIO_WITH_CROP;
                        break;
                    case 'borders':
                        $resize_mode = Image::EXACT_RATIO_WITH_BORDERS;
                        break;
                    case 'none':
                    default:
                        $resize_mode = Image::KEEP_IMAGE_RATIO;
                }

                if (null !== $width) {
                    $event->setWidth($width);
                }

                if (null !== $height) {
                    $event->setHeight($height);
                }

                $event->setResizeMode($resize_mode);

                if (null !== $format) {
                    $event->setFormat($format);
                }

                $event->setSourceFilepath($imgSourcePath)
                    ->setCacheSubdirectory('carousel');

                // Dispatch image processing event
                $this->dispatcher->dispatch($event, TheliaEvents::IMAGE_PROCESS);

                $fileUrl = $event->getFileUrl();
                $originalFileUrl = $event->getOriginalFileUrl();
            }
        } catch (\Exception $e) {

        }
        return [$fileUrl, $originalFileUrl];
    }
}
