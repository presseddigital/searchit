
<p align="left"><a href="https://github.com/fruitstudios/craft-searchit" target="_blank"><img width="100" height="100" src="resources/img/searchit.svg" alt="Searchit"></a></p>

# Searchit plugin for Craft CMS 3

Configure powerful custom filters for an enhanced search in the Craft CMS control panel.

<p align="left"><a href="https://www.youtube.com/watch?v=CYzaND0IGPw" target="_blank"><img width="600" src="resources/img/searchit-promo.png" alt="Searchit Promo Video"></a></p>

## Example usage

### Entries

Create filters for authors, date, categories, etc..

<p align="left"><img width="600px" src="resources/img/searchit-entries.png" alt="Entries"></a></p>

How to get this filter...

```
{% for user in craft.users.all() %}
    {{ ({
        filter: {
            authorId: user.id
        },
        label: user.fullName
    })|json_encode() }}{{ not loop.last ? ',' }}
{% endfor %}
```

### Categories

Create filters to narrow down categories by heirarchy.

<p align="left"><img width="600px" src="resources/img/searchit-categories.png" alt="Categories"></a></p>

How to get this filter...

```
{% for category in craft.categories.group(‘alcoholicDrinks’).level(1).all() %}
   {{ ({
       filter: {
           descendantOf: category.id
       },
       label: category.title
   })|json_encode() }}{{ not loop.last ? ‘,’ }}
{% endfor %}
```

### Assets
Create filters for file types, extensions etc.

<p align="left"><img width="600px" src="resources/img/searchit-assets.png" alt="Assets"></a></p>

How to get these filters...

**Kind filter**
```json
{ "filter":"kind:compressed", "label":"Zip" },
{ "filter":"kind:image", "label":"Images" }
```
**Extension filter**
```json
{ "filter":"extension:jpg", "label":"JPG" },
{ "filter":"extension:png", "label":"PNG" },
{ "filter":"extension:gif", "label":"GIF" }
```


## Creating a filter

Filters can be produced manually or dynamically and are made up of a JSON array containing rows with a label key `(string)` and a filter key `(string or valid JSON)`. If the filter contains a `string` then it will pass that value to the `search` parameter on the element search. If you pass JSON to the filter than you can create multiple parameters.

<p align="left"><img width="600px" src="resources/img/searchit-new.png" alt="New Filter"></a></p>
<p align="left"><img width="600px" src="resources/img/searchit-config.png" alt="Configuration"></a></p>

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

#### Useful Resources
Craft CMS Search Documentation [Docs](https://docs.craftcms.com/v3/searching.html)

As with the regular Craft Search, if you suspect that your search indexes don’t have the latest and greatest data, you can have Craft rebuild them with the Rebuild Search Indexes tool in Settings. [Docs](https://docs.craftcms.com/v3/searching.html#rebuilding-your-search-indexes)


Brought to you by [FRUIT](https://fruitstudios.co.uk)
