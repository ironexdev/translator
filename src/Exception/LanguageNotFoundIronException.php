<?php

namespace Ironex\Exception;

use Exception;

class LanguageNotFoundIronException extends Exception
{
    public function __construct()
    {
        parent::__construct("Language Not Found");
    }
}