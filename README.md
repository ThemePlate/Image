# ThemePlate Image

## Usage

```php
use ThemePlate\Image;

Image::register( 'wanted_size', 1920, 1080 );
Image::manipulate( 'wanted_size', 'pixilate', 10 );
Image::get_html( 'attachment_id', 'wanted_size' );

Image::register( 'another_size', 640, 480 );
Image::manipulate( 'another_size', 'greyscale' );
Image::manipulate( 'another_size', 'blur', 20 );
Image::get_url( 'attachment_id', 'another_size' );

$processor = Image::processor();

$processor->report( function( $output ) {
	error_log( print_r( $output, true ) );
} );
```

### Image::register( $name, $width, $height )

- **$name** *(string)(Required)* Size identifier
- **$width** *(int)(Required)* Width in pixels
- **$height** *(int)(Required)* Height in pixels

### Image::manipulate( $size, $filter, $args )

- **$size** *(string)(Required)* Registered size
- **$filter** *(string)(Required)* Filter to apply
- **$args** *(array)(Optional)* Parameters to pass. Default `null`

### Image::get_html( $attachment_id, $size )
### Image::get_url( $attachment_id, $size )

- **$attachment_id** *(int)(Required)* Image attachment ID
- **$size** *(string)(Required)* Valid image size

### Image::processor()

Do the processing(crop/resize and manipulations) in the background

---

> **After v1.1.0**, `Image::get_html` or `Image::get_url` is optional
>
> Simply use the core functions like you normally would
>
> - wp_get_attachment_image
> - wp_get_attachment_image_url
> - the_post_thumbnail
> - get_the_post_thumbnail
> - get_the_post_thumbnail_url
>
> > Given `Image::processor()` is *used and fired* successfully
