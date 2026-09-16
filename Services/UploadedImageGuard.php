<?php

declare(strict_types=1);

namespace HeaderHighlights\Services;

use HeaderHighlights\HeaderHighlights;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;

/**
 * Hands back a submitted picture under a name whose extension comes from the
 * content of the file.
 *
 * What is stored, and what the image cache later publishes, is named after
 * the extension the browser sent. Reading the format from the bytes and
 * rebuilding the name around it keeps the two consistent.
 */
final class UploadedImageGuard
{
    private const EXTENSION_BY_MIME_TYPE = [
        'image/gif' => 'gif',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function rename(UploadedFile $file): UploadedFile
    {
        $mimeType = $file->getMimeType();
        $dimensions = @getimagesize($file->getPathname());

        if (
            null === $mimeType
            || !isset(self::EXTENSION_BY_MIME_TYPE[$mimeType])
            || false === $dimensions
            || !isset($dimensions[2])
            || image_type_to_mime_type($dimensions[2]) !== $mimeType
        ) {
            throw new FormValidationException(
                Translator::getInstance()->trans(
                    'Only JPEG, PNG, GIF and WebP images can be used here.',
                    [],
                    HeaderHighlights::DOMAIN_NAME
                )
            );
        }

        $extension = self::EXTENSION_BY_MIME_TYPE[$mimeType];
        $base = pathinfo($file->getClientOriginalName(), \PATHINFO_FILENAME);
        $base = strtolower((string) preg_replace('/[^a-zA-Z0-9-_]/', '', $base));

        return new UploadedFile(
            $file->getPathname(),
            ('' === $base ? 'image' : $base).'.'.$extension,
            $mimeType,
            null,
            true
        );
    }
}
