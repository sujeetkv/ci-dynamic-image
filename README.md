# ci-dynamic-image
## CodeIgniter Dynamic Image Library
Resize and Crop images on-the-fly.

-by Sujeet <sujeetkv90@gmail.com>

*Uses effective Browser-Server caching to overcome repeated image processing.*

Usage Examples:

With Url-Rewrite (Recommended):
```html
<img src="app_images/index/assets/images/image.jpg" />
<img src="app_images/index/150x200/assets/images/image.jpg" />
<img src="app_images/index/150x200-c/assets/images/image.jpg" />
<img src="app_images/index/150x200-r/assets/images/image.jpg" />
<img src="app_images/index/150x200-c-r/assets/images/image.jpg" />
```

Without Url-Rewrite:
```html
<img src="index.php/app_images/index/assets/images/image.jpg" />
<img src="index.php/app_images/index/150x200/assets/images/image.jpg" />
<img src="index.php/app_images/index/150x200-c/assets/images/image.jpg" />
<img src="index.php/app_images/index/150x200-r/assets/images/image.jpg" />
<img src="index.php/app_images/index/150x200-c-r/assets/images/image.jpg" />
```

Resize Options:

Example | Description
--- | ---
`150x200` | `width`x`height`
`150x200-c` | `width`x`height`-`crop`
`150x200-r` | `width`x`height`-`maintain_ratio`
`150x200-c-r` | `width`x`height`-`crop`-`maintain_ratio`

