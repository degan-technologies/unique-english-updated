<?php

namespace Database\Seeders\Role;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait UserSeederTrait{

    public function createUser($info, $createdBy = null) {
        $user = new User();
        $user->slug = Str::uuid();
        $user->gender = $info['gender'];

        $user->first_name = $info['first_name'];
        $user->middle_name = 'Admin';
        $user->user_name = Str::slug($info['first_name']);
        $user->email = $info['email'] . "@qelemmeda.com";
        $user->password = Hash::make('webpass');
        $user->role = $info['role'];
        $user->phone = '09' . mt_rand(11111111, 99999999);
        $user->email_verified_at = date("Y-m-d H:i:s");

        $user->user_id = $createdBy;
        $user->save();

        return $user;
    }
}