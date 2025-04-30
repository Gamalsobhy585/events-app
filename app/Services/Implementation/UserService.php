<?php
namespace App\Services\Implementation;
use App\Models\User;
use App\Services\Interface\UserServiceInterface;

class UserService implements UserServiceInterface
{
   

    public function saveUserBackgroundInformation($user, $detail)
    {
        $fullName = trim(
            $user->firstname . ' ' . 
            ($user->middlename ? $user->middlename . ' ' : '') . 
            $user->lastname
        );
        
        $middleInitial = $user->middlename ? strtoupper(substr($user->middlename, 0, 1)) . '.' : '';
        $avatar = $user->photo ?? 'default-avatar.png';
        $gender = $this->determineGenderFromPrefix($user->prefixname);
        
        $this->saveDetail($user->id, 'full_name', $fullName, $detail);
        $this->saveDetail($user->id, 'middle_initial', $middleInitial, $detail);
        $this->saveDetail($user->id, 'avatar', $avatar, $detail);
        $this->saveDetail($user->id, 'gender', $gender, $detail);
    }

    public function saveDetail($userId, $key, $value, $detail, $type = 'detail')
    {
        return $detail->updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'status' => '1'
            ]
        );
    }

    public function determineGenderFromPrefix($prefix)
    {
        if (!$prefix) {
            return 'unknown';
        }
        
        $prefix = strtolower($prefix);
        
        $malePrefix = ['mr', 'mr.', 'sir', 'master'];
        $femalePrefix = ['mrs', 'mrs.', 'miss', 'ms', 'ms.', 'madam'];
        
        if (in_array($prefix, $malePrefix)) {
            return 'male';
        }
        
        if (in_array($prefix, $femalePrefix)) {
            return 'female';
        }
        
        return 'unknown';
    }

    public function getUserDetails($user)
    {
        return $user->details()->paginate(10);
    }

    public function createUserDetail($user, array $data)
    {
        return $user->details()->create($data);
    }

    public function getUserDetail($user, $detailId)
    {
        return $user->details()->findOrFail($detailId);
    }

    public function updateUserDetail($detail, array $data)
    {
        $detail->update($data);
        return $detail;
    }

    public function deleteUserDetail($detail)
    {
        return $detail->delete();
    }
}