<?php

namespace Database\Seeders\Role;

use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SecondaryAdminSeeder extends Seeder {
    use UserSeederTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $secondaryAdmins = [
            ['first_name' => 'Secondary Admin', 'email' => 'secondaryadmin', 'gender' => FEMALE, 'role' => SECONDARY_ADMIN],
        ];

        try {
            DB::beginTransaction();


            foreach ($secondaryAdmins as $secondaryAdmin) {
                $user = $this->createUser($secondaryAdmin);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            echo "Unable to seed secondary admins: " . $e->getMessage();
        }
    }
}
