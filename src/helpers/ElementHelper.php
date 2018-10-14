<?php
namespace fruitstudios\searchit\helpers;

use fruitstudios\searchit\Searchit;

use Craft;
use craft\elements\Entry;
use craft\elements\Category;
use craft\elements\Asset;
use craft\elements\User;

use craft\helpers\StringHelper;

use craft\commerce\Plugin as Commerce;
use craft\commerce\elements\Product;
use craft\commerce\elements\Order;
use craft\commerce\elements\Subscription;

class ElementHelper
{
    // Public Methods
    // =========================================================================

    public static function getElementTypeByHandle(string $handle)
    {
        switch ($handle)
        {
            case 'entry':
            case 'entries':
                return Entry::class;
                break;

            case 'category':
            case 'categories':
                return Category::class;
                break;

            case 'asset':
            case 'assets':
                return Asset::class;
                break;

            case 'user':
            case 'users':
                return User::class;
                break;

            case 'product':
            case 'products':
                return Searchit::$commerceInstalled ? Product::class : false;
                break;

            case 'order':
            case 'orders':
                return Searchit::$commerceInstalled ? Order::class : false;
                break;

            case 'subscription':
            case 'subscriptions':
                return Searchit::$commerceInstalled ? Subscription::class : false;
                break;

            default:
                return false;
                break;
        }
    }

    public static function sourceKeyAsHandle(string $key)
    {
        if ($key == Searchit::$plugin->getElementFilters()::GLOBAL_SOURCE_KEY)
        {
            return Searchit::$plugin->getElementFilters()::GLOBAL_SOURCE_HANDLE;
        }

        return StringHelper::replace($key, ':', '-');
    }


}
