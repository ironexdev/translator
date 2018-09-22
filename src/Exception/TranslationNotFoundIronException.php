<?php

namespace Ironex\Exception;

use Exception;

class TranslationNotFoundIronException extends Exception
{
    public function __construct()
    {
        parent::__construct("Translation Not Found");
    }
}