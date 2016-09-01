<?php

namespace CoRex\Command;

interface BaseCommandInterface
{
    /**
     * Run command.
     * @return boolean
     */
    public function run();
}