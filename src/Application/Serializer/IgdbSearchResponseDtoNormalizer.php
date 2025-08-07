<?php

declare(strict_types=1);

namespace App\Application\Serializer;

use App\Shared\Dto\Game\IgdbSearchResponseDto;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IgdbSearchResponseDtoNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        if (!$object instanceof IgdbSearchResponseDto) {
            throw new \InvalidArgumentException('The object must be an instance of IgdbSearchResponseDto');
        }

        return [
            'id' => $object->id,
            'name' => $object->name,
            'slug' => $object->slug,
            'summary' => $object->summary,
            'cover' => $object->cover,
            'first_release_date' => $object->first_release_date,
            'involved_companies' => $object->involved_companies,
            'websites' => $object->websites,
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof IgdbSearchResponseDto;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): IgdbSearchResponseDto
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Data is not an array');
        }

        $id = isset($data['id']) && is_int($data['id']) ? $data['id'] : 0;
        $name = isset($data['name']) && is_string($data['name']) ? $data['name'] : '';
        $slug = isset($data['slug']) && is_string($data['slug']) ? $data['slug'] : '';
        $summary = isset($data['summary']) && is_string($data['summary']) ? $data['summary'] : '';
        $cover = isset($data['cover']) && is_array($data['cover']) ? $data['cover'] : [];
        $firstReleaseDate = isset($data['first_release_date']) && is_int($data['first_release_date']) ? $data['first_release_date'] : null;
        $involvedCompanies = isset($data['involved_companies']) && is_array($data['involved_companies']) ? $data['involved_companies'] : [];
        $websites = isset($data['websites']) && is_array($data['websites']) ? $data['websites'] : [];

        return new IgdbSearchResponseDto(
            id: $id,
            name: $name,
            slug: $slug,
            involved_companies: $involvedCompanies,
            cover: $cover,
            first_release_date: $firstReleaseDate,
            summary: $summary,
            websites: $websites,
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return IgdbSearchResponseDto::class === $type || 'App\Shared\Dto\Game\IgdbSearchResponseDto' === $type;
    }

    /**
     * @return array<string, bool>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            IgdbSearchResponseDto::class => true,
        ];
    }
}
