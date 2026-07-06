<?php

namespace HeaderHighlights\Api\Serializer;

use ArrayObject;
use HeaderHighlights\Api\Resource\HeaderHighlightsImage;
use HeaderHighlights\Services\ImageService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

readonly class HeaderHighlightsNormalizer implements NormalizerInterface
{
    public function __construct(
        private ImageService        $imageService,
        private RequestStack        $requestStack,
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
    )
    {
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array|ArrayObject|bool|float|int|null|string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }
        /** @var HeaderHighlightsImage $headerHighlightImage */
        $headerHighlightImage = $object;

        $useTheliaLibrary = $request->get('use_thelia_library');
        $resizeMode = $request->get('resize_mode');
        $format = $request->get('format') ?? 'webp';
        $quality = (int) ($request->get('quality') ?? 70);

        $widthDesktop = (int) ($request->get('width') ?? 1280);
        $widthMobile = (int) ($request->get('width_mobile') ?? 768);

        // Thelia's legacy image cache keys the file name on getOptionsHash(), which
        // is empty when height is null. A null height would make the desktop and
        // mobile renders collide on the same cache file. Bound by a square box
        // (height = width) so the hash differs per size while KEEP_IMAGE_RATIO stays
        // width-driven for the landscape hero (no upscale, no borders).
        $height = $request->get('height');

        [$fileUrl, $originalFileUrl] = $this->imageService->imageProcess(
            headerHighlightsImage: $headerHighlightImage->getPropelModel(),
            useTheliaLibrary: $useTheliaLibrary,
            resizeMode: $resizeMode,
            width: $widthDesktop,
            height: $height ?? $widthDesktop,
            format: $format,
            quality: $quality,
        );

        [$fileUrlMobile] = $this->imageService->imageProcess(
            headerHighlightsImage: $headerHighlightImage->getPropelModel(),
            useTheliaLibrary: $useTheliaLibrary,
            resizeMode: $resizeMode,
            width: $widthMobile,
            height: $height ?? $widthMobile,
            format: $format,
            quality: $quality,
        );

        $headerHighlightImage
            ->setFileUrl($fileUrl)
            ->setFileUrlMobile($fileUrlMobile)
            ->setOriginalFileUrl($originalFileUrl);

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        $operationName = $context['root_operation_name'] ?? (isset($context['operation']) ? $context['operation']->getName() : null);
        return $operationName === HeaderHighlightsImage::ROUTE_NAME_GET_COLLECTION;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            HeaderHighlightsImage::class => true,
        ];
    }
}
