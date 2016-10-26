# CoRex Command
Command Framework for php.

You will recognize some familiar patterns i.e. signature from Laravel but with additional properties. This is for future use in other frameworks and CMS'es.

Run "php crcmd" to see list of available commands.

Run "php crcmd help make:shortcut" to create a shortcut in current directory. This file can be modified to suit your needs i.e. disabling internal commands. If you specify --command, you will be able to make a shortcut for a specific command.

Every command created must end in "Command.php" i.e. "MyCommand.php". Otherwise it will not be added to list of available commands.

## Installation
- Run "composer require corex/command".

## Commands (internal).
- make:command - This command creates commands.
- make:shortcut - This command creates a shortcut in current directory.

## Documentation for command

Look at existing commands to see example. Source can be found in vendor package.

### Properties

#### $component
Every command belongs to a component. This property specifies the name of component. Must be specified in lowercase. This can be set to an empty string and will then be a "main"-command (no component).

#### $signature
This signature describes the arguments and options of the command. It follows the same setup as Laravel 5.

Format is "command {argument/--option : description} {argument/--option : description}"
- Part 1 "command" is the name of the command. Must be specified in lowercase.
- Part 2 "{argument/--option : description}" is the format of arguments and options. Can be specified multiple times. Each argument/options must be surrounded by {}.
  - Every argument is required unless you add a "?" at the end of argument-name. If "?" is added, argument will return null.
  - Every option must be prefix'ed with "--". If specified on command-line, it will be true, otherwise false. If you need to parse a value instead, add a "=" at the end.

#### $description
This is a short description you will see on the list of available commands. 

#### $visible
This option can be either true or false. If false, it will not be visible on the list of commands. You can still use "help" to show command and it will still work.

You code lives in a method called run().

## Helpers when developing
If you use "$this->" when developing, there will be a long list of methods to help you write commands i.e. table(), header(), info(), throwError(), words(), choice(), etc.

## Start
To start a new project using commands, do following:
- Install package.
- Setup namespace for commands.
- Use "make:shortcut" to create a shortcut in root of your project.
- Modify and set registerOnPath().
- Use "make:command" to create a new commmand and modify it.

## Config
Setup configuration for your project.
- 