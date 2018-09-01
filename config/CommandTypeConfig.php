<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 2018/9/1
 * Time: 15:59
 */

namespace config;


use src\command\ChatCommand;
use src\command\LoginCommand;

class CommandTypeConfig
{
    const LOGIN_COMMAND_KEY = "login";
    const CHAT_COMMAND_KEY  = "chat";

    public static function registerCommand()
    {
        $commandList                                       = [];
        $commandList[CommandTypeConfig::LOGIN_COMMAND_KEY] = LoginCommand::class;
        $commandList[CommandTypeConfig::CHAT_COMMAND_KEY]  = ChatCommand::class;

        return $commandList;
    }
}