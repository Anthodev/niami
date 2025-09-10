<?php

declare(strict_types=1);

namespace App\Application\Query\Search;

use Symfony\Component\Form\FormInterface;

class GetSearchResultsQuery
{
    public function __construct(
        public readonly FormInterface $form,
    ) {
    }
}
