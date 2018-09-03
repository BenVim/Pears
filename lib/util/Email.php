<?php 
/*
 * @Author: Ben 
 * @Date: 2017-08-10 22:43:05 
 * @Last Modified by: Ben
 * @Last Modified time: 2017-08-11 09:02:40
 */
namespace lib\core;

use InvalidArgumentException;

final class Email
{
    private $email;

    private function __construct(string $email)
    {
        $this->ensureIsValidEmail($email);

        $this->email = $email;
    }

    public static function fromString(string $email)
    {
        return new self($email);
    }

    public function __toString()
    {
        return $this->email;
    }

    private function ensureIsValidEmail(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" is not a valid email address',
                    $email
                )
            );
        }
    }
}