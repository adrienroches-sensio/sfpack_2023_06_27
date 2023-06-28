<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Movie;
use Attribute;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\RegexValidator;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class MovieSlugFormat extends Regex
{
    public function __construct(string $message = null, string $htmlPattern = null, bool $match = null, callable $normalizer = null, array $groups = null, mixed $payload = null, array $options = [])
    {
        parent::__construct('#'.Movie::SLUG_FORMAT.'#', $message, $htmlPattern, $match, $normalizer, $groups, $payload, $options);
    }

    public function validatedBy(): string
    {
        return RegexValidator::class;
    }
}
