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
 * Apply a gamma correction to an image.
 *
 * @category   ImageTransform
 * @package    Transform
 * @subpackage Abstract
 *
 * @author Stuart Lowes <stuart.lowes@gmail.com>
 * @author Jan Schumann <js@schumann-it.com>
 */
abstract class ImageTransform_Transform_Abstract_Gamma extends ImageTransform_Transform_Abstract
{
  /**
   * The input gamma.
   * @var float
  */
  private $input_gamma = 1.0;

  /**
   * The number of pixels used for the blur.
   * @var float
  */
  private $output_gamma = 1.6;

  /**
   * Construct an Gamma object.
   *
   * @param float
   * @param float
   */
  public function __construct($input_gamma=1.0, $output_gamma=1.6)
  {
    $this->setInputGamma($input_gamma);
    $this->setOutputGamma($output_gamma);
  }

  /**
   * Sets the input gamma
   *
   * @param float
   * @return boolean
   */
  private function setInputGamma($gamma)
  {
    if (is_float($gamma))
    {
      $this->input_gamma = (float)$gamma;

      return true;
    }

    return false;
  }

  /**
   * Gets the input gamma
   *
   * @return integer
   */
  protected function getInputGamma()
  {
    return $this->input_gamma;
  }

  /**
   * Sets the ouput gamma
   *
   * @param float
   */
  private function setOutputGamma($gamma)
  {
    if (is_numeric($gamma))
    {
      $this->ouput_gamma = (float)$gamma;

      return true;
    }
  }

  /**
   * Gets the ouput gamma
   *
   * @return integer
   */
  protected function getOuputGamma()
  {
    return $this->ouput_gamma;
  }
}
