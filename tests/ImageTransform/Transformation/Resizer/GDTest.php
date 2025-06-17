<?php
/**
 * This file is part of the ImageTransform package.
 * (c) Christian Schaefer <caefer@ical.ly>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ImageTransform\Tests\Transformation\Resizer;

use ImageTransform\Image\GD as Image;
use ImageTransform\Transformation\Resizer\GD as Resizer;
use PHPUnit\Framework\TestCase;

class GDTest extends TestCase
{
    private Image $image;
    private Resizer $resize;

    protected function setUp(): void
    {
        $width = 10;
        $height = 10;
        $resource = imagecreatetruecolor($width, $height);

        $this->image = $this->getMockBuilder(Image::class)
            ->onlyMethods(['create', 'open', 'flush', 'save', 'saveAs', 'initialize'])
            ->getMock();
        $this->image->set('image.resource', $resource);
        $this->image->set('image.width', $width);
        $this->image->set('image.height', $height);

        $this->resize = new Resizer();
    }

    /**
     * @covers \ImageTransform\Transformation\Resizer\GD::doResize
     */
    public function testResizing()
    {
        $width = 20;
        $height = 20;

        $this->image = $this->resize->resize($this->image, $width, $height);
        $this->assertEquals($width, $this->image->get('image.width'));
        $this->assertEquals($height, $this->image->get('image.height'));

        // Verify that the resource has changed by checking dimensions
        $newResource = $this->image->get('image.resource');
        $this->assertInstanceOf(\GdImage::class, $newResource);

        $newWidth = imagesx($newResource);
        $newHeight = imagesy($newResource);

        $this->assertEquals($width, $newWidth);
        $this->assertEquals($height, $newHeight);
    }
}