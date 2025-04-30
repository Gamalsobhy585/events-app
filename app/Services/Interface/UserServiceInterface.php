<?php

namespace App\Services\Interface;


interface UserServiceInterface
{
    public function saveUserBackgroundInformation($user);
    public function saveDetail($userId, $key, $value, $type = 'detail');
    public function determineGenderFromPrefix($prefix);
}
