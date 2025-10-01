# Image Handling Enhancement

## Overview
This enhancement adds comprehensive image handling capabilities to the Load HTML Files plugin, addressing issue #3. The plugin now intelligently processes images in imported HTML files to ensure they work seamlessly within WordPress.

## Features

### 1. Download External Images
- Automatically downloads external images to the WordPress Media Library
- Prevents broken links when external sources become unavailable
- Improves page load performance by serving images locally
- Maintains image quality while ensuring proper WordPress integration

### 2. Convert Relative URLs to Absolute
- Converts relative image paths (e.g., `images/photo.jpg`) to absolute URLs
- Handles protocol-relative URLs (e.g., `//example.com/image.jpg`)
- Processes root-relative URLs (e.g., `/uploads/image.jpg`)
- Ensures images display correctly regardless of the page location

### 3. Set Featured Image
- Automatically sets the first image in the content as the post's featured image
- Improves SEO and visual appeal in post listings
- Works with all post types (HTML Files, Posts, Pages)
- Only processes when enabled in settings

### 4. Preserve Image Positions
- Maintains the original placement of images within the content
- Ensures the visual layout remains as intended
- Adds responsive attributes for better mobile experience

## Configuration

The new image handling settings can be found in the plugin's Settings page:

1. Navigate to **HTML Files → Settings**
2. Scroll to the **Image Handling** section
3. Configure the following options:
   - **Download external images to Media Library**: Downloads and stores external images locally
   - **Set first image as featured image**: Uses the first image as the post thumbnail
   - **Preserve original image positions in content**: Maintains image placement
   - **Convert relative image URLs to absolute**: Fixes relative path issues

## Technical Implementation

### Image Processing Flow
1. HTML content is parsed using DOMDocument
2. All `<img>` tags are identified and processed
3. URLs are validated and converted as needed
4. External images are downloaded if enabled
5. First valid image ID is stored for featured image
6. Modified content is returned with updated image URLs

### Performance Considerations
- Images are only downloaded once (duplicate detection)
- Processing happens during import, not on page load
- Responsive srcset attributes are added for optimized delivery
- Failed downloads are handled gracefully without breaking import

### Edge Cases Handled
- Invalid image URLs are skipped
- Large image files are processed with proper timeouts
- Images behind authentication are handled gracefully
- Malformed HTML is parsed safely using libxml error handling

## Benefits

### For Site Administrators
- Centralized media management through WordPress
- Reduced dependency on external image sources
- Improved site performance and reliability
- Better control over image optimization

### For End Users
- Faster page load times with local images
- Consistent image availability
- Better mobile experience with responsive images
- Improved visual presentation with featured images

## Testing

To test the image handling functionality:

1. Enable the desired image handling options in settings
2. Place HTML files with various image types in the unprocessed folder:
   - Files with external images (http/https URLs)
   - Files with relative image paths
   - Files with protocol-relative URLs
3. Wait for the import process or trigger it manually
4. Verify that:
   - External images appear in the Media Library
   - Relative URLs are converted correctly
   - Featured images are set on posts
   - Images display correctly in the imported content

## Compatibility

- Works with all WordPress post types
- Compatible with existing WordPress media handling
- Supports common image formats (JPG, PNG, GIF, WebP, SVG)
- Maintains backward compatibility with existing imports

## Migration

For existing imported content:
- Settings apply only to new imports
- Existing posts are not retroactively modified
- Re-importing HTML files will apply new image settings

## Troubleshooting

### Images Not Downloading
- Check that the external image URLs are accessible
- Verify WordPress has write permissions to the uploads directory
- Ensure sufficient server resources for image processing

### Featured Images Not Setting
- Confirm the setting is enabled
- Verify at least one valid image exists in the content
- Check that the post type supports featured images

### Relative URLs Not Converting
- Ensure the setting is enabled
- Verify the HTML file location is correct
- Check that the base URL can be determined