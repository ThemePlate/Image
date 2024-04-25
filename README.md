# ThemePlate Image

## Usage

```php
use ThemePlate\Image;

Image::register( 'wanted_size', 1920, 1080 );
Image::manipulate( 'wanted_size', 'pixilate', 10 );

Image::register( 'another_size', 640, 480 );
Image::manipulate( 'another_size', 'greyscale' );
Image::manipulate( 'another_size', 'blur', 20 );

$processor = Image::processor();

$processor->report( function( $output ) {
    error_log( print_r( $output, true ) );
} );

// Simply use the core functions like you normally would
// - wp_get_attachment_image
// - wp_get_attachment_image_url
// - the_post_thumbnail
// - get_the_post_thumbnail
// - get_the_post_thumbnail_url
```

### Force refresh image/s

`<WP_HOME>/?tpi_refresh=<attachment_id>`

`<WP_HOME>/?tpi_refresh[]=<id1>&tcs_refresh[]=<id2>`

> _works only when logged-in_

### Image::register( $name, $width, $height )

- **$name** _(string)(Required)_ Size identifier
- **$width** _(int)(Required)_ Width in pixels
- **$height** _(int)(Required)_ Height in pixels

### Image::manipulate( $size, $filter, $args )

- **$size** _(string)(Required)_ Registered size
- **$filter** _(string)(Required)_ Filter to apply
- **$args** _(array)(Optional)_ Parameters to pass. Default `null`

> See available filters in <https://image.intervention.io/v2/>

### Image::processor()

Do the processing(crop/resize and manipulations) in the background
