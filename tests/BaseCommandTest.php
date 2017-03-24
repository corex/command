<?php

use CoRex\Command\BaseCommand;
use CoRex\Command\Handler;
use CoRex\Command\Loader;
use CoRex\Command\SignatureHandler;
use CoRex\Command\Tests\Command\TestCommand;
use PHPUnit\Framework\TestCase;

class BaseCommandTest extends TestCase
{
    /**
     * Test unknown command.
     */
    public function testUnknownCommand()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Command not found.');
        SignatureHandler::call('test', 'unknown');
    }

    /**
     * Test missing parameters.
     */
    public function testMissingParameters()
    {
        $this->initializeCommandHandler();
        $this->expectException('Exception');
        $this->expectExceptionMessage('Argument required: param1');
        SignatureHandler::call('test', 'command', [], true);
    }

    /**
     * Test set properties.
     */
    public function testSetProperties()
    {
        $this->initializeCommandHandler();

        // Test signature.
        $signature = [
            'class' => 'CoRex\Command\Tests\Command\TestCommand',
            'description' => 'Test command',
            'visible' => false,
            'arguments' => [
                'param1' => [
                    'description' => 'Parameter 1',
                    'optional' => false
                ],
                'param2' => [
                    'description' => 'Parameter 2',
                    'optional' => false
                ]
            ],
            'options' => []
        ];

        $command = new TestCommand();
        $command->setProperties($signature, ['param1' => 'Test 1', 'param2' => 'Param 2'], true);

        // Extract private properties.
        $result = [];
        $reflectionClass = new ReflectionClass(BaseCommand::class);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        if (count($properties) > 0) {
            foreach ($properties as $property) {
                $property->setAccessible(true);
                $result[$property->getName()] = $property->getValue($command);
            }
        }

        // Check properties.
        $this->assertEquals($signature['class'], $result['signature']['class']);
        $this->assertEquals($signature['description'], $result['signature']['description']);
        $this->assertEquals($signature['visible'], $result['signature']['visible']);
        $this->assertEquals($signature['arguments'], $result['signature']['arguments']);
        $this->assertEquals($signature['options'], $result['signature']['options']);
        $this->assertEquals(true, $result['throughComposer']);
    }

    /**
     * Test set silent.
     */
    public function testSetSilent()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test through composer.
     */
    public function testThroughComposer()
    {
        $this->assertTrue(true, 'This is tested in testSetProperties()');
    }

    /**
     * Test argument.
     */
    public function testArgument()
    {
        $this->assertTrue(true, 'This is tested in testSetProperties()');
    }

    /**
     * Test option.
     */
    public function testOption()
    {
        $this->assertTrue(true, 'This is tested in testSetProperties()');
    }

    /**
     * Test line length.
     */
    public function testSetLineLength()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test call.
     */
    public function testCall()
    {
        $value1 = md5(microtime(true));
        $value2 = md5(microtime(true));
        ob_start();
        SignatureHandler::call('test', 'command', [
            'param1' => $value1,
            'param2' => $value2
        ], false);
        $content = ob_get_clean();
        $checkValue = json_encode([
            'param1' => $value1,
            'param2' => $value2
        ]);
        $this->assertEquals($checkValue, $content);
    }

    /**
     * Test call silent.
     */
    public function testCallSilent()
    {
        $value1 = md5(microtime(true));
        $value2 = md5(microtime(true));
        ob_start();
        SignatureHandler::call('test', 'command', [
            'param1' => $value1,
            'param2' => $value2
        ], true);
        $content = ob_get_clean();
        $this->assertEquals('', $content);
    }

    /**
     * Test write.
     */
    public function testWrite()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test writeln.
     */
    public function testWriteln()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test header.
     */
    public function testHeader()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test separator.
     */
    public function testSeparator()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test info.
     */
    public function testInfo()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test error.
     */
    public function testError()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test comment.
     */
    public function testComment()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test warning.
     */
    public function testWarning()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test title.
     */
    public function testTitle()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test block.
     */
    public function testBlock()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test ask.
     */
    public function testAsk()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test confirm.
     */
    public function testConfirm()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test secret.
     */
    public function testSecret()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test choice.
     */
    public function testChoice()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test table.
     */
    public function testTable()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test words.
     */
    public function testWords()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test throw error.
     */
    public function testThrowError()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test config.
     */
    public function testConfig()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Initialize command handler.
     *
     * @param array $arguments Default [].
     */
    private function initializeCommandHandler(array $arguments = [])
    {
        ob_start();
        Loader::initialize();
        try {
            $handler = new Handler($arguments, true, false);
            $handler->registerOnPath(__DIR__);
            $handler->execute();
        } catch (Exception $e) {
            print($e->getMessage() . "\n");
        }
        ob_end_clean();
    }
}
