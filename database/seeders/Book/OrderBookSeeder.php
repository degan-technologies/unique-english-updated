<?php


namespace Database\Seeders\Book;


use Carbon\Carbon;
use App\Models\User;
use App\Models\Book\Book;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class OrderBookSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $book = Book::first();

        foreach($users as $user){
            $user->orderedBooks()->create([
                'slug' => Str::uuid(),
                'book_id' => $book->id,
                'enrolled_at' => Carbon::now(),
            ]);
        }
    }
}
