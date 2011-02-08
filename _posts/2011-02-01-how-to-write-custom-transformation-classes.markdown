---
layout: default
kicker: DOCUMENTATION
title: How to write custom transformation classes
category: documentation
---

Transformation classes are plain old PHP objects (POPOs) that perform a specific operation on an Image instance.

The following example demonstrates some best practices and conventions of how to implement a custom transformation and how to use it.

## Example

Let's implement a _colorize_ transformation.

### 1. First step is to create a new _abstract_ class in the `\ImageTransform\Transformation` namespace.

    {% highlight php %}
    <?php
    
    namespace \ImageTransform\Transformation;

    abstract class Colorizer
    {
      public function colorize(\ImageTransform\Image $image, $color = false)
      {
        $color = $this->validate($color);
        $image->set('image.overlay_color', $color);
        return $this->doOperate($image, $color);
      }

      protected function validate($color)
      {
        // do some validation
        return $color;
      }
    }
    {% endhighlight %}

<br/><br/>

* A good naming convention is to name this class as you would call a person executing its task i.e. to `colorize` is what a `Colorizer` does.
* This class must be completely stateless! Only use local variables and parameters, no member variables as one instance might be executed multiple times.
* The public methods defined in the abstract class will be available on any `Transformation` instance via delegation after it is being registered.
* All public methods must accept an instance of `\ImageTransform\Image` as their first argument!
* All public methods must return the modified `\ImageTransform\Image` instance to maintain a fluent interface!
* In this class must be no concrete image-api (GD, ImageMagick, ..) dependent code!
* Call concrete implementation by prefixing the public methods name with _do_ i.e. abstract: `colorize()` => concrete: `doColorize()`.
* Implement image-api independent functionality within protected methods.
* Use `$image->set(key, value)` to store information about the processed image like its dimensions.

### 2. Second step is to extend this abstract class for each image-api in the `\ImageTransform\Transformation\Colorizer` namespace

One for GD.

    {% highlight php %}
    <?php

    namespace \ImageTransform\Transformation\Colorizer;

    use \ImageTransform\Transformation\Colorizer;

    class GD extends Colorizer
    {
      protected function doColorize(\ImageTransform\Image $image, $color = false)
      {
        // implement GD specific colorizing
        return $image;
      }
    }
    {% endhighlight %}
    
<br/><br/>

One for ImageMagick.

    {% highlight php %}
    <?php
    
    namespace \ImageTransform\Transformation\Colorizer;

    use \ImageTransform\Transformation\Colorizer;

    class ImageMagick extends Colorizer
    {
      protected function doColorize(\ImageTransform\Image $image, $color = false)
      {
        // implement ImageMagick specific colorizing
        return $image;
      }
    }
    {% endhighlight %}

<br/><br/>

* Concrete implementations live in their parents namespace.
* They take the name of the image-api they use internally.
* Concrete classes must not define public methods!
* These classes as well must be completely stateless! Only use local variables and parameters, no member variables as one instance might be executed multiple times.
* Both implementations must be functional equivalent!
* All generic code belongs in the abstract parent class.

## Usage

You can register your transformation class by passing an instance to `Transformation`.

    {% highlight php %}
    <?php
    
    // You can use the GD version
    use \ImageTransform\Transformation\Colorizer\GD as Colorizer;
    // or use the ImageMagick version
    use \ImageTransform\Transformation\Colorizer\ImageMagick as Colorizer;

    // Registering Resize
    Transformation::addTransformation(new Colorizer());
    {% endhighlight %}

<br/><br/>

After registering this transformation during the bootstrap of your application you can simply use it like this.

    {% highlight php %}
    <?php
    
    $image = new Image('/path/to/image.jpg');

    $transformation = new Transformation();
    $transformation->colorize('#ff0000')
                   ->process($image);

    $image->save();
    {% endhighlight %}

<br/><br/>

* When calling transformations their first parameter of type `\ImageTransform\Image` is omitted! (i.e. `->colorize('#ff0000')`)
* Transformations can be chained. (i.e. `->colorize('#ff0000')->process($image)`)
* Calling a transformation will only schedule it for later execution. (i.e. `->colorize('#ff0000')`)
* Transformations will be executed when `process()` is called. (i.e. `->process($image)`)<br/>This way a chain of transformations can be configured once and executed multiple times.
