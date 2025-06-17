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

        [$fileUrl, $originalFileUrl] = $this->imageService->imageProcess(
            headerHighlightsImage: $headerHighlightImage->getPropelModel(),
            useTheliaLibrary: $request->get('use_thelia_library'),
            resizeMode: $request->get('resize_mode'),
            width: $request->get('width'),
            height: $request->get('height'),
            format: $request->get('format'),
        );
        $headerHighlightImage
            ->setFileUrl($fileUrl)
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
