<?php

namespace LionMM\WebDriver;

use Facebook\WebDriver\WebDriverBy;

class WebDriverExpectedCondition extends \Facebook\WebDriver\WebDriverExpectedCondition
{
    /**
     * {@inheritdoc}
     */
    public static function presenceOfAllElementsLocatedBy(WebDriverBy $by, $need = 1)
    {
        return new static(
            function ($driver) use ($by, $need) {
                $elements = $driver->findElements($by);

                return count($elements) >= $need ? $elements : null;
            }
        );
    }
}
