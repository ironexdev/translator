<?php

namespace Ironex;

interface LanguageInterface
{
    /**
     * @return string
     */
    public function getLocale(): string; // en-US, cs-CZ, ...
}