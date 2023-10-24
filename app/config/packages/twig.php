<?php
use Symfony\Config\TwigConfig;

return static function (TwigConfig $twig): void {
    // ...
    $twig->debug('%kernel.debug%');
};