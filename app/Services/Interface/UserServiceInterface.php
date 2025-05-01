<?php

namespace App\Services\Interface;


interface UserServiceInterface
{
    public function saveUserBackgroundInformation($user, $detail);
    public function saveDetail($userId, $key, $value, $detail, $type = 'detail');
    public function determineGenderFromPrefix($prefix);
    public function getUserDetails($user);
    public function createUserDetail($user, array $data);
    public function getUserDetail($user, $detailId);
    public function updateUserDetail($detail, array $data);
}