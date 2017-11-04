<?php

use CoRex\Command\Commands;
use CoRex\Command\Loader;
use CoRex\Support\Obj;
use PHPUnit\Framework\TestCase;
use Tests\CoRex\Command\TestCommand;

class CommandsTest extends TestCase
{
    private $component = 'test';
    private $command = 'command';
    private $param1 = 'param1';
    private $param2 = 'param2';
    private $description = 'Test command';
    private $visible = true;

    /**
     * Tet register.
     */
    public function testRegister()
    {
        $this->initialize(true);
        $properties = $this->getPrivatePropertiesFromObject(Commands::getInstance());
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
        $commandProperties = Commands::getInstance()->getSignature($this->component, $this->command);
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
        $this->assertEquals($this->visible, Commands::getInstance()->isComponentVisible($this->component));
    }

    /**
     * Test call.
     */
    public function testCall()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test component exist.
     */
    public function testComponentExist()
    {
        $this->initialize(true);
        $this->assertTrue(Commands::getInstance()->componentExist($this->component));
    }

    /**
     * Test command exist.
     */
    public function testCommandExist()
    {
        $this->initialize(true);
        $this->assertTrue(Commands::getInstance()->commandExist($this->component, $this->command));
    }

    /**
     * Test get components.
     */
    public function testGetComponents()
    {
        $this->initialize(true);
        $components = Commands::getInstance()->getComponents();
        $this->assertTrue(is_array($components));
        $this->assertTrue(in_array($this->component, $components));
    }

    /**
     * Test get commands.
     */
    public function testGetCommands()
    {
        $this->initialize(true);
        $commands = Commands::getInstance()->getCommands($this->component);
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
        Commands::getInstance()->setComponentVisibility($this->component, true);
        $properties = $this->getPrivatePropertiesFromObject(Commands::getInstance());
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
        Commands::getInstance()->hideComponent($this->component);
        $properties = $this->getPrivatePropertiesFromObject(Commands::getInstance());
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
        Commands::getInstance()->setCommandVisibility($this->component, $this->command, true);
        $properties = $this->getPrivatePropertiesFromObject(Commands::getInstance());
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
        Commands::getInstance()->hideCommand($this->component, $this->command);
        $properties = $this->getPrivatePropertiesFromObject(Commands::getInstance());
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
        Commands::getInstance()->hideCommands($this->component, [$this->command]);
        $properties = $this->getPrivatePropertiesFromObject(Commands::getInstance());
        $this->assertArrayHasKey('visible', $properties);
        $this->assertTrue(isset($properties['visible'][$this->component][$this->command]));
        $this->assertFalse($properties['visible'][$this->component][$this->command]);
    }

    /**
     * Get private properties from object.
     *
     * @param string $object
     * @return array
     */
    private function getPrivatePropertiesFromObject($object)
    {
        return Obj::getProperties($object, null, Obj::PROPERTY_PRIVATE);
//        $result = [];
//        $object = new $className();
//        $reflectionClass = new ReflectionClass($className);
//        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
//        $properties = Obj::getProperties(Commands::getInstance(), null, Obj::PROPERTY_PRIVATE);
//        if (count($properties) > 0) {
//            foreach ($properties as $property) {
//                $property->setAccessible(true);
//                $result[$property->getName()] = $property->getValue($object);
//            }
//        }
//        return $result;
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
            Commands::getInstance()->hideInternal(true);
            Commands::getInstance()->register(TestCommand::class);
        }
    }
}