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

### Image::register( $name, $width, $height )

- **$name** *(string)(Required)* Size identifier
- **$width** *(int)(Required)* Width in pixels
- **$height** *(int)(Required)* Height in pixels

### Image::manipulate( $size, $filter, $args )

- **$size** *(string)(Required)* Registered size
- **$filter** *(string)(Required)* Filter to apply
- **$args** *(array)(Optional)* Parameters to pass. Default `null`

> See available filters in <https://image.intervention.io/v2/>

### Image::processor()

Do the processing(crop/resize and manipulations) in the background
