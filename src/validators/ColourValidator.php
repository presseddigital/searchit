<?php
namespace fruitstudios\searchit\validators;

use Craft;

class ColourValidator extends YiiValidator
{
    public $format = 'hex';

    // Public Methods
    // =========================================================================

    public function validateValue($value)
    {
        switch ($this->format)
        {
            case 'hex':
                return preg_match('/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i', $value) ? true : false;
                break;

            case 'rgb':
                return preg_match('/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i', $value) ? true : false;
                break;

            case 'rgba':
                return preg_match('/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i', $value) ? true : false;
                break;

            default:
                return false;
                break;
        }
    }

}
