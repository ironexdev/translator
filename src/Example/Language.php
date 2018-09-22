<?php

namespace Ironex\Example;

use Ironex\LanguageInterface;

class Language implements LanguageInterface
{
    /**
     * @var string
     */
    private $locale;

    /**
     * Language constructor.
     * @param string $locale
     */
    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }
}