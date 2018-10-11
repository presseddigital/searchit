<?php
namespace fruitstudios\searchit\web\twig;

use fruitstudios\searchit\Searchit;

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
