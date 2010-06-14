<?php
/**
 * This file is part of the ImageTransform package.
 * (c) 2007 Stuart Lowes <stuart.lowes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @category   ImageTransform
 * @package    Transform
 * @version    $Id:$
 */

/**
 * generic thumbnail transform
 *
 * Create a thumbnail 100 x 100, with the image resized to fit
 * <code>
 * <?php
 * $img = new ImageTransform_Source('image1.jpg');
 * $img->thumbnail(100, 100);
 * $img->setQuality(50);
 * $img->saveAs('thumbnail.png');
 * ?>
 * </code>
 *
 * @category   ImageTransform
 * @package    Transform
 * @subpackage Abstract
 *
 * @author Stuart Lowes <stuart.lowes@gmail.com>
 * @author Miloslav Kmet <miloslav.kmet@gmail.com>
 * @author Jan Schumann <js@schumann-it.com>
 */
abstract class ImageTransform_Transform_Abstract_Thumbnail extends ImageTransform_Transform_Abstract
{
  /**
   * width of the thumbnail
   */
  private $width;

  /**
   * height of the thumbnail
   */
  private $height;

  /**
   * method to be used for thumbnail creation. default is scale.
   */
  private $method = 'fit';

  /**
   * available methods for thumbnail creation
   */
  private $methods = array('fit', 'scale', 'inflate','deflate', 'left' ,'right', 'top', 'bottom', 'center');

  /*
   * background color in hex or null for transparent
   */
  private $background = null;

  /**
   * constructor
   *
   * @param integer $width of the thumbnail
   * @param integer $height of the thumbnail
   * @param string type of thumbnail method to be created
   *
   * @return void
   */
  public function __construct($width, $height, $method='fit', $background=null)
  {
    if(!$this->setWidth($width) || !$this->setHeight($height))
		{
			throw new ImageTransform_Transform_Exception(sprintf('Cannot perform thumbnail, a valid width and height must be supplied'));
		}
    $this->setMethod($method);
    $this->setBackground($background);
  }

  /**
   * sets the height of the thumbnail
   * @param integer $height of the image
   *
   * @return void
   */
  private function setHeight($height)
  {
    if(is_numeric($height) && $height > 0)
    {
      $this->height = (int)$height;

      return true;
    }

    return false;
  }

  /**
   * returns the height of the thumbnail
   *
   * @return integer
   */
  protected function getHeight()
  {
    return $this->height;
  }

  /**
   * sets the width of the thumbnail
   * @param integer $width of the image
   *
   * @return void
   */
  private function setWidth($width)
  {
    if(is_numeric($width) && $width > 0)
    {
      $this->width = (int)$width;

      return true;
    }

    return false;
  }

  /**
   * returns the width of the thumbnail
   *
   * @return integer
   */
  protected function getWidth()
  {
    return $this->width;
  }

  /**
   * returns the width of the thumbnail
   * @param string thumbnail method. Options are scale (default), deflate (or inflate), right, left, top, bottom, scale
   *
   * @return integer
   */
  private function setMethod($method)
  {

    if(in_array($method, $this->methods))
    {
      $this->method = strtolower($method);

      return true;
    }

    return false;
  }

  /**
   * returns the method for thumbnail creation
   *
   * @return integer
   */
  protected function getMethod()
  {
    return $this->method;
  }

  /**
   * Sets background color.
   *
   * @param string
   */
  private function setBackground($color)
  {
    $this->background = $color;
  }

  /**
   * Gets background color.
   *
   * @return string
   */
  protected function getBackground()
  {
    return $this->background;
  }
}
