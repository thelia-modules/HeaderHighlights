<?php

namespace HeaderHighlights\Services;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Action\Image;
use Thelia\Core\Event\Image\ImageEvent;
use Thelia\Core\Event\TheliaEvents;

class ImageService
{

    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private LoggerInterface $logger
    ) {
    }


    public function imageProcess($headerHighlightsImage,$useTheliaLibrary,$resizeMode,$width,$height,$format,$quality = null): array
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

                // ImageEvent::getOptionsHash() returns '' unless width, height,
                // resize_mode AND background_color are all set. Without it every
                // size collides on the same cache file (-<source>.webp). Only used
                // to fill borders (EXACT_RATIO_WITH_BORDERS), inert for KEEP_IMAGE_RATIO.
                $event->setBackgroundColor('ffffff');

                if (null !== $format) {
                    $event->setFormat($format);
                }

                if (null !== $quality) {
                    $event->setQuality((int) $quality);
                }

                $event->setSourceFilepath($imgSourcePath)
                    ->setCacheSubdirectory('carousel');

                // Dispatch image processing event
                $this->dispatcher->dispatch($event, TheliaEvents::IMAGE_PROCESS);

                $fileUrl = $event->getFileUrl();
                $originalFileUrl = $event->getOriginalFileUrl();
            }
        } catch (\Exception $e) {
            $this->logger->error('HeaderHighlights: image processing failed', ['exception' => $e]);
        }
        return [$fileUrl, $originalFileUrl];
    }
}
