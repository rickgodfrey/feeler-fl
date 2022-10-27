<?php
namespace Feeler\Fl\Utils\NanoID;

interface GeneratorInterface
{
    /**
     * Return random bytes array
     *
     * @param int $size
     * @return array
     */
    public function random($size);
}