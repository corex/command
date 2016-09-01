# CoRex Command
Command Framework for php.

You will recognize some familiar patterns i.e. signature from Laravel but with additional properties. This is for future use in other frameworks and CMS'es.

All you need to use is this one package to start writing and use commands.

Run "php crcmd" to see list of available commands.

Run "php crcmd help make:root" to create a "crcmd" in current directory. This file can be modified to suit your needs i.e. disabling internal commands. You will also be able to specify a new name for created command.

Every command created must end in "Command.php" i.e. "MyCommand.php". Otherwise it will not be added to list of available commands.

## Dokumentation for command

Look at Make/RootCommand (existing command) to see example.

Following properties must be set in command.

### Property $component
Every command belongs to a component. This property specifies the name of component. Must be specified in lowercase.

### Property $signature
This signature describes the arguments and options of the command. It follows the same setup as Laravel 5.

Format is "command {argument/--option : description} {argument/--option : description}"
- Part 1 "command" is the name of the command. Must be specified in lowercase.
- Part 2 "{argument/--option : description}" is the format of arguments and options. Can be specified multiple times. Each argument/options must be surrounded by {}.
  - Every argument is required unless you add a "?" at the end of argument-name. If "?" is added, argument will return null.
  - Every option must be prefix'ed with "--". If specified on command-line, it will be true, otherwise false. If you need to parse a value instead, add a "=" at the end.

### Property $description
This is a short description you will see on the list of available commands. 

### Property $visible
This option can be either true or false. If false, it will not be visible on the list of commands. You can still use "help" to show command and it will still work.

You code lives in a method called run().
