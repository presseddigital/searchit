<?php
namespace fruitstudios\searchit\web\assets\searchit;

use Craft;

use yii\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SearchitAssetBundle extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->sourcePath = "@fruitstudios/searchit/web/assets/searchit/build";

        $this->depends = [];

        $this->js = [
            'js/vendor/polyfill.js',
            'js/vendor/extend.js',
            'js/Searchit.js',
        ];

        $this->css = [
            'css/cp.css',
            'css/searchit.css',
        ];

        parent::init();
    }
}
