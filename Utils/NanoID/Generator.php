<?php
namespace Feeler\Fl\Utils\NanoID;

class Generator implements GeneratorInterface
{
    /**
     * @inheritDoc
     */
    public function random($size)
    {
        return unpack('C*', \random_bytes($size));
    }
}