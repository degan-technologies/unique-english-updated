<?php

namespace Database\Seeders\Role;

use App\Models\Role\SystemAdmin;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemAdminSeeder extends Seeder {
    use UserSeederTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $sysAdmins = [
            ['first_name' => 'System Admin', 'email' => 'admin', 'gender' => MALE, 'role' => SYSTEM_ADMIN],
        ];

        try {
            DB::beginTransaction();

            foreach ($sysAdmins as $sysAdmin) {
                $user = $this->createUser($sysAdmin, null);

                $sys = new SystemAdmin();
                $sys->user_id = $user->id;
                $sys->save();
            }

            DB::commit();
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();
            echo "Unable to seed system admins";
        }
    }
}
