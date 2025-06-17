<?php
/**
 * This file is part of the ImageTransform package.
 * (c) Christian Schaefer <caefer@ical.ly>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ImageTransform\Tests\Image;

use PHPUnit\Framework\TestCase;
use ImageTransform\Image\ImageMagick as Image;

class ImageMagickTest extends TestCase
{
    /**
     * @covers \ImageTransform\Image\ImageMagick::initialize
     */
    public function testInitialization()
    {
        if (!extension_loaded('imagick')) {
            $this->setExpectedException('\RuntimeException');
        }

        $image = new Image();
        $this->assertInstanceof('\ImageTransform\Image', $image);
    }

    /**
     * @covers \ImageTransform\Image\ImageMagick::create
     * @covers \ImageTransform\FileAccessAdapter::create
     */
    public function testCreation()
    {
        $width = 100;
        $height = 200;

        $image = new Image();
        $image->create($width, $height);

        $this->assertInstanceOf('\Imagick', $image->get('image.resource'));
        $this->assertEquals($width, $image->get('image.width'));
        $this->assertEquals($height, $image->get('image.height'));
    }

    /**
     * @dataProvider fixtureImages
     * @covers       \ImageTransform\Image\ImageMagick::open
     * @covers       \ImageTransform\FileAccessAdapter::open
     */
    public function testOpening($filepath, $mimeType, $width, $height)
    {
        $image = new Image($filepath);

        $this->assertInstanceOf('\Imagick', $image->get('image.resource'));
        $this->assertEquals($filepath, $image->get('image.filepath'));
        $this->assertEquals($width, $image->get('image.width'));
        $this->assertEquals($height, $image->get('image.height'));
        $this->assertEquals($mimeType, $image->get('image.mime_type'));
    }

    /**
     * @covers \ImageTransform\Image\ImageMagick::open
     * @covers \ImageTransform\FileAccessAdapter::open
     */
    public function testOpeningOfUnreadableFile()
    {
        $this->expectException(\InvalidArgumentException::class);
        $filepath = '/does/not/exist';
        $image = new Image($filepath);
    }

    /**
     * @covers \ImageTransform\Image\ImageMagick::open
     * @covers \ImageTransform\FileAccessAdapter::open
     */
    public function testOpeningOfUnsupportedMimeType()
    {
        $this->expectException(\UnexpectedValueException::class);
        $filepath = __FILE__;
        $image = new Image($filepath);
    }

    /**
     * @dataProvider mimeTypes
     * @covers       \ImageTransform\Image\ImageMagick::flush
     * @covers       \ImageTransform\FileAccessAdapter::flush
     */
    public function testFlushingWithMimeType($mimeType)
    {
        $resource = new \Imagick(__DIR__ . '/../../fixtures/20x20-pattern.jpg');

        $image = $this->getMockBuilder(Image::class)
            ->onlyMethods(['checkResource', 'syncMimeType'])
            ->getMock();
        $image->set('image.resource', $resource);

        $image->expects($this->once())->method('checkResource')->with();
        $image->expects($this->once())->method('syncMimeType')->with($this->anything(), $this->equalTo($mimeType));

        $image->flush($mimeType);
    }

    /**
     * @covers \ImageTransform\Image\ImageMagick::save
     * @covers \ImageTransform\FileAccessAdapter::save
     */
    public function testSaving()
    {
        $filepath = '/path/to/some/file';

        $image = $this->getMockBuilder(Image::class)
            ->onlyMethods(['saveAs'])
            ->getMock();
        $image->expects($this->once())->method('saveAs')->with($this->equalTo($filepath), $this->isFalse());
        $image->set('image.filepath', $filepath);

        $image->save();
    }

    /**
     * @covers \ImageTransform\Image\ImageMagick::save
     * @covers \ImageTransform\FileAccessAdapter::save
     */
    public function testSavingWithNoFilepath()
    {
        $this->expectException(\InvalidArgumentException::class);
        $image = $this->getMockBuilder(Image::class)
            ->onlyMethods(['saveAs'])
            ->getMock();
        $image->save();
    }

    /**
     * @dataProvider mimeTypes
     * @covers       \ImageTransform\Image\ImageMagick::saveAs
     * @covers       \ImageTransform\FileAccessAdapter::saveAs
     */
    public function testSavingAsWithMimeType($mimeType)
    {
        $resource = new \Imagick(__DIR__ . '/../../fixtures/20x20-pattern.jpg');

        $image = $this->getMockBuilder(Image::class)
            ->onlyMethods(['syncMimeType'])
            ->getMock();
        $image->set('image.resource', $resource);

        $image->expects($this->once())->method('syncMimeType')->with($this->anything(), $this->equalTo($mimeType));

        $targetFilepath = tempnam(sys_get_temp_dir(), 'image');
        $image->saveAs($targetFilepath, $mimeType);
        @unlink($targetFilepath);
    }

    /**
     * @dataProvider fixtureImages
     * @covers       \ImageTransform\Image\ImageMagick::saveAs
     * @covers       \ImageTransform\FileAccessAdapter::saveAs
     */
//    public function testSavingAtGivenUnwritableFilepath($filepath, $mimeType, $width, $height)
//    {
//        $this->expectException(\InvalidArgumentException::class);
//        $resource = new \Imagick(__DIR__ . '/../../fixtures/20x20-pattern.jpg');
//        $mimeType = 'image/jpg';
//
//
//        $image = $this->getMockBuilder(Image::class)
//            ->onlyMethods(['saveAs'])
//            ->getMock();
//        $image->set('image.resource', $resource);
//        $image->set('image.mime_type', $mimeType);
//
//        $image->saveAs('/does/not/exist');
//    }

    /**
     * @dataProvider fixtureImages
     * @covers       \ImageTransform\Image\ImageMagick::flush
     * @covers       \ImageTransform\Image\ImageMagick::checkResource
     * @covers       \ImageTransform\Image\ImageMagick::syncMimeType
     */
    public function testFlushingDifferentMimeTypes($filepath, $mimeType, $width, $height)
    {
        $image = new Image($filepath);

        ob_start();
        $image->flush();
        $this->assertNotEmpty(ob_get_contents());
        ob_end_clean();
    }

    /**
     * @covers \ImageTransform\Image\ImageMagick::checkResource
     */
    public function testFlushingWhenNoResourceIsSet()
    {
        $this->expectException(\UnexpectedValueException::class);
        $image = new Image();
        $image->flush();
    }

    /**
     * @covers \ImageTransform\Image\ImageMagick::syncMimeType
     */
    public function testFlushingUnsupportedMimeType()
    {
        $this->expectException(\UnexpectedValueException::class);
        $image = new Image();
        $image->set('image.resource', __FILE__);

        $image->flush('mime/unsupported');
    }

    public static function mimeTypes(): array
    {
        return [
            [false],
            ['image/gif'],
            ['image/jpeg'],
            ['image/png']
        ];
    }

    public static function fixtureImages()
    {
        return [
            [__DIR__ . '/../../fixtures/20x20-pattern.gif', 'image/gif', 20, 20],
            [__DIR__ . '/../../fixtures/20x20-pattern.jpg', 'image/jpeg', 20, 20],
            [__DIR__ . '/../../fixtures/20x20-pattern.png', 'image/png', 20, 20]
        ];
    }
}
