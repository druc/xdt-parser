<?php

namespace Druc\XdtParser;

class CorruptedXdt extends \Exception
{
    protected $message = 'File is corrupted';
}
