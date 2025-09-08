<?php

declare(strict_types=1);

namespace App\Infrastructure\Client;

use App\Application\Processor\SearchQueryProcessor;
use App\Domain\Factory\Game\ApiGameFactory;
use App\Domain\Model\Game\ApiGame;
use App\Infrastructure\Enum\ApiTypeRequestEnum;
use App\Infrastructure\Enum\IgdbGamePlatformEnum;
use App\Infrastructure\Enum\IgdbGameTypeEnum;
use App\Infrastructure\Exception\Game\IgdbAccessTokenRetrievalException;
use App\Shared\Dto\Game\GameCompanyDataDto;
use App\Shared\Dto\Game\IgdbSearchResponseDto;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IgdbClient implements ApiClientInterface
{
    private const string API_RESPONSE_CACHE_PREFIX = 'igdb_api_';
    private const int API_RESPONSE_CACHE_TTL = 72 * 3600;
    private const string ACCESS_TOKEN_CACHE_KEY = 'igdb_access_token';
    private const int ACCESS_TOKEN_CACHE_TTL = 30 * 24 * 3600;

    private HttpClientInterface $httpClient;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly CacheInterface $cache,
        private readonly SerializerInterface $serializer,
        private readonly string $baseUrl = 'https://api.igdb.com/v4/',
    ) {
        $this->httpClient = HttpClient::create();
    }

    /**
     * @return ApiGame[]
     *
     * @throws InvalidArgumentException
     */
    public function searchGames(string $query, int $limit = 25): array
    {
        $searchCacheKey = $this->generateSearchCacheKey($query, $limit);

        $apiGames = $this->cache->get(
            $searchCacheKey,
            function () use ($query, $limit) {
                return $this->performApiRequest(
                    query: $query,
                    type: ApiTypeRequestEnum::SEARCH,
                    limit: $limit,
                );
            },
            self::API_RESPONSE_CACHE_TTL,
        );

        foreach ($apiGames as $apiGame) {
            $this->cache->get(
                'api_game_'.$apiGame->getSlug(),
                function () use ($apiGame) {
                    return $apiGame;
                },
            );
        }

        return $apiGames;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getGameBySlug(string $slug, int $limit = 3): ?ApiGame
    {
        $cacheKey = $this->generateGetGameSlugCacheKey($slug, $limit);

        $game = $this->cache->get(
            $cacheKey,
            function () use ($slug, $limit) {
                return $this->performApiRequest(
                    query: $slug,
                    type: ApiTypeRequestEnum::SLUG,
                    limit: $limit,
                );
            },
            self::API_RESPONSE_CACHE_TTL,
        );

        if (!empty($game)) {
            return $game[0];
        }

        return null;
    }

    private function generateSearchCacheKey(string $query, int $limit): string
    {
        $cacheKeyData = [
            'query' => trim(strtolower($query)),
            'limit' => $limit,
            'platform' => IgdbGamePlatformEnum::NINTENDO_SWITCH->value,
        ];

        /** @phpstan-ignore-next-line */
        $md5CacheKeyData = md5(json_encode($cacheKeyData));

        return self::API_RESPONSE_CACHE_PREFIX.'search_'.$md5CacheKeyData;
    }

    private function generateGetGameSlugCacheKey(
        string $query,
        int $limit,
    ): string {
        $cacheKeyData = [
            'query' => trim(strtolower($query)),
            'limit' => $limit,
            'platform' => IgdbGamePlatformEnum::NINTENDO_SWITCH->value,
        ];

        /** @phpstan-ignore-next-line */
        $md5CacheKeyData = md5(json_encode($cacheKeyData));

        return self::API_RESPONSE_CACHE_PREFIX.'slug_'.$md5CacheKeyData;
    }

    /**
     * @return ApiGame[]
     *                                           *
     * @throws ExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws IgdbAccessTokenRetrievalException
     * @throws InvalidArgumentException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function performApiRequest(
        string $query,
        ApiTypeRequestEnum $type,
        int $limit = 10,
    ): array {
        $query = trim(strtolower($query));
        $queryTerms = SearchQueryProcessor::process($query);

        if (empty($queryTerms)) {
            return [];
        }

        $apiQuery = '';

        if (ApiTypeRequestEnum::SLUG === $type) {
            $apiQuery = sprintf('slug ~ *"%s";', $query);
        } else {
            foreach ($queryTerms as $term) {
                $apiQuery .=
                    $term === $queryTerms[0]
                        ? sprintf('name ~ *"%s"*', $term)
                        : sprintf(' & name ~ *"%s"*', $term);
            }
        }

        $body = sprintf(
            '
                    fields name,
                    slug,
                    involved_companies.company.name,
                    involved_companies.company.websites.url,
                    involved_companies.publisher,
                    involved_companies.developer,
                    cover.url,
                    first_release_date,
                    summary,
                    websites.url,
                    updated_at;
                    where (%s)
                    & platforms = (%d)
                    & %s
                    & version_parent = null
                    & first_release_date < %d;
                    sort first_release_date desc;
                    limit %d;
                ',
            $apiQuery,
            IgdbGamePlatformEnum::NINTENDO_SWITCH->value,
            sprintf(
                '(game_type = %d | game_type = %d | game_type = %d | game_type = %d)',
                IgdbGameTypeEnum::MAIN_GAME->value,
                IgdbGameTypeEnum::REMAKE->value,
                IgdbGameTypeEnum::EXPANDED_GAME->value,
                IgdbGameTypeEnum::PORT->value,
            ),
            time(),
            $limit,
        );

        $responseData = $this->makeRequestWithRetry(
            httpMethod: Request::METHOD_POST,
            body: $body,
            endpoint: 'games',
        );

        /**
         * @var IgdbSearchResponseDto[] $deserializedResponseData
         *
         * @phpstan-ignore-next-line
         */
        $deserializedResponseData = $this->serializer->denormalize(
            $responseData,
            IgdbSearchResponseDto::class.'[]',
        );

        return $this->formatResponseData($deserializedResponseData);
    }

    /**
     * @param IgdbSearchResponseDto[] $igdbSearchResponseDto
     *
     * @return ApiGame[]
     */
    private function formatResponseData(array $igdbSearchResponseDto): array
    {
        $apiGames = [];

        foreach ($igdbSearchResponseDto as $igdbSearchResultItem) {
            $firstReleaseTimestamp = null;
            if (!empty($igdbSearchResultItem->first_release_date)) {
                $firstReleaseTimestamp =
                    $igdbSearchResultItem->first_release_date;
            }

            $releaseDate = null;
            if ($firstReleaseTimestamp) {
                $releaseDate = new \DateTime();
                $releaseDate->setTimestamp($firstReleaseTimestamp);
            }

            $publisher = new GameCompanyDataDto('', '', 0);
            $developer = new GameCompanyDataDto('', '', 0);

            if (!empty($igdbSearchResultItem->involved_companies)) {
                /** @var array<string, mixed> $company */
                foreach ($igdbSearchResultItem->involved_companies as $company) {
                    if (empty($publisher->name)) {
                        $publisher = $this->checkCompany(
                            involvedCompany: $company,
                            companyType: 'publisher',
                            oldCompany: $publisher,
                        );
                    }

                    if (empty($developer->name)) {
                        $developer = $this->checkCompany(
                            involvedCompany: $company,
                            companyType: 'developer',
                            oldCompany: $developer,
                        );
                    }

                    if (
                        !empty($publisher->name)
                        && !empty($developer->name)
                    ) {
                        break;
                    }
                }
            }

            $slug = $igdbSearchResultItem->slug;

            $imageCover = '';
            if (!empty($igdbSearchResultItem->cover)) {
                /** @var string $coverUrl */
                $coverUrl = $igdbSearchResultItem->cover['url'] ?? '';
                $imageCover = 'https:'.$coverUrl;
            }

            if (
                empty($publisher->name)
                || empty($developer->name)
            ) {
                if (empty($developer->name)) {
                    $developer = null;
                }

                $publisher = null;
            }

            $updatedAt = new \DateTimeImmutable()->setTimestamp($igdbSearchResultItem->updatedAt);

            $apiGames[] = ApiGameFactory::create(
                name: $igdbSearchResultItem->name,
                slug: $slug,
                description: $igdbSearchResultItem->summary ? nl2br($igdbSearchResultItem->summary) : '',
                imageCover: $imageCover,
                releaseDate: $releaseDate
                    ? $releaseDate->format(DATE_ATOM)
                    : '',
                updatedAt: $updatedAt,
                publisher: $publisher,
                developer: $developer,
            );
        }

        return $apiGames;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getAuthorizationToken(): string
    {
        return $this->cache->get(
            self::ACCESS_TOKEN_CACHE_KEY,
            function () {
                return $this->generateNewAuthorizationToken();
            },
            self::ACCESS_TOKEN_CACHE_TTL,
        );
    }

    /**
     * @throws IgdbAccessTokenRetrievalException
     */
    private function generateNewAuthorizationToken(): string
    {
        try {
            $response = $this->httpClient->request(
                Request::METHOD_POST,
                sprintf(
                    'https://id.twitch.tv/oauth2/token?client_id=%s&client_secret=%s&grant_type=client_credentials',
                    $this->clientId,
                    $this->clientSecret,
                ),
            );

            $data = $response->toArray();

            if (!isset($data['access_token'])) {
                throw new IgdbAccessTokenRetrievalException('Access token not found in response');
            }

            return $data['access_token'];
        } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            throw new IgdbAccessTokenRetrievalException('Failed to retrieve access token: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function invalidateAuthorizationTokenCache(): void
    {
        $this->cache->delete(self::ACCESS_TOKEN_CACHE_KEY);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function makeRequest(
        string $accessToken,
        string $httpMethod,
        string $body,
        string $endpoint,
    ): array {
        $response = $this->httpClient->request(
            $httpMethod,
            $this->baseUrl.$endpoint,
            [
                'headers' => [
                    'Client-ID' => $this->clientId,
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                ],
                'body' => $body,
            ],
        );

        return $response->toArray();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws IgdbAccessTokenRetrievalException
     * @throws ServerExceptionInterface
     */
    private function makeRequestWithRetry(
        string $httpMethod,
        string $body,
        string $endpoint,
        bool $isRetry = false,
    ): array {
        try {
            $accessToken = $this->getAuthorizationToken();

            return $this->makeRequest(
                $accessToken,
                $httpMethod,
                $body,
                $endpoint,
            );
        } catch (ClientExceptionInterface $e) {
            if (401 === $e->getResponse()->getStatusCode() && !$isRetry) {
                $this->invalidateAuthorizationTokenCache();

                return $this->makeRequestWithRetry(
                    $httpMethod,
                    $body,
                    $endpoint,
                    true,
                );
            }

            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $involvedCompany
     */
    private function checkCompany(
        array $involvedCompany,
        string $companyType,
        GameCompanyDataDto $oldCompany,
    ): GameCompanyDataDto {
        if ('' !== $oldCompany->name) {
            return $oldCompany;
        }

        $isCompany = $involvedCompany[$companyType] ?? false;

        if ($isCompany) {
            /** @var array<string, int|string|array<string, string>> $involvedCompany */
            $company = $involvedCompany['company'] ?? [];

            if (empty($company)) {
                return new GameCompanyDataDto('', '', 0);
            }

            $companyName = '';
            if (isset($company['name']) && is_string($company['name'])) {
                $companyName = $company['name'];
            }

            $companyWebsites = [];
            if (isset($company['websites']) && is_array($company['websites'])) {
                $companyWebsites = $company['websites'];
            }

            $companyWebsite = '';
            if (isset($companyWebsites[0]['url']) && is_array($companyWebsites[0]) && is_string(
                $companyWebsites[0]['url']
            )) {
                $companyWebsite = $companyWebsites[0]['url'];
            }

            $companyApiId = 0;
            if (isset($company['id']) && (is_int($company['id']) || is_numeric($company['id']))) {
                $companyApiId = (int) $company['id'];
            }

            return new GameCompanyDataDto(
                name: $companyName,
                website: $companyWebsite,
                apiId: $companyApiId,
            );
        }

        return new GameCompanyDataDto('', '', 0);
    }
}
