<?php

namespace HeaderHighlights\Api\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\OpenApi;
use HeaderHighlights\Api\Resource\HeaderHighlightsImage;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: 'api_platform.openapi.factory')]
readonly class HeaderHighlightsOpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private OpenApiFactoryInterface $decorated) {}

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        foreach ($openApi->getPaths()->getPaths() as $path => $pathItem) {
            if ($pathItem->getGet()?->getOperationId() === HeaderHighlightsImage::ROUTE_NAME_GET_COLLECTION) {
                $operation = $pathItem->getGet();

                if ($operation) {
                    $parameters = $operation->getParameters();

                    $newParameters = [
                        new Parameter(
                            name: 'width',
                            in: 'query',
                            required: false,
                            schema: ['type' => 'integer']
                        ),
                        new Parameter(
                            name: 'height',
                            in: 'query',
                            required: false,
                            schema: ['type' => 'integer']
                        ),
                        new Parameter(
                            name: 'use_thelia_library',
                            in: 'query',
                            required: false,
                            schema: ['type' => 'boolean']
                        ),
                        new Parameter(
                            name: 'resize_mode',
                            in: 'query',
                            required: false,
                            schema: [
                                'type' => 'string',
                                'enum' => ['crop', 'borders', 'none'],
                                'default' => 'none'
                            ]
                        ),
                        new Parameter(
                            name: 'format',
                            in: 'query',
                            description: 'Format d\'image',
                            required: false,
                            schema: ['type' => 'string']
                        ),
                    ];

                    $updatedOperation = $operation->withParameters(array_merge($parameters, $newParameters));
                    $openApi->getPaths()->addPath(
                        $path,
                        $pathItem->withGet($updatedOperation)
                    );
                }
            }
        }

        return $openApi;
    }
}
