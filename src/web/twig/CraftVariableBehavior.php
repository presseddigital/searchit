<?php
namespace presseddigital\searchit\web\twig;

use presseddigital\searchit\Searchit;

use Craft;
use yii\base\Behavior;

class CraftVariableBehavior extends Behavior
{
    public $searchit;

    public function init()
    {
        parent::init();
        // Point `craft.searchit` to the craft\searchit\Plugin instance
        $this->searchit = Searchit::getInstance();
    }
}
