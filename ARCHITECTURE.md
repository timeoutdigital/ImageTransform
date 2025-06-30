# ImageTransform Architecture Documentation

## Overview

ImageTransform is a PHP image manipulation library that provides a flexible, extensible framework for applying transformations to images. The library employs a strategy pattern with dual backend support, allowing developers to choose between PHP GD and ImageMagick implementations based on their specific requirements and available system resources.

## Core Architecture

### High-Level System Design

```mermaid
graph TB
    A[Client Application] --> B[ImageTransform\Transformation]
    B --> C[Image Backend Strategy]
    C --> D[ImageTransform\Image\GD]
    C --> E[ImageTransform\Image\ImageMagick]
    D --> F[PHP GD Extension]
    E --> G[ImageMagick Extension]
    
    B --> H[Transformation Strategy]
    H --> I[ImageTransform\Transformation\Resizer\GD]
    H --> J[ImageTransform\Transformation\Resizer\ImageMagick]
    
    K[FileAccessAdapter] --> D
    K --> E
    
    style A fill:#e1f5fe
    style B fill:#f3e5f5
    style C fill:#fff3e0
    style H fill:#fff3e0
    style K fill:#e8f5e8
```

### C4 Context Diagram

```mermaid
C4Context
    title System Context Diagram for ImageTransform

    Person(developer, "PHP Developer", "Develops applications requiring image manipulation")
    System(imagetransform, "ImageTransform Library", "PHP image manipulation library")
    
    System_Ext(gd, "PHP GD Extension", "Built-in PHP image processing")
    System_Ext(imagemagick, "ImageMagick", "External image processing library")
    System_Ext(filesystem, "File System", "Local or remote file storage")
    
    Rel(developer, imagetransform, "Uses", "PHP API")
    Rel(imagetransform, gd, "Processes images via", "PHP GD functions")
    Rel(imagetransform, imagemagick, "Processes images via", "ImageMagick API")
    Rel(imagetransform, filesystem, "Reads/Writes", "Image files")
```

### C4 Container Diagram

```mermaid
C4Container
    title Container Diagram for ImageTransform

    Container(transformation, "Transformation Manager", "PHP Class", "Orchestrates image transformations using fluent API")
    Container(image_abstraction, "Image Abstraction Layer", "PHP Abstract Class", "Provides unified interface for image operations")
    Container(gd_implementation, "GD Implementation", "PHP Class", "Concrete implementation using PHP GD")
    Container(imagemagick_implementation, "ImageMagick Implementation", "PHP Class", "Concrete implementation using ImageMagick")
    Container(resizer, "Resizer Transformations", "PHP Classes", "Implements image resizing logic with various options")
    Container(file_adapter, "File Access Adapter", "PHP Interface", "Defines file I/O operations")
    
    Rel(transformation, image_abstraction, "Uses")
    Rel(image_abstraction, gd_implementation, "Implemented by")
    Rel(image_abstraction, imagemagick_implementation, "Implemented by")
    Rel(transformation, resizer, "Delegates to")
    Rel(gd_implementation, file_adapter, "Implements")
    Rel(imagemagick_implementation, file_adapter, "Implements")
```

## Core Components

### 1. Transformation Layer

**Class**: `ImageTransform\Transformation`

The central orchestrator that manages the transformation pipeline through a fluent API.

#### Key Responsibilities:
- Maintains a stack of transformations to be applied
- Provides method chaining capabilities
- Manages transformation registration and callback resolution
- Processes the transformation stack against Image instances

#### Key Methods:
```php
// Dynamic method calling for registered transformations
public function __call($method, $arguments)

// Process transformation stack
public function process(\ImageTransform\Image $image)

// Register new transformation callbacks
public static function addTransformation($transformation)
```

#### Process Flow:
```mermaid
sequenceDiagram
    participant Client
    participant Transformation
    participant TransformationStack
    participant ImageBackend
    
    Client->>Transformation: resize(100, 100)
    Transformation->>TransformationStack: push(resize, [100, 100])
    Client->>Transformation: process(image)
    Transformation->>TransformationStack: foreach transformation
    loop Each Transformation
        Transformation->>ImageBackend: call_user_func_array(callback, args)
        ImageBackend-->>Transformation: modified image
    end
    Transformation-->>Client: processed image
```

### 2. Image Abstraction Layer

**Base Class**: `ImageTransform\Image`

Provides a unified interface for image operations regardless of the underlying backend implementation.

#### Key Responsibilities:
- Abstract image resource management
- Attribute storage for image metadata
- Initialization delegation to concrete implementations

#### Concrete Implementations:

##### GD Implementation (`ImageTransform\Image\GD`)
- Utilizes PHP's built-in GD extension
- Supports JPEG, PNG, and GIF formats
- Lightweight and widely available

##### ImageMagick Implementation (`ImageTransform\Image\ImageMagick`)
- Leverages ImageMagick's advanced capabilities
- Broader format support
- Enhanced image quality and feature set

```mermaid
classDiagram
    class Image {
        <<abstract>>
        +array attributes
        +__construct(filepath)
        +get(key, default)
        +set(key, value)
        +initialize()* abstract
    }
    
    class FileAccessAdapter {
        <<interface>>
        +create(width, height)
        +open(filepath)
        +flush(mimeType)
        +save()
        +saveAs(filepath, mimeType)
    }
    
    class GD {
        +initialize()
        +create(width, height)
        +open(filepath)
        +flush(mimeType)
        +save()
        +saveAs(filepath, mimeType)
        -out(filepath, mimeType)
    }
    
    class ImageMagick {
        +initialize()
        +create(width, height)
        +open(filepath)
        +flush(mimeType)
        +save()
        +saveAs(filepath, mimeType)
        -checkResource()
        -syncMimeType(resource, mimeType)
    }
    
    Image <|-- GD
    Image <|-- ImageMagick
    FileAccessAdapter <|.. GD
    FileAccessAdapter <|.. ImageMagick
```

### 3. Transformation Implementations

#### Resizer Transformation

**Base Class**: `ImageTransform\Transformation\Resizer`

Handles image resizing with sophisticated dimension calculation logic.

##### Resize Flags:
- `PROPORTIONAL`: Maintains aspect ratio
- `NO_INFLATE`: Prevents enlarging images beyond original size
- `NO_DEFLATE`: Prevents reducing images below target size
- `MINIMUM`: Uses minimum dimension for proportional scaling

##### Algorithm Flow:
```mermaid
flowchart TD
    A[Start Resize] --> B{Proportional?}
    B -->|No| C{No Inflate & No Deflate?}
    B -->|Yes| D[Calculate Scale Factors]
    C -->|Yes| E[Keep Original Dimensions]
    C -->|No| F{No Inflate Only?}
    F -->|Yes| G[Use Minimum of Original/Target]
    F -->|No| H{No Deflate Only?}
    H -->|Yes| I[Use Maximum of Original/Target]
    H -->|No| J[Use Target Dimensions]
    D --> K{Same Ratio?}
    K -->|Yes| L[Use Target Dimensions]
    K -->|No| M{Width Factor < Height Factor?}
    M --> N[Scale by Appropriate Factor]
    E --> O[Apply Resize]
    G --> O
    I --> O
    J --> O
    L --> O
    N --> O
    O --> P[End]
```

## File I/O Architecture

### FileAccessAdapter Interface

Standardizes file operations across different image backends:

```mermaid
graph LR
    A[FileAccessAdapter Interface] --> B[create]
    A --> C[open]
    A --> D[flush]
    A --> E[save]
    A --> F[saveAs]
    
    B --> G[Create new image resource]
    C --> H[Load image from file]
    D --> I[Output to stdout]
    E --> J[Save to original location]
    F --> K[Save to specified location]
```

#### Supported Operations:
- **create(width, height)**: Generate blank image canvas
- **open(filepath)**: Load existing image file
- **flush(mimeType)**: Stream image to stdout
- **save()**: Overwrite original file
- **saveAs(filepath, mimeType)**: Save with new name/format

## Technology Stack

### Core Dependencies

| Component | Version | Purpose |
|-----------|---------|---------|
| PHP | ≥5.3.2 | Runtime environment |
| GD Extension | - | Basic image processing |
| ImageMagick Extension | - | Advanced image processing |
| Symfony UniversalClassLoader | - | PSR-0 autoloading |

### Development Dependencies

| Tool | Purpose |
|------|---------|
| PHPUnit | Unit testing framework |
| Apache Ant | Build automation |
| PHPMD | Mess detection |
| PHPCPD | Copy/paste detection |
| PHPCS | Code style checking |
| PHPDoc | API documentation |

### Build Pipeline

```mermaid
graph LR
    A[Source Code] --> B[Clean Build Directory]
    B --> C[Parallel Quality Checks]
    C --> D[PHPMD<br/>Mess Detection]
    C --> E[PHPCPD<br/>Copy/Paste Detection]
    C --> F[PHPCS<br/>Code Standards]
    C --> G[PHPDoc<br/>API Documentation]
    C --> H[PHPLOC<br/>Lines of Code]
    C --> I[pdepend<br/>Dependency Analysis]
    D --> J[Unit Tests]
    E --> J
    F --> J
    G --> J
    H --> J
    I --> J
    J --> K[Code Browser Report]
    K --> L[Build Artifacts]
```

## Extension Points

### Creating Custom Transformations

The library supports custom transformations through the registration system:

```php
// 1. Create transformation class
class CustomTransformation
{
    public function customEffect(\ImageTransform\Image $image, $param1, $param2)
    {
        // Implementation logic
        return $image;
    }
}

// 2. Register transformation
Transformation::addTransformation(new CustomTransformation());

// 3. Use in fluent API
$transformation = new Transformation();
$transformation->customEffect($param1, $param2);
```

### Adding New Image Backends

Extend the `Image` class and implement `FileAccessAdapter`:

```php
class NewBackend extends \ImageTransform\Image implements \ImageTransform\FileAccessAdapter
{
    protected function initialize()
    {
        // Backend-specific initialization
    }
    
    // Implement all FileAccessAdapter methods
}
```

## Performance Considerations

### Backend Selection Guidelines

| Scenario | Recommended Backend | Rationale |
|----------|-------------------|-----------|
| Simple operations, minimal dependencies | GD | Lightweight, built-in |
| Complex transformations, quality critical | ImageMagick | Superior algorithms |
| High-volume processing | ImageMagick | Better performance for batch operations |
| Memory-constrained environments | GD | Lower memory footprint |

### Memory Management

- Both backends handle resource cleanup automatically
- Large images should be processed in chunks where possible
- Consider streaming for web output to reduce memory usage

## Error Handling

### Exception Hierarchy

```mermaid
graph TD
    A[Standard PHP Exceptions] --> B[RuntimeException]
    A --> C[InvalidArgumentException]
    A --> D[UnexpectedValueException]
    A --> E[BadMethodCallException]
    A --> F[OutOfBoundsException]
    
    B --> G[Missing Extensions]
    C --> H[Invalid File Paths]
    D --> I[Unsupported Formats]
    E --> J[Unregistered Transformations]
    F --> K[Invalid Transformation Methods]
```

### Error Recovery Strategies

1. **Graceful Degradation**: Fall back to alternative backends
2. **Validation**: Check file accessibility before processing
3. **Format Detection**: Verify MIME types before operations
4. **Resource Checks**: Validate image resources exist

## Security Considerations

### Input Validation

- File path sanitization required for user inputs
- MIME type validation prevents malicious uploads
- Resource limits should be enforced for memory usage

### File Access Controls

- Implement proper permissions checking
- Validate file extensions against allowed types
- Consider implementing file size limits

## Integration Patterns

### Basic Usage Pattern

```php
// Setup
$image = new \ImageTransform\Image\GD('source.jpg');
$transformation = new \ImageTransform\Transformation();

// Register transformations
\ImageTransform\Transformation::addTransformation(new \ImageTransform\Transformation\Resizer\GD());

// Apply transformations
$transformation
    ->resize(800, 600, Resizer::PROPORTIONAL)
    ->process($image);

// Output
$image->saveAs('output.jpg');
```

### Batch Processing Pattern

```php
$files = glob('images/*.jpg');
$transformation = new \ImageTransform\Transformation();
$transformation->resize(200, 200, Resizer::PROPORTIONAL | Resizer::MINIMUM);

foreach ($files as $file) {
    $image = new \ImageTransform\Image\GD($file);
    $transformation->process($image);
    $image->saveAs('thumbnails/' . basename($file));
}
```

### Web Service Integration

```php
// Stream directly to browser
$image = new \ImageTransform\Image\GD($_GET['image']);
$transformation = new \ImageTransform\Transformation();
$transformation->resize($_GET['width'], $_GET['height']);
$transformation->process($image);

header('Content-Type: image/jpeg');
$image->flush('image/jpeg');
```

## API Reference Summary

### Main Classes

| Class | Purpose | Key Methods |
|-------|---------|-------------|
| `Transformation` | Orchestration | `__call()`, `process()`, `addTransformation()` |
| `Image\GD` | GD Backend | `create()`, `open()`, `save()`, `flush()` |
| `Image\ImageMagick` | ImageMagick Backend | `create()`, `open()`, `save()`, `flush()` |
| `Transformation\Resizer` | Resize Logic | `resize()`, `computeFinalDimension()` |

### Interfaces

| Interface | Purpose | Methods |
|-----------|---------|---------|
| `FileAccessAdapter` | File Operations | `create()`, `open()`, `flush()`, `save()`, `saveAs()` |

This architecture provides a solid foundation for image manipulation while maintaining flexibility for future enhancements and extensions.