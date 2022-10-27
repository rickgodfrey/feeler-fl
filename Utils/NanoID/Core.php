<?php
namespace Feeler\Fl\Utils\NanoID;

class Core implements CoreInterface
{
    /**
     * @param GeneratorInterface $generator
     * @param $size
     * @param $alphabet
     * @return string
     */
    public function random(GeneratorInterface $generator, $size, $alphabet = CoreInterface::SAFE_SYMBOLS)
    {
        $len = strlen($alphabet);
        $mask = (2 << (int) (log($len - 1) / M_LN2)) - 1;
        $step = (int) ceil(1.6 * $mask * $size / $len);
        $id = '';
        while (true) {
            $bytes = $generator->random($step);
            // NOTE: `$bytes` maybe not a normal "Array"
            // sometimes it's begin from index 1, use iterator please
            foreach ($bytes as $byte) {
                $byte &= $mask;
                if (isset($alphabet[$byte])) {
                    $id .= $alphabet[$byte];
                    if (strlen($id) === $size) {

                        return $id;
                    }
                }
            }
        }
    }
}

