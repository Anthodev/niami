<?php

declare(strict_types=1);

namespace App\Tests\Functional\Domain\User\Common;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

it('can ping the api', function () {
    // When
    static::$client->request(Request::METHOD_GET, '/api/ping');

    // Then
    $response = static::$client->getResponse();
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
    expect($response->getContent())->toBe('"pong"');
});

it('cannot ping the authenticated api', function () {
    // When
    static::$client->request(Request::METHOD_GET, '/api/ping_auth');

    // Then
    $response = static::$client->getResponse();
    expect($response->getStatusCode())->toBe(Response::HTTP_FORBIDDEN);

    $data = json_decode($response->getContent(), true);
    expect($data['message'])
        ->toBe('Unauthorized')
    ;
});

it('can ping the authenticated api', function () {
    // Given
    $user = $this->makeDefaultUser();
    $this->loginUser($user->getEmail());

    // When
    static::$client->request(Request::METHOD_GET, '/api/ping_auth');

    // Then
    $response = static::$client->getResponse();
    expect($response->getStatusCode())->toBe(Response::HTTP_OK);
    expect($response->getContent())->toBe('"pong"');
});
