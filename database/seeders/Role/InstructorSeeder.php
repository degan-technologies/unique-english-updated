<?php

namespace Database\Seeders\Role;

use App\Models\Role\Instructor;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstructorSeeder extends Seeder {
    use UserSeederTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $instructors = [
            ['first_name' => 'Instructor', 'email' => 'instructor', 'gender' => MALE, 'phone' =>'0921030278', 'role' => INSTRUCTOR],
        ];

        $systemAdmin = User::query()
            ->has('systemAdmin')
            ->first()
            ->id;

        try {
            DB::beginTransaction();

            foreach ($instructors as $instructor) {
                $user = $this->createUser($instructor, $systemAdmin);

                $sys = new Instructor();
                $sys->user_id = $user->id;
                $sys->save();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            echo "Unable to seed Instructor";
        }
    }
}
