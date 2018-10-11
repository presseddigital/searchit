
<p align="left"><a href="https://github.com/fruitstudios/craft-searchit" target="_blank"><img width="100" height="100" src="resources/img/searchit.svg" alt="Searchit"></a></p>

# Searchit plugin for Craft CMS 3

Configure powerful custom filters for enhanced search in the Craft CP.

This plugin gives your CP users an extensive tool for searching/ filtering entries, categories, assets and users in the CP.

Uses and extends the Craft CP element [search](https://docs.craftcms.com/v3/searching.html)

[![Searchit Promo video](resources/img/searchit-promo.png)](https://www.youtube.com/watch?v=CYzaND0IGPw)

## Creating a filter

Filters can be produced manually or dynamically and are made up of a JSON array containing rows with a label key `(string)` and a filter key `(string or valid JSON)`. If the filter contains a `string` then it will pass that value to the `search` parameter on the element search. If you pass JSON to the filter than you can create multiple parameters.

## Examples

You have two ways to setup filters. Manually or dynamically.

#### Using Twig (Recommended)
```php
{% for category in craft.categories.group('countries').all() %}
    {{ ({
        filter: {
            relatedTo: {
                element: category.id,
                field: 'countries'
            }
        },
        label: category.title
    })|json_encode() }}{{ not loop.last ? ',' }}
{% endfor %}
```

```php
{% for user in craft.users.all() %}
    {{ ({
        filter: {
            authorId: user.id
        },
        label: user.fullName
    })|json_encode() }}{{ not loop.last ? ',' }}
{% endfor %}
```

```php
 {{ ({ label: user.fullName filter: {authorId: user.id},})|json_encode() }}{{ not loop.last ? ',' }}
```

```
{{ craft.entries.section('').authorId('1').all() }}
```

#### Using JSON
```json
{ "filter":"page 1", "label":"Page 1" },
{ "filter":"page 2", "label":"Page 2" },
{ "filter":"page 3", "label":"Page 3" },
{ "filter":"page 4", "label":"Page 4" }
```

```
{{ craft.entries.section('').search('page 1').all() }}
```

#### As an include
```
{% include '_includes/filters/rooms' ignore missing %}
```


Tip: As with the regular Craft Search, if you suspect that your search indexes don’t have the latest and greatest data, you can have Craft rebuild them with the Rebuild Search Indexes tool in Settings.


Brought to you by [Fruit Studios](https://fruitstudios.co.uk)
