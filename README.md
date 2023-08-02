# MYC Getter

This class aims to facilitate the process of obtaining and sanitizing data from the post information (ID or object).

With MYC Getter, is possible to replace this:
```php
$posts = [];
foreach ($query->posts as $post) {
  $genresObjects = get_the_terms($post, 'genre');
  $genres = [];
  if (is_array($genresObjects)) {
    foreach ($genresObjects as $genre) {
      $genres[] = [
        'name'      => esc_html($genre->name),
        'url'       => esc_url(get_term_link($genre)),
      ];
    }
  }

  $posts[] = [
      'title'     => esc_html($post->post_title),
      'url'       => esc_url(get_the_permalink($post)),
      'image'     => get_the_post_thumbnail($post, 'large'),
      'author'    => absint(get_field('author', $post)),
      'genres'    => $genres,
  ];
}
```
By this:
```php
$args = [
  'acf'         => ['author' => 'string'],
  'taxonomies'  => ['genre' => 'link'],
];
$getter = new MycGetter($args);

$posts = [];
foreach ($query->posts as $post) {
  $posts[] = $getter->escapedContent($post);
}
```
## Requirements
- PHP >= 8.0

## Instalattion
```bash
composer require radishlab/myc-getter
```
## Usage
```php
use RadishLab\MycGetter\MycGetter;

class MyClass {
  public function MyMethod()
  {
    $getter = new MycGetter();
  }
}
```
The class accept two options:
- An array of arguments, listing the content you want to pull
- A preset name

If nothing is passed, the class will use the arguments of the preset `default`.
```php
[
  'fields'        => ['title', 'url', 'image'],
  'image_size'    => 'large',
  'image_type'    => 'html',
  'acf'           => false,
  'taxonomies'    => false,
];
```

To overwrite the `default` preset, you just need to pass the keys you want to change:
```php
$args = [
  'fields' => ['title'],
];
$getter = new MycGetter($args);

// The arguments used by the class will be:
[
  'fields'        => ['title'],
  'image_size'    => 'large',
  'image_type'    => 'html',
  'acf'           => false,
  'taxonomies'    => false,
];
```
If you're using the same arguments in multiples places, you can use the filter `myc_getter_presets` to create a new preset:

```php
add_filter('myc_getter_presets', function ($presets) {
  $presets['new_preset'] = [
    'fields'    => ['title'],
    'image_size' => 'medium',
  ];

  return $presets;
});
```

Then, you can use this new preset when instantiate the class:
```php
$getter = new MycGetter('new_preset');
```

## fields
- Format: array

Each field provides a filter to modify the returned content. The filter has three parameters: `$value`, `$post_type` and `$post_id`.

### title
- Returns escaped content of `post->post_title`
- Format: string
- Filter name: `myc_getter_get_title`

### url
- Returns escaped permalink
- Format: string
- Filter name: `myc_getter_get_url`

### image
- Returns image markup or image URL based on the value of `image_type` and `image_size`
- Format: string
- Filter name: `myc_getter_get_image`

### text
- Returns escaped content of `post->post_content`
- Format: string
- Filter name: `myc_getter_get_text`

### excerpt
- Returns escaped content of the function `get_the_excerpt($post)`
- Format: string
- Filter name: `myc_getter_get_excerpt`

### date
- Returns escaped dates of the post in two different format: using the global date format and using ISO 8601 (`c`) date	format
- Format: array
- Filter name: `myc_getter_get_date`

## image_size
- Format: string

Use one of the default WordPress image sizes (`thumbnail`, `medium`, `medium_large`, `large`, `full`) or a custom image size created by `add_image_size()` function.

## image_type
- Format: string

Defines the format that the featured image will be returned: `html` or `url`.

## acf
- Format: array

List of key/value where the key is the field slug and the value is the field type.

Each field type provides a filter to modify the returned content. The filter has four parameters: `$value`, `$post_type`, `$post_id` and `$field_slug`.

### date
- Returns date in global date format or `false` if it is empty or the date is not formatted correctly
- Format: string
- Filter name: `myc_getter_get_acf_date`

### time
- Returns time in global time format or `false` if it is empty or the time is not formatted correctly
- Format: string
- Filter name: `myc_getter_get_acf_time`

### link
- Returns escaped link info (`title`, `url`, `target`, `rel`) or `false` if it is empty
- Format: array
- Filter name: `myc_getter_get_acf_link`

### string
- Returns escaped content using `esc_html()` function or `false` if it is empty
- Format: string
- Filter name: `myc_getter_get_acf_string`

### attr
- Returns escaped content using `esc_attr()` function or `false` if it is empty
- Format: string
- Filter name: `myc_getter_get_acf_attr`

### text
- Returns escaped content using `wp_kses_post()` function or `false` if it is empty.
- Format: string
- Filter name: `myc_getter_get_acf_text`

### image
- Returns the image markup or `false` if the image doesn't exists
- Pass the image size inside the key using a pipe (e.g. `image_field|medium`)
- Format: string
- Filter name: `myc_getter_get_acf_image`

### image_url
- Returns the escaped image url or `false` if the image doesn't exists
- Pass the image size inside the key using a pipe (e.g. `image_field|medium`)
- Format: string
- Filter name: `myc_getter_get_acf_image`

### int
- Returns the escaped integer value
- Format: int
- Filter name: `myc_getter_get_acf_int`

### email
- Returns the escaped email value using the function `antispambot()`
- Format: string
- Filter name: `myc_getter_get_acf_email`

### raw
- Returns the content without any treatment
- Format: string
- Filter name: `myc_getter_get_acf_raw`

### repeater
- For repeater fields, pass an array as the value:
```php
'acf'      => [
  'cards' => ['headline' => 'string', 'button' => 'link', 'text' => 'string']
],
```

## taxonomies
- Format: array

List of key/value where the key is the taxonomy slug and the value is the desired returned format.

Each format provides a filter to modify the returned content. The filter has three parameters: `$terms`, `$post_type` and `$post_id`.

### all
- Returns the term object without any treatment
- Format: object
- Filter name: `myc_getter_get_terms_object`

### name
- Returns escaped content of `term->name`
- Format: string
- Filter name: `myc_getter_get_terms_name`

### link
- Returns escaped content of `term->name` and `get_term_link()` function
- Format: array
- Filter name: `myc_getter_get_terms_link`

### slug/name
- Returns escaped content of `term->slug` and `term->name`
- Format: array
- Filter name: `myc_getter_get_terms_slug_name`

### slug/id
- Returns escaped content of `term->term_id` and `term->name`
- Format: array
- Filter name: `myc_getter_get_terms_id_name`
