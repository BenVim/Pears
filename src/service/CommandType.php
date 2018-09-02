<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 03/11/2017
 * Time: 17:48
 */

namespace src\factory;


use src\command\ChatCommand;
use src\command\LoginCommand;


class CommandType
{
    const LOGIN_COMMAND_KEY            = "login";
    const CHAT_COMMAND_KEY            = "chat";

    public static function getCommandList()
    {
        $commandList                                            = [];
        $commandList[CommandType::LOGIN_COMMAND_KEY]            = LoginCommand::class;
        $commandList[CommandType::CHAT_COMMAND_KEY]             = ChatCommand::class;

        return $commandList;
    }


}