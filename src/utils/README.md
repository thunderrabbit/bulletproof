### bulletproof\utils

Contains separate, stand-alone functions to crop, resize or watermark images

Install
-----
open `utils/` folder and require the function you need.

#### use crop function
```php
require_once 'src/utils/func.image-crop.php';

/**
 * $image : full image path
 * $mime : the mime type of the image
 * $width : the image width
 * $height : the image height
 * $newWidth : the new width of the image
 * $newHeight : the new height of the image:
 */
$crop = bulletproof\utils\crop($image, $mime, $width, $height, $newWidth, $newHeight);
```
##### crop function example
```php
require_once 'src/utils/func.image-crop.php';
// call the function and pass the right arguments.
$crop = Bulletproof\Utils\crop(
	'images/my-car.jpg', 'jpg', 100, 200, 50, 25
);
// now 'images/my-car.jpg' is cropped to 50x25 pixels.
```

### with bulletproof

If you want to use these function with [bulletproof][bulletproof], here are some examples:

#### Resizing
```php
// include bulletproof and the function you need.
require "src/bulletproof.php";
require "src/utils/func.image-resize.php";

// after you upload the image, call the function
$image = new Bulletproof\Image($_FILES);

if($image["picture"]){
	$upload = $image->upload();

	if($upload){
		Bulletproof\Utils\resize(
			$image->getFullPath(),
			$image->getMime(),
			$image->getWidth(),
			$image->getHeight(),
			50,
			50
	 );
	}
}
```

#### Cropping
The `crop()` function supports resizing by ratio, check out the file for more.
```php
require "src/utils/func.image-crop.php";

$crop = Bulletproof\Utils\crop(
	$upload->getFullPath(),
	$upload->getMime(),
	$upload->getWidth(),
	$upload->getHeight(),
	50,
	50
);

```
#### Watermark
The `watermark()` function allows adding a watermark into an image

```php
require 'src/utils/func.image-watermark.php';
// the image to watermark
$logo = 'my-logo.png';
// where to place the watermark
$position = 'center';
// get the width and height of the logo
list($logoWidth, $logoHeight) = getimagesize($logo);

$watermark = Bulletproof\watermark(
	$upload->getFullPath(),
	$upload->getMime(),
	$upload->getWidth(),
	$upload->getHeight(),
	$logo,
	$logoHeight,
	$logoWidth,
	$position		
);
```

Contribution
-----

You are encouraged to add functions for other features (ex: add text, rotate images .. )

LICENSE
-----
Check the main [bulletproof][bulletproof] page for the license.


[bulletproof]: https://github.com/samayo/bulletproof
