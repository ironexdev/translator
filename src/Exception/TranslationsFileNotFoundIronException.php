<?php

namespace Ironex\Exception;

use Exception;

class TranslationsFileNotFoundIronException extends Exception
{
    public function __construct()
    {
        parent::__construct("Translations File Not Found");
    }
}