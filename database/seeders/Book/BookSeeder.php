<?php

namespace Database\Seeders\Book;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Exception;

class BookSeeder extends Seeder {

    public function run(): void {
        try {
            $user = User::first();
            $books = [
                [
                    'slug' => Str::uuid(),
                    'title' => 'The Great Gatsby',
                    'auther' => 'F. Scott Fitzgerald',
                    'page_number' => 180,
                    'publish_date' => '1925-04-10',
                    'eddition' => '1',
                    'price' => 15.99,
                    'discount' => 0,
                    'description' => 'A novel set in the Roaring Twenties, exploring wealth and class.',
                    'language' => 'English',
                    'file_format' => 'PDF',
                    'cover_page_url' => '/images/book-1.webp',
                    'file_url' => 'http://example.com/files/great-gatsby.pdf',
                    'tag' => json_encode(['Classic', 'Novel']),
                    'isDownloadable' => true,
                ],
                [
                    'slug' => Str::uuid(),
                    'title' => '1984',
                    'auther' => 'George Orwell',
                    'page_number' => 328,
                    'publish_date' => '1949-06-08',
                    'eddition' => '1',
                    'price' => 18.99,
                    'discount' => 0,
                    'description' => 'A dystopian novel about totalitarian surveillance and control.',
                    'language' => 'English',
                    'file_format' => 'PDF',
                    'cover_page_url' => '/images/book-2.jpeg',
                    'file_url' => 'http://example.com/files/1984.pdf',
                    'tag' => json_encode(['Dystopian', 'Classic']),
                    'isDownloadable' => true,
                ],
                [
                    'slug' => Str::uuid(),
                    'title' => 'To Kill a Mockingbird',
                    'auther' => 'Harper Lee',
                    'page_number' => 281,
                    'publish_date' => '1960-07-11',
                    'eddition' => '1',
                    'price' => 14.99,
                    'discount' => 0,
                    'description' => 'A powerful novel about racial injustice in the American South.',
                    'language' => 'English',
                    'file_format' => 'PDF',
                    'cover_page_url' => '/images/book-3.jpeg',
                    'file_url' => 'http://example.com/files/to-kill-a-mockingbird.pdf',
                    'tag' => json_encode(['Classic', 'Historical']),
                    'isDownloadable' => true,
                ],
                [
                    'slug' => Str::uuid(),
                    'title' => 'The Catcher in the Rye',
                    'auther' => 'J.D. Salinger',
                    'page_number' => 214,
                    'publish_date' => '1951-07-16',
                    'eddition' => '1',
                    'price' => 17.99,
                    'discount' => 0,
                    'description' => 'A coming-of-age novel about teenage rebellion and identity.',
                    'language' => 'English',
                    'file_format' => 'PDF',
                    'cover_page_url' => '/images/book-4.jpg',
                    'file_url' => 'http://example.com/files/catcher-in-the-rye.pdf',
                    'tag' => json_encode(['Classic', 'Coming-of-Age']),
                    'isDownloadable' => true,
                ]
            ];

            foreach ($books as $book) {
                $user->books()->create($book);
            }
        } catch (Exception $e) {
            dd($e);
        }
    }
}
