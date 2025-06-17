<?php
/**
 * This file is part of the ImageTransform package.
 * (c) Christian Schaefer <caefer@ical.ly>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ImageTransform\Tests;

use ImageTransform\Image\GD as Image;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    private Image $image;
    protected function setUp(): void
    {
        $this->image = $this->getMockBuilder(Image::class)
            ->onlyMethods(['create', 'open', 'flush', 'save', 'saveAs', 'initialize'])
            ->getMock();
    }

    /**
     * @covers \ImageTransform\Image::__construct
     */
    public function testNewImage()
    {
        $this->assertInstanceOf(Image::class, $this->image);
    }

    /**
     * @covers \ImageTransform\Image::__construct
     */
    public function testOpeningNewImage()
    {
        $filepath = '/path/to/some/file';
        $this->image->expects($this->once())
            ->method('open')
            ->with($this->equalTo($filepath));
        $this->image->__construct($filepath);
    }

    /**
     * @covers \ImageTransform\Image::get
     * @covers \ImageTransform\Image::set
     */
    public function testAttributeAccess()
    {
        $this->assertFalse($this->image->get('test.value'));
        $this->image->set('test.value', 'barfoo');
        $this->assertEquals('barfoo', $this->image->get('test.value'));
    }
}
