<?php
/**
 * This file is part of the ImageTransform package.
 * (c) Christian Schaefer <caefer@ical.ly>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ImageTransform\Tests;

use ImageTransform\Transformation;
use PHPUnit\Framework\TestCase;
use ImageTransform\Image\GD as Image;

class TransformationTest extends TestCase
{
    /**
     *
     * @covers \ImageTransform\Transformation::getTransformation
     */
    public function testUnaddedTransformation()
    {
        $this->expectException(\OutOfBoundsException::class);
        $methodName = 'dummyCallback';
        Transformation::getTransformation($methodName);
    }

    /**
     * @depends testUnaddedTransformation
     * @covers  \ImageTransform\Transformation::addTransformation
     * @covers  \ImageTransform\Transformation::getTransformation
     */
    public function testAddingTransformation()
    {
        $methodName = 'dummyCallback';

        $stubTransformation = $this->getMockBuilder(DummyClass::class)
            ->onlyMethods([$methodName])
            ->getMock();

        Transformation::addTransformation($stubTransformation);

        $callback = Transformation::getTransformation($methodName);
        $this->assertIsArray($callback);
        $this->assertEquals($stubTransformation, $callback[0]);
        $this->assertEquals($methodName, $callback[1]);
    }

    /**
     * @covers \ImageTransform\Transformation::__call
     * @covers \ImageTransform\Transformation::getStack
     */
    public function testSuccessfulDelegation()
    {
        $methodName = 'dummyCallback';

        $stubTransformation = $this->getMockBuilder(DummyClass::class)
            ->onlyMethods([$methodName])
            ->getMock();

        Transformation::addTransformation($stubTransformation);

        $transformation = new Transformation();

        $this->assertCount(0, $transformation->getStack());
        $transformation->dummyCallback();
        $this->assertCount(1, $transformation->getStack());
        $transformation->dummyCallback();
        $this->assertCount(2, $transformation->getStack());

        return $transformation;
    }

    /**
     * @depends testUnaddedTransformation
     *
     * @covers  \ImageTransform\Transformation::__call
     */
    public function testFailedDelegation()
    {
        $this->expectException(\BadMethodCallException::class);
        $methodName = 'dummyCallback';
        $transformation = new Transformation();
        $transformation->$methodName();
    }

    /**
     * @depends testSuccessfulDelegation
     * @covers  \ImageTransform\Transformation::process
     */
    protected function testSuccessfulProcessing()
    {
        $image = $this->getMockBuilder(Image::class)
            ->onlyMethods(['create', 'open', 'flush', 'save', 'saveAs', 'initialize'])
            ->getMock();

        $transformation = $this->getMockBuilder(Transformation::class)
            ->onlyMethods(['process'])
            ->getMock();

        $transformation->expects($this->once())
            ->method('process')
            ->with($this->equalTo($image));

        $transformation->process($image);

        $this->assertInstanceOf(Image::class, $image);
    }

    /**
     * @covers \ImageTransform\Transformation::__invoke
     */
    public function testSuccessfulProcessingByDirectInvocation()
    {
        $image = $this->getMockBuilder(Image::class)
            ->onlyMethods(['create', 'open', 'flush', 'save', 'saveAs', 'initialize'])
            ->getMock();

        $transformation = $this->getMockBuilder(Transformation::class)
            ->onlyMethods(['process'])
            ->getMock();

        $transformation->expects($this->once())->method('process')->with($this->equalTo($image));

        $transformation($image);
    }

    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(Transformation::class);
        $property = $reflection->getProperty('transformations');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }
}

class DummyClass
{
    public function dummyCallback()
    {
    }
}