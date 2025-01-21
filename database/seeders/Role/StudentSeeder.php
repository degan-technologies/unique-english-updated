<?php

namespace Database\Seeders\Role;

use App\Models\Role\Student;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder {
    use UserSeederTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $students = [
            ['first_name' => 'Student', 'email' => 'Student', 'gender' => MALE,'role' => STUDENT],
        ];
        $systemAdmin = User::query()
            ->has('systemAdmin')
            ->first()
            ->id;

        try {
            DB::beginTransaction();

            foreach ($students as $student) {
                $user = $this->createUser($student, $systemAdmin);

                $sys = new Student();
                $sys->user_id = $user->id;
                $sys->save();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            echo "Unable to seed new student";
        }
    }
}
