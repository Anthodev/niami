<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Serializer;

use App\Application\Serializer\IgdbSearchResponseDtoArrayNormalizer;
use App\Shared\Dto\Game\IgdbSearchResponseDto;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

beforeEach(function () {
    $this->faker = Factory::create();
    $this->normalizer = $this->createMock(NormalizerInterface::class);
    $this->denormalizer = $this->createMock(DenormalizerInterface::class);

    $this->serializer = new IgdbSearchResponseDtoArrayNormalizer();
    $this->serializer->setNormalizer($this->normalizer);
    $this->serializer->setDenormalizer($this->denormalizer);
});

describe('normalize', function () {
    it('normalizes array of IgdbSearchResponseDto objects successfully', function () {
        // Given
        $dto1 = createIgdbSearchResponseDto($this->faker);
        $dto2 = createIgdbSearchResponseDto($this->faker);
        $data = [$dto1, $dto2];

        $normalizedDto1 = ['name' => $dto1->name, 'slug' => $dto1->slug];
        $normalizedDto2 = ['name' => $dto2->name, 'slug' => $dto2->slug];

        $this->normalizer
            ->expects($this->exactly(2))
            ->method('normalize')
            ->willReturnCallback(function ($item) use ($dto1, $dto2, $normalizedDto1, $normalizedDto2) {
                if ($item === $dto1) {
                    return $normalizedDto1;
                }
                if ($item === $dto2) {
                    return $normalizedDto2;
                }
                return [];
            });

        // When
        $result = $this->serializer->normalize($data);

        // Then
        expect($result)
            ->toBeArray()
            ->toHaveCount(2)
            ->and($result[0])->toBe($normalizedDto1)
            ->and($result[1])->toBe($normalizedDto2);
    });

    it('normalizes empty array successfully', function () {
        // Given
        $data = [];

        // When
        $result = $this->serializer->normalize($data);

        // Then
        expect($result)
            ->toBeArray()
            ->toBeEmpty();

        $this->normalizer->expects($this->never())->method('normalize');
    });

    it('skips non-IgdbSearchResponseDto items in array', function () {
        // Given
        $dto = createIgdbSearchResponseDto($this->faker);
        $data = [$dto, 'invalid_item', null];

        $normalizedDto = ['name' => $dto->name, 'slug' => $dto->slug];

        $this->normalizer
            ->expects($this->once())
            ->method('normalize')
            ->with($this->identicalTo($dto))
            ->willReturn($normalizedDto);

        // When
        $result = $this->serializer->normalize($data);

        // Then
        expect($result)
            ->toBeArray()
            ->toHaveCount(1)
            ->and($result[0])->toBe($normalizedDto);
    });

    it('throws InvalidArgumentException when data is not an array', function () {
        // Given
        $data = 'not_an_array';

        // When & Then
        expect(fn() => $this->serializer->normalize($data))
            ->toThrow(\InvalidArgumentException::class, 'The object must be an array');
    });
});

describe('denormalize', function () {
    it('denormalizes array data to IgdbSearchResponseDto objects successfully', function () {
        // Given
        $data = [
            ['name' => 'Game 1', 'slug' => 'game-1'],
            ['name' => 'Game 2', 'slug' => 'game-2'],
        ];

        $dto1 = createIgdbSearchResponseDto($this->faker);
        $dto2 = createIgdbSearchResponseDto($this->faker);

        $callCount = 0;
        $this->denormalizer
            ->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnCallback(function ($item, $type) use ($data, $dto1, $dto2, &$callCount) {
                expect($type)->toBe(IgdbSearchResponseDto::class);

                if ($callCount === 0) {
                    expect($item)->toBe($data[0]);
                    $callCount++;
                    return $dto1;
                }

                expect($item)->toBe($data[1]);
                return $dto2;
            });

        // When
        $result = $this->serializer->denormalize($data, 'array');

        // Then
        expect($result)
            ->toBeArray()
            ->toHaveCount(2)
            ->and($result[0])->toBe($dto1)
            ->and($result[1])->toBe($dto2);
    });

    it('returns empty array when data is not an array', function () {
        // Given
        $data = 'not_an_array';

        // When
        $result = $this->serializer->denormalize($data, 'array');

        // Then
        expect($result)
            ->toBeArray()
            ->toBeEmpty();

        $this->denormalizer->expects($this->never())->method('denormalize');
    });

    it('skips non-array items in input array', function () {
        // Given
        $data = [
            ['name' => 'Game 1', 'slug' => 'game-1'],
            'invalid_item',
            null,
            ['name' => 'Game 2', 'slug' => 'game-2'],
        ];

        $dto1 = createIgdbSearchResponseDto($this->faker);
        $dto2 = createIgdbSearchResponseDto($this->faker);

        $callCount = 0;
        $this->denormalizer
            ->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnCallback(function ($item, $type) use ($data, $dto1, $dto2, &$callCount) {
                expect($type)->toBe(IgdbSearchResponseDto::class);

                if ($callCount === 0) {
                    expect($item)->toBe($data[0]);
                    $callCount++;
                    return $dto1;
                }

                expect($item)->toBe($data[3]);
                return $dto2;
            });

        // When
        $result = $this->serializer->denormalize($data, 'array');

        // Then
        expect($result)
            ->toBeArray()
            ->toHaveCount(2)
            ->and($result[0])->toBe($dto1)
            ->and($result[1])->toBe($dto2);
    });

    it('passes format and context to denormalizer', function () {
        // Given
        $data = [['name' => 'Game 1', 'slug' => 'game-1']];
        $format = 'json';
        $context = ['groups' => ['test']];

        $dto = createIgdbSearchResponseDto($this->faker);

        $this->denormalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with(
                $this->identicalTo($data[0]),
                $this->identicalTo(IgdbSearchResponseDto::class),
                $this->identicalTo($format),
                $this->identicalTo($context)
            )
            ->willReturn($dto);

        // When
        $this->serializer->denormalize($data, 'array', $format, $context);
    });
});

function createIgdbSearchResponseDto(Generator $faker): IgdbSearchResponseDto
{
    return new IgdbSearchResponseDto(
        id: $faker->randomNumber(),
        name: $faker->words(3, true),
        slug: $faker->slug(),
        involved_companies: [
            [
                'company' => ['name' => $faker->company()],
                'publisher' => true
            ]
        ],
        cover: [
            'url' => '//images.igdb.com/igdb/image/upload/t_thumb/' . $faker->sha1() . '.jpg'
        ],
        first_release_date: $faker->unixTime(),
        summary: $faker->paragraph(),
        websites: [
            ['url' => $faker->url()]
        ],
        updatedAt: new \DateTime('now')->getTimestamp(),
    );
}
