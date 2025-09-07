# Usage Guide: Nova Multiselect Field

## Installation

The field is already included in the `dpsoft/nova-multiselect-filter` package. Just build the assets and it's ready to use!

## Basic Usage

```php
use Dpsoft\NovaMultiselectFilter\MultiselectField;

// In your Nova Resource's fields() method:
MultiselectField::make('Categories')
    ->options([
        'tech' => 'Technology',
        'business' => 'Business', 
        'science' => 'Science'
    ])
    ->placeholder('Select categories')
```

## Advanced Options

```php
// Single select mode
MultiselectField::make('Status')
    ->options(['draft' => 'Draft', 'published' => 'Published'])
    ->singleSelect()

// Limit selections
MultiselectField::make('Tags')
    ->options($tags)
    ->max(3)

// Ajax search
MultiselectField::make('Users')
    ->ajaxEndpoint('/api/search-users')
    ->minChars(2)
    ->debounce(300)

// Using built-in search
MultiselectField::make('Categories')
    ->model(\App\Models\Category::class)
    ->searchColumn('name')
```

## Data Storage

The field intelligently handles value storage based on the selection mode:

### Multi-select mode (default):
- **Database**: Values are stored as JSON arrays: `["value1", "value2", "value3"]`
- **PHP Model**: When accessed, automatically decoded to PHP arrays: `['value1', 'value2', 'value3']`
- **Recommended column type**: `json` or `text`

### Single-select mode:
- **Database**: Values are stored as single values: `"value1"`
- **PHP Model**: When accessed, returns the single value: `"value1"`
- **Recommended column type**: `string`, `varchar`, or `text`

### Value Resolution Examples:

```php
// Multi-select field
$article = Article::find(1);
$categories = $article->categories; // Returns: ['tech', 'business', 'science']

// Single-select field  
$status = $article->status; // Returns: 'published'
```

## Migration Example

```php
// For multi-select fields, use JSON column type:
$table->json('categories')->nullable();
// Alternative for older MySQL versions:
// $table->text('categories')->nullable();

// For single-select fields, use string:
$table->string('status')->nullable();

// Example with default values:
$table->json('tags')->default('[]'); // Empty array for multi-select
$table->string('priority')->default('medium'); // Default value for single-select
```

## Working with Values in Your Models

```php
// Multi-select example
class Article extends Model
{
    // The field automatically handles JSON encoding/decoding
    // No need for custom accessors/mutators
    
    public function someMethod()
    {
        // Access categories as array
        $categories = $this->categories; // ['tech', 'business']
        
        // Check if contains specific category
        if (in_array('tech', $categories)) {
            // Do something
        }
    }
}

// Single-select example  
class User extends Model
{
    public function someMethod()
    {
        // Access status as single value
        $status = $this->status; // 'active'
        
        // Direct comparison
        if ($status === 'active') {
            // Do something
        }
    }
}
```
