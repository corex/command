<?php

use CoRex\Command\Commands;
use CoRex\Command\Handler;
use CoRex\Command\Loader;
use PHPUnit\Framework\TestCase;
use Tests\CoRex\Command\TestCommand;

class HandlerTest extends TestCase
{
    private $component = 'test';
    private $command = 'command';
    private $param1 = 'param1';
    private $param2 = 'param2';
    private $description = 'Test command';

    /**
     * Test constructor.
     */
    public function testConstructor()
    {
        $arguments = ['artisan', 'test:command', 'p1', 'p2'];
        $showInternalCommands = true;
        $throughComposer = false;
        $handler = new Handler($arguments, $showInternalCommands, $throughComposer);
        $properties = $this->getPrivatePropertiesFromStaticClass(Handler::class, $handler);
        $this->assertEquals('CoRex Command.', $properties['title']);
        $this->assertEquals(['p1', 'p2'], $properties['arguments']);
        $this->assertEquals($this->component, $properties['component']);
        $this->assertEquals($this->command, $properties['command']);
        $this->assertFalse($properties['isHelp']);
        $this->assertFalse($properties['throughComposer']);
        $this->assertEquals(30, $properties['indentLength']);
    }

    /**
     * Test set title.
     */
    public function testSetTitle()
    {
        $title = md5(microtime(true));
        $handler = new Handler([], false, false);
        $handler->setTitle($title);
        $properties = $this->getPrivatePropertiesFromStaticClass(Handler::class, $handler);
        $this->assertEquals($title, $properties['title']);
    }

    /**
     * Test register.
     */
    public function testRegister()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test register on path.
     */
    public function testRegisterOnPath()
    {
        Commands::getInstance()->clear();
        $this->initialize(false);
        $handler = new Handler([], false, false);
        $handler->registerOnPath(__DIR__);
        $commands = Commands::getInstance()->getCommands($this->component);
        $commandProperties = $commands[$this->command];
        $this->assertEquals(TestCommand::class, $commandProperties['class']);
        $this->assertEquals($this->description, $commandProperties['description']);
        $this->assertTrue($commandProperties['visible']);
        $this->assertEquals([$this->param1, $this->param2], array_keys($commandProperties['arguments']));
        $this->assertEquals([], array_keys($commandProperties['options']));
    }

    /**
     * Test execute.
     */
    public function testExecute()
    {
        $this->initialize(false);

        $value1 = md5(microtime(true));
        $value2 = md5(microtime(true));
        $arguments = [
            'param1' => $value1,
            'param2' => $value2
        ];

        $handler = new Handler($arguments, false, false);
        $handler->registerOnPath(__DIR__);

        ob_start();
        $handler->execute($this->component, $this->command);
        $content = ob_get_clean();

        $checkValue = json_encode([
            'param1' => $value1,
            'param2' => $value2
        ]);
        $this->assertEquals($checkValue, $content);
    }

    /**
     * Test show all.
     */
    public function testShowAll()
    {
        $this->initialize(false);
        $handler = new Handler([], true, false);
        $handler->registerOnPath(__DIR__);

        ob_start();
        $handler->showAll();
        $content = ob_get_clean();

        $this->assertContains('json:get', $content);
        $this->assertContains($this->component . ':' . $this->command, $content);
        $this->assertContains($this->description, $content);
    }

    /**
     * Test show.
     */
    public function testShow()
    {
        $this->initialize(false);
        $handler = new Handler([], true, false);
        $handler->registerOnPath(__DIR__);

        ob_start();
        $handler->show($this->component, $this->command);
        $content = ob_get_clean();

        $this->assertContains($this->component . ':' . $this->command, $content);
        $this->assertContains($this->param1, $content);
        $this->assertContains($this->param2, $content);
    }

    /**
     * Test set component visibility.
     */
    public function testSetComponentVisibility()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test hide component.
     */
    public function testHideComponent()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test set command visibility.
     */
    public function testSetCommandVisibility()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test hide command.
     */
    public function testHideCommand()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Test hide commands.
     */
    public function testHideCommands()
    {
        $this->assertTrue(true, 'This is tested elsewhere.');
    }

    /**
     * Get private properties from static class.
     *
     * @param string $className
     * @param object $object Default null which means new $className().
     * @return array
     */
    private function getPrivatePropertiesFromStaticClass($className, $object = null)
    {
        $result = [];
        if ($object === null) {
            $object = new $className();
        }
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
            Commands::getInstance()->hideInternal(true);
            Commands::getInstance()->register(TestCommand::class);
            Commands::getInstance()->hideInternal(false);
        }
    }
}