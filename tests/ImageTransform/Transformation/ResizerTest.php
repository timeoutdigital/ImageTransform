<?php

namespace ImageTransfrom\Test\Transformation\Resizer;

use ImageTransform\Image\GD as Image;
use ImageTransform\Transformation\Resizer;
use PHPUnit\Framework\TestCase;

class ResizerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->image = $this->getMockBuilder(Image::class)
            ->onlyMethods(['create', 'open', 'flush', 'save', 'saveAs', 'initialize'])
            ->getMock();

        $this->resize = $this->getMockBuilder(Resizer::class)
            ->onlyMethods(['doResize'])
            ->getMock();

        $this->resize->expects($this->any())->method('doResize')->will($this->returnValue(true));
    }

    /**
     * @dataProvider transformations
     * @covers       \ImageTransform\Transformation\Resizer::resize
     * @covers       \ImageTransform\Transformation\Resizer::computeFinalDimension
     */
    public function testComputingDimensions($sourceDimensions, $targetDimensions, $resultDimensions, $options)
    {
        $this->image->set('image.width', $sourceDimensions[0]);
        $this->image->set('image.height', $sourceDimensions[1]);

        $this->resize->resize($this->image, $targetDimensions[0], $targetDimensions[1], $options);

        $this->assertEquals($resultDimensions[0], $this->image->get('image.width'));
        $this->assertEquals($resultDimensions[1], $this->image->get('image.height'));
    }

    public static function transformations()
    {
        return [
            [[10, 10], [16, 24], [16, 16], Resizer::PROPORTIONAL],
            [[10, 10], [16, 24], [24, 24], Resizer::PROPORTIONAL | Resizer::MINIMUM],
            [[10, 10], [16, 24], [16, 16], Resizer::PROPORTIONAL | Resizer::NO_DEFLATE],
            [[10, 10], [16, 24], [24, 24], Resizer::PROPORTIONAL | Resizer::NO_DEFLATE | Resizer::MINIMUM],
            [[10, 10], [16, 24], [10, 10], Resizer::PROPORTIONAL | Resizer::NO_INFLATE],
            [[10, 10], [16, 24], [10, 10], Resizer::PROPORTIONAL | Resizer::NO_INFLATE | Resizer::MINIMUM],
            [[10, 10], [16, 24], [10, 10], Resizer::PROPORTIONAL | Resizer::NO_INFLATE | Resizer::NO_DEFLATE],
            [[10, 10], [16, 24], [10, 10], Resizer::PROPORTIONAL | Resizer::NO_INFLATE | Resizer::NO_DEFLATE | Resizer::MINIMUM],
            [[10, 10], [16, 24], [16, 24], 0],
            [[10, 10], [16, 24], [16, 24], Resizer::MINIMUM],
            [[10, 10], [16, 24], [16, 24], Resizer::NO_DEFLATE],
            [[10, 10], [16, 24], [16, 24], Resizer::NO_DEFLATE | Resizer::MINIMUM],
            [[10, 10], [16, 24], [10, 10], Resizer::NO_INFLATE],
            [[10, 10], [16, 24], [10, 10], Resizer::NO_INFLATE | Resizer::MINIMUM],
            [[10, 10], [16, 24], [10, 10], Resizer::NO_INFLATE | Resizer::NO_DEFLATE],
            [[10, 10], [16, 24], [10, 10], Resizer::NO_INFLATE | Resizer::NO_DEFLATE | Resizer::MINIMUM],

            [[16, 24], [10, 10], [7, 10], Resizer::PROPORTIONAL],
            [[16, 24], [10, 10], [10, 15], Resizer::PROPORTIONAL | Resizer::MINIMUM],
            [[16, 24], [10, 10], [16, 24], Resizer::PROPORTIONAL | Resizer::NO_DEFLATE],
            [[16, 24], [10, 10], [16, 24], Resizer::PROPORTIONAL | Resizer::NO_DEFLATE | Resizer::MINIMUM],
            [[16, 24], [10, 10], [7, 10], Resizer::PROPORTIONAL | Resizer::NO_INFLATE],
            [[16, 24], [10, 10], [10, 15], Resizer::PROPORTIONAL | Resizer::NO_INFLATE | Resizer::MINIMUM],
            [[16, 24], [10, 10], [16, 24], Resizer::PROPORTIONAL | Resizer::NO_INFLATE | Resizer::NO_DEFLATE],
            [[16, 24], [10, 10], [16, 24], Resizer::PROPORTIONAL | Resizer::NO_INFLATE | Resizer::NO_DEFLATE | Resizer::MINIMUM],
            [[16, 24], [10, 10], [10, 10], 0],
            [[16, 24], [10, 10], [10, 10], Resizer::MINIMUM],
            [[16, 24], [10, 10], [16, 24], Resizer::NO_DEFLATE],
            [[16, 24], [10, 10], [16, 24], Resizer::NO_DEFLATE | Resizer::MINIMUM],
            [[16, 24], [10, 10], [10, 10], Resizer::NO_INFLATE],
            [[16, 24], [10, 10], [10, 10], Resizer::NO_INFLATE | Resizer::MINIMUM],
            [[16, 24], [10, 10], [16, 24], Resizer::NO_INFLATE | Resizer::NO_DEFLATE],
            [[16, 24], [10, 10], [16, 24], Resizer::NO_INFLATE | Resizer::NO_DEFLATE | Resizer::MINIMUM],

            [[16, 8], [10, 10], [10, 5], Resizer::PROPORTIONAL],
            [[16, 8], [10, 10], [20, 10], Resizer::PROPORTIONAL | Resizer::MINIMUM],
            [[16, 8], [10, 10], [16, 8], Resizer::PROPORTIONAL | Resizer::NO_DEFLATE],
            [[16, 8], [10, 10], [20, 10], Resizer::PROPORTIONAL | Resizer::NO_DEFLATE | Resizer::MINIMUM],
            [[16, 8], [10, 10], [10, 5], Resizer::PROPORTIONAL | Resizer::NO_INFLATE],
            [[16, 8], [10, 10], [16, 8], Resizer::PROPORTIONAL | Resizer::NO_INFLATE | Resizer::MINIMUM],
            [[16, 8], [10, 10], [16, 8], Resizer::PROPORTIONAL | Resizer::NO_INFLATE | Resizer::NO_DEFLATE],
            [[16, 8], [10, 10], [16, 8], Resizer::PROPORTIONAL | Resizer::NO_INFLATE | Resizer::NO_DEFLATE | Resizer::MINIMUM],
            [[16, 8], [10, 10], [10, 10], 0],
            [[16, 8], [10, 10], [10, 10], Resizer::MINIMUM],
            [[16, 8], [10, 10], [16, 10], Resizer::NO_DEFLATE],
            [[16, 8], [10, 10], [16, 10], Resizer::NO_DEFLATE | Resizer::MINIMUM],
            [[16, 8], [10, 10], [10, 8], Resizer::NO_INFLATE],
            [[16, 8], [10, 10], [10, 8], Resizer::NO_INFLATE | Resizer::MINIMUM],
            [[16, 8], [10, 10], [16, 8], Resizer::NO_INFLATE | Resizer::NO_DEFLATE],
            [[16, 8], [10, 10], [16, 8], Resizer::NO_INFLATE | Resizer::NO_DEFLATE | Resizer::MINIMUM],

            [[8, 24], [10, 10], [3, 10], Resizer::PROPORTIONAL],
            [[8, 24], [10, 10], [10, 30], Resizer::PROPORTIONAL | Resizer::MINIMUM],
            [[8, 24], [10, 10], [8, 24], Resizer::PROPORTIONAL | Resizer::NO_DEFLATE],
            [[8, 24], [10, 10], [10, 30], Resizer::PROPORTIONAL | Resizer::NO_DEFLATE | Resizer::MINIMUM],
            [[8, 24], [10, 10], [3, 10], Resizer::PROPORTIONAL | Resizer::NO_INFLATE],
            [[8, 24], [10, 10], [8, 24], Resizer::PROPORTIONAL | Resizer::NO_INFLATE | Resizer::MINIMUM],
            [[8, 24], [10, 10], [8, 24], Resizer::PROPORTIONAL | Resizer::NO_INFLATE | Resizer::NO_DEFLATE],
            [[8, 24], [10, 10], [8, 24], Resizer::PROPORTIONAL | Resizer::NO_INFLATE | Resizer::NO_DEFLATE | Resizer::MINIMUM],
            [[8, 24], [10, 10], [10, 10], 0],
            [[8, 24], [10, 10], [10, 10], Resizer::MINIMUM],
            [[8, 24], [10, 10], [10, 24], Resizer::NO_DEFLATE],
            [[8, 24], [10, 10], [10, 24], Resizer::NO_DEFLATE | Resizer::MINIMUM],
            [[8, 24], [10, 10], [8, 10], Resizer::NO_INFLATE],
            [[8, 24], [10, 10], [8, 10], Resizer::NO_INFLATE | Resizer::MINIMUM],
            [[8, 24], [10, 10], [8, 24], Resizer::NO_INFLATE | Resizer::NO_DEFLATE],
            [[8, 24], [10, 10], [8, 24], Resizer::NO_INFLATE | Resizer::NO_DEFLATE | Resizer::MINIMUM],

            [[10, 10], [10, 10], [10, 10], Resizer::PROPORTIONAL],
            [[10, 10], [10, 10], [10, 10], Resizer::PROPORTIONAL | Resizer::MINIMUM],
            [[10, 10], [10, 10], [10, 10], Resizer::PROPORTIONAL | Resizer::NO_DEFLATE],
            [[10, 10], [10, 10], [10, 10], Resizer::PROPORTIONAL | Resizer::NO_DEFLATE | Resizer::MINIMUM],
            [[10, 10], [10, 10], [10, 10], Resizer::PROPORTIONAL | Resizer::NO_INFLATE],
            [[10, 10], [10, 10], [10, 10], Resizer::PROPORTIONAL | Resizer::NO_INFLATE | Resizer::MINIMUM],
            [[10, 10], [10, 10], [10, 10], Resizer::PROPORTIONAL | Resizer::NO_INFLATE | Resizer::NO_DEFLATE],
            [[10, 10], [10, 10], [10, 10], Resizer::PROPORTIONAL | Resizer::NO_INFLATE | Resizer::NO_DEFLATE | Resizer::MINIMUM],
            [[10, 10], [10, 10], [10, 10], 0],
            [[10, 10], [10, 10], [10, 10], Resizer::MINIMUM],
            [[10, 10], [10, 10], [10, 10], Resizer::NO_DEFLATE],
            [[10, 10], [10, 10], [10, 10], Resizer::NO_DEFLATE | Resizer::MINIMUM],
            [[10, 10], [10, 10], [10, 10], Resizer::NO_INFLATE],
            [[10, 10], [10, 10], [10, 10], Resizer::NO_INFLATE | Resizer::MINIMUM],
            [[10, 10], [10, 10], [10, 10], Resizer::NO_INFLATE | Resizer::NO_DEFLATE],
            [[10, 10], [10, 10], [10, 10], Resizer::NO_INFLATE | Resizer::NO_DEFLATE | Resizer::MINIMUM]
        ];
    }
}
