<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Template\Html;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Template\Html\Minifier;

class MinifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Minifier
     */
    protected $object;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $htmlDirectory;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appDirectory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readFactory;

    /**
     * Initialize testable object
     */
    protected function setUp()
    {
        $this->htmlDirectory = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\WriteInterface')
            ->getMock();
        $this->appDirectory = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')->getMock();
        $filesystem = $this->getMockBuilder('Magento\Framework\Filesystem')->disableOriginalConstructor()->getMock();
        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::TEMPLATE_MINIFICATION_DIR)
            ->willReturn($this->htmlDirectory);
        /** @var \Magento\Framework\Filesystem $filesystem */

        $this->readFactory = $this->getMock('Magento\Framework\Filesystem\Directory\ReadFactory', [], [], '', false);
        $this->readFactory->expects($this->any())->method('create')->willReturn($this->appDirectory);
        $this->object = new Minifier($filesystem, $this->readFactory);
    }

    /**
     * Covered method getPathToMinified
     * @test
     */
    public function testGetPathToMinified()
    {
        $file = '/absolute/path/to/phtml/template/file';
        $relativeGeneratedPath = 'absolute/path/to/phtml/template/file';
        $absolutePath = '/full/path/to/compiled/html/file';

        $this->htmlDirectory->expects($this->once())
            ->method('getAbsolutePath')
            ->with($relativeGeneratedPath)
            ->willReturn($absolutePath);

        $this->assertEquals($absolutePath, $this->object->getPathToMinified($file));
    }

    // @codingStandardsIgnoreStart
    /**
     * Covered method minify and test regular expressions
     * @test
     */
    public function testMinify()
    {
        $file = '/absolute/path/to/phtml/template/file';
        $relativeGeneratedPath = 'absolute/path/to/phtml/template/file';
        $baseContent = <<<TEXT
<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
?>
<?php //one line comment ?>
<html>
    <head>
        <title>Test title</title>
    </head>
    <body>
        <a href="http://somelink.com/text.html">Text Link</a>
        <img src="test.png" alt="some text" />
        <?php echo \$block->someMethod(); ?>
        <div style="width: 800px" class="<?php echo \$block->getClass() ?>" />
        <script>
            var i = 1;// comment
            var j = 1;// <?php echo 'hi' ?>
//<?php ?> ')){
// if (<?php echo __('hi')) { ?>
// if (<?php )) {
// comment
            //<![CDATA[
            var someVar = 123;
            testFunctionCall(function () {
                return {
                    'someProperty': test,
                    'someMethod': function () {
                        alert(<?php echo \$block->getJsAlert() ?>);
                    }
                }
            });
            //]]>
        </script>
        <?php echo "http://some.link.com/" ?>
        <em>inline text</em>
        <a href="http://www.<?php echo 'hi' ?>"></a>
    </body>
</html>
TEXT;

        $expectedContent = <<<TEXT
<?php /** * Copyright © 2015 Magento. All rights reserved. * See COPYING.txt for license details. */ ?> <?php ?> <html><head><title>Test title</title></head><body><a href="http://somelink.com/text.html">Text Link</a> <img src="test.png" alt="some text" /><?php echo \$block->someMethod(); ?> <div style="width: 800px" class="<?php echo \$block->getClass() ?>" /><script>
            var i = 1;
            var j = 1;




            //<![CDATA[
            var someVar = 123;
            testFunctionCall(function () {
                return {
                    'someProperty': test,
                    'someMethod': function () {
                        alert(<?php echo \$block->getJsAlert() ?>);
                    }
                }
            });
            //]]>
</script><?php echo "http://some.link.com/" ?> <em>inline text</em> <a href="http://www.<?php echo 'hi' ?>"></a></body></html>
TEXT;

        $this->appDirectory->expects($this->once())
            ->method('readFile')
            ->with(basename($file))
            ->willReturn($baseContent);

        $this->htmlDirectory->expects($this->once())
            ->method('isExist')
            ->willReturn(false);
        $this->htmlDirectory->expects($this->once())
            ->method('create');
        $this->htmlDirectory->expects($this->once())
            ->method('writeFile')
            ->with($relativeGeneratedPath, $expectedContent);

        $this->object->minify($file);
    }
    // @codingStandardsIgnoreEnd

    /**
     * Contain method modify and getPathToModified
     * @test
     */
    public function testGetMinified()
    {
        $file = '/absolute/path/to/phtml/template/file';
        $relativeGeneratedPath = 'absolute/path/to/phtml/template/file';

        $htmlDriver = $this->getMock('Magento\Framework\Filesystem\DriverInterface', [], [], '', false);
        $htmlDriver
            ->expects($this->once())
            ->method('getRealPathSafety')
            ->willReturn($file);

        $this->htmlDirectory
            ->expects($this->at(1))
            ->method('isExist')
            ->with($relativeGeneratedPath)
            ->willReturn(false);

        $this->htmlDirectory
            ->expects($this->once())
            ->method('getDriver')
            ->willReturn($htmlDriver);

        $this->object->getMinified($file);
    }
}
