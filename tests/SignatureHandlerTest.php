<?php

use CoRex\Command\Loader;
use CoRex\Command\SignatureHandler;
use CoRex\Command\Tests\Command\TestCommand;
use PHPUnit\Framework\TestCase;

class SignatureHandlerTest extends TestCase
{
    private $component = 'test';
    private $command = 'command';
    private $param1 = 'param1';
    private $param2 = 'param2';
    private $description = 'Test command';
    private $visible = false;

    /**
     * Tet register.
     */
    public function testRegister()
    {
        $this->initialize(true);
        $properties = $this->getPrivatePropertiesFromStaticClass(SignatureHandler::class);
        $this->assertArrayHasKey('commands', $properties);
        $this->assertArrayHasKey('visible', $properties);
        $this->assertNotNull($properties['commands']);
        $this->assertNotNull($properties['visible']);
        $this->assertTrue(isset($properties['commands'][$this->component][$this->command]));

        // Check command properties.
        $commandProperties = $properties['commands'][$this->component][$this->command];
        $this->assertEquals(TestCommand::class, $commandProperties['class']);
        $this->assertEquals($this->description, $commandProperties['description']);
        $this->assertEquals($this->visible, $commandProperties['visible']);
        $this->assertEquals([$this->param1, $this->param2], array_keys($commandProperties['arguments']));
        $this->assertEquals([], array_keys($commandProperties['options']));
    }

    /**
     * Test get signature.
     */
    public function testGetSignature()
    {
        $this->initialize(true);
        $commandProperties = SignatureHandler::getSignature($this->component, $this->command);
        $this->assertEquals(TestCommand::class, $commandProperties['class']);
        $this->assertEquals($this->description, $commandProperties['description']);
        $this->assertEquals($this->visible, $commandProperties['visible']);
        $this->assertEquals([$this->param1, $this->param2], array_keys($commandProperties['arguments']));
        $this->assertEquals([], array_keys($commandProperties['options']));
    }

    /**
     * Test is component visible.
     */
    public function testIsComponentVisible()
    {
        $this->initialize(true);
        $this->assertEquals($this->visible, SignatureHandler::isComponentVisible($this->component, $this->command));
    }

    /**
     * Test call.
     */
    public function testCall()
    {
        $this->assertTrue(true, 'This is tested in test BaseCommandTest.');
    }

    /**
     * Test component exist.
     */
    public function testComponentExist()
    {
        $this->initialize(true);
        $this->assertTrue(SignatureHandler::componentExist($this->component));
    }

    /**
     * Test command exist.
     */
    public function testCommandExist()
    {
        $this->initialize(true);
        $this->assertTrue(SignatureHandler::commandExist($this->component, $this->command));
    }

    /**
     * Test get components.
     */
    public function testGetComponents()
    {
        $this->initialize(true);
        $components = SignatureHandler::getComponents();
        $this->assertTrue(is_array($components));
        $this->assertTrue(in_array($this->component, $components));
    }

    /**
     * Test get commands.
     */
    public function testGetCommands()
    {
        $this->initialize(true);
        $commands = SignatureHandler::getCommands($this->component);
        $this->assertTrue(isset($commands[$this->command]));
        $commandProperties = $commands[$this->command];
        $this->assertEquals(TestCommand::class, $commandProperties['class']);
        $this->assertEquals($this->description, $commandProperties['description']);
        $this->assertEquals($this->visible, $commandProperties['visible']);
        $this->assertEquals([$this->param1, $this->param2], array_keys($commandProperties['arguments']));
        $this->assertEquals([], array_keys($commandProperties['options']));
    }

    /**
     * Test set component visibility.
     */
    public function testSetComponentVisibility()
    {
        $this->initialize(false);
        SignatureHandler::setComponentVisibility($this->component, true);
        $properties = $this->getPrivatePropertiesFromStaticClass(SignatureHandler::class);
        $this->assertArrayHasKey('visible', $properties);
        $this->assertTrue(isset($properties['visible'][$this->component]['*']));
        $this->assertTrue($properties['visible'][$this->component]['*']);
    }

    /**
     * Test hide component.
     */
    public function testHideComponent()
    {
        $this->initialize(false);
        SignatureHandler::hideComponent($this->component);
        $properties = $this->getPrivatePropertiesFromStaticClass(SignatureHandler::class);
        $this->assertArrayHasKey('visible', $properties);
        $this->assertTrue(isset($properties['visible'][$this->component]['*']));
        $this->assertFalse($properties['visible'][$this->component]['*']);
    }

    /**
     * Test set command visibility.
     */
    public function testSetCommandVisibility()
    {
        $this->initialize(false);
        SignatureHandler::setCommandVisibility($this->component, $this->command, true);
        $properties = $this->getPrivatePropertiesFromStaticClass(SignatureHandler::class);
        $this->assertArrayHasKey('visible', $properties);
        $this->assertTrue(isset($properties['visible'][$this->component][$this->command]));
        $this->assertTrue($properties['visible'][$this->component][$this->command]);
    }

    /**
     * Test hide command.
     */
    public function testHideCommand()
    {
        $this->initialize(false);
        SignatureHandler::hideCommand($this->component, $this->command);
        $properties = $this->getPrivatePropertiesFromStaticClass(SignatureHandler::class);
        $this->assertArrayHasKey('visible', $properties);
        $this->assertTrue(isset($properties['visible'][$this->component][$this->command]));
        $this->assertFalse($properties['visible'][$this->component][$this->command]);
    }

    /**
     * Test hide commands.
     */
    public function testHideCommands()
    {
        $this->initialize(false);
        SignatureHandler::hideCommands($this->component, [$this->command]);
        $properties = $this->getPrivatePropertiesFromStaticClass(SignatureHandler::class);
        $this->assertArrayHasKey('visible', $properties);
        $this->assertTrue(isset($properties['visible'][$this->component][$this->command]));
        $this->assertFalse($properties['visible'][$this->component][$this->command]);
    }

    /**
     * Get private properties from static class.
     *
     * @param string $className
     * @return array
     */
    private function getPrivatePropertiesFromStaticClass($className)
    {
        $result = [];
        $object = new $className();
        $reflectionClass = new ReflectionClass($className);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        if (count($properties) > 0) {
            foreach ($properties as $property) {
                $property->setAccessible(true);
                $result[$property->getName()] = $property->getValue($object);
            }
        }
        return $result;
    }

    /**
     * Initialize.
     *
     * @param boolean $registerCommand
     */
    private function initialize($registerCommand)
    {
        require_once(dirname(__DIR__) . '/src/Loader.php');
        Loader::initialize();
        if ($registerCommand) {
            SignatureHandler::register(TestCommand::class, true);
        }
    }
}