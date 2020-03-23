# ThemePlate Image

## Usage

```php
use ThemePlate\Image;

Image::register( 'wanted_size', 1920, 1080 );
Image::manipulate( 'wanted_size', 'pixilate', 10 );
Image::get_html( 'attachment_id', 'wanted_size' );
Image::get_url( 'attachment_id', 'wanted_size' );
```
