<?php

use Symfony\Component\Validator\Validator\ValidatorInterface;

beforeEach(function () {
    $this->validator = $this->getContainer()->get(ValidatorInterface::class);
});

it('can validate a user', function () {
    $user = $this->makeUser();

    $eroors = $this->validator->validate($user);

    expect($eroors)->toBeEmpty();
});

it('cannot validate a user with an empty email', function () {
    $user = $this->makeUser();
    $user->setEmail('');

    $errors = $this->validator->validate($user);

    expect($errors)
        ->ToHaveCount(1)
        ->and($errors[0]->getPropertyPath())->toBe('email')
        ->and($errors[0]->getMessage())->toBe('This value should not be blank.');
});

it('cannot validate a user with an invalid email', function () {
    $user = $this->makeUser();
    $user->setEmail('invalid');

    $erorrs = $this->validator->validate($user);

    expect($erorrs)
        ->toHaveCount(1)
        ->and($erorrs[0]->getPropertyPath())->toBe('email')
        ->and($erorrs[0]->getMessage())->toBe('This value is not a valid email address.');
});

it('cannot validate a user with an empty username', function () {
    $user = $this->makeUser();
    $user->setUsername('');

    $errors = $this->validator->validate($user);

    expect(count($errors))
        ->toBeGreaterThan(1)
        ->and($errors[0]->getPropertyPath())->toBe('username')
        ->and($errors[0]->getMessage())->toBe('This value should not be blank.');
});
