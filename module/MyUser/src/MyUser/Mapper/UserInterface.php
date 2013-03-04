<?php

namespace MyUser\Mapper;

use ZfcUser\Mapper\UserInterface as BaseUserInterface;

interface UserInterface extends BaseUserInterface
{
    public function findByEmail($email);

    public function findByUsername($username);

    public function findById($id);

    public function insert($user);

    public function update($user);
}
