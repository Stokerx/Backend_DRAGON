<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UsersHistory;
use App\Models\User;
use App\Models\Chapter;

class UsersHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        $chapters = Chapter::all();

        foreach ($users as $user) {
            foreach ($chapters as $chapter) {
                UsersHistory::factory()->create([
                    'user_id' => $user->id,
                    'chapter_id' => $chapter->id,
                ]);
            }
        }
    }
}
