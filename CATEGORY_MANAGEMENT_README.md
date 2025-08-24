# Category Management System

## Overview
The category management system now uses a separate JSON file (`config/categories.json`) to store category information, providing better organization and additional properties for each category.

## Files Structure

### `config/categories.json`
Stores all category data with the following structure:
```json
[
    {
        "id": "1",
        "key": "apron",
        "name": "Apron",
        "description": "Kitchen and cooking aprons",
        "display_name": "Aprons",
        "created_at": "2024-01-01"
    }
]
```

### `config/products.json`
Products now reference categories by their `key` field:
```json
[
    {
        "id": "1",
        "name": "Kitchen Apron",
        "category": "apron",
        "color": "White",
        "image": "assets/img/products/product_123.jpg"
    }
]
```

## Category Properties

| Property | Type | Description |
|----------|------|-------------|
| `id` | string | Unique identifier for the category |
| `key` | string | Internal identifier used in products (lowercase, no spaces) |
| `name` | string | Category name (e.g., "Apron") |
| `description` | string | Brief description of the category |
| `display_name` | string | Name shown to users on frontend (e.g., "Aprons") |
| `created_at` | string | Date when category was created |

## Admin Interface Features

### Add New Category
1. Go to "Manage Categories" tab
2. Fill in:
   - **Category Key**: Internal identifier (e.g., "bedding")
   - **Category Name**: Display name (e.g., "Bedding")
   - **Display Name**: Frontend display (e.g., "Bedding")
   - **Description**: Optional description

### Edit Category
1. Click "Edit" on any category card
2. Modify any field
3. Click "Update Category"

### Delete Category
1. Click "Delete" on any category card
2. **Safety Check**: System prevents deletion if category is used by products
3. Must reassign or delete products first

## Frontend Integration

### API Response
The `api/products.php` now returns:
```json
{
    "products": [...],
    "categories": [
        {
            "key": "apron",
            "display_name": "Aprons"
        }
    ]
}
```

### Filter Dropdown
- Automatically populated from categories.json
- Shows display_name to users
- Uses key for filtering logic

## Benefits

✅ **Better Organization**: Separate file for category management
✅ **Rich Metadata**: Description, display names, creation dates
✅ **Safety Checks**: Prevents deletion of categories in use
✅ **Flexible Display**: Different names for admin vs frontend
✅ **Easy Maintenance**: Centralized category management

## Usage Examples

### Adding a New Category
1. **Admin Panel**: Go to "Manage Categories"
2. **Fill Form**:
   - Key: `outdoor`
   - Name: `Outdoor`
   - Display Name: `Outdoor Textiles`
   - Description: `Outdoor furniture covers and accessories`
3. **Save**: Category appears in dropdowns immediately

### Using Categories in Products
1. **Add Product**: Go to "Add New Product"
2. **Select Category**: Choose from dropdown (shows display_name)
3. **Save**: Product uses category key internally

### Frontend Display
- **Filter Dropdown**: Shows "Outdoor Textiles" (display_name)
- **Product Cards**: Show category display name
- **Filtering**: Works with category key internally

## File Permissions
Ensure the following files are writable:
- `config/categories.json`
- `config/products.json`
- `assets/img/products/` (for image uploads)
