<?php

use Symfony\Component\Validator\Validator\ValidatorInterface;

beforeEach(function () {
    $this->validator = $this->getContainer()->get(ValidatorInterface::class);
});

it('can validate a role', function () {
    $user = $this->makeUser();
    $role = $user->getRole();

    $errors = $this->validator->validate($role);

    expect($errors)->toBeEmpty();
});

it('cannot validate a role with an empty label', function () {
    $user = $this->makeUser();
    $role = $user->getRole();
    $role->setLabel('');

    $errors = $this->validator->validate($role);

    expect(count($errors))
        ->toBeGreaterThan(1)
        ->and($errors[0]->getPropertyPath())->toBe('label')
        ->and($errors[0]->getMessage())->toBe('This value should not be blank.');
});

it('cannot validate a role with an empty code', function () {
    $user = $this->makeUser();
    $role = $user->getRole();
    $role->setCode('');

    $errors = $this->validator->validate($role);

    expect(count($errors))
        ->toBeGreaterThan(1)
        ->and($errors[0]->getPropertyPath())->toBe('code')
        ->and($errors[0]->getMessage())->toBe('This value should not be blank.');
});

it('cannot validate a role with an invalid code', function () {
    $user = $this->makeUser();
    $role = $user->getRole();
    $role->setCode('ab');

    $errors = $this->validator->validate($role);

    expect($errors)
        ->toHaveCount(1)
        ->and($errors[0]->getPropertyPath())->toBe('code')
        ->and($errors[0]->getMessage())->toBe('This value is too short. It should have 3 characters or more.');
});
