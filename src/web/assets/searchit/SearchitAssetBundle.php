<?php
namespace presseddigital\searchit\web\assets\searchit;

use Craft;

use yii\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SearchitAssetBundle extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->sourcePath = "@presseddigital/searchit/web/assets/searchit/build";

        $this->depends = [];

        $this->js = [
            'js/vendor/polyfill.js',
            'js/vendor/extend.js',
            'js/vendor/copy.js',
            'js/vendor/trueTypeOf.js',
            'js/ElementFilters.js',
        ];

        $this->css = [
            'css/cp.css',
            'css/searchit.css',
        ];

        parent::init();
    }
}
