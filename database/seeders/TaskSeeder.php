<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        
        $user = User::where('email', 'user@softxpert.com')->first();
        

        $task1 = Task::create([
            'title' => 'BackEnd Task',
            'description' => 'Complete The Api Integration Task',
            'assignee_id' => $user->id,
            'status' => 'pending',
            'due_date' => now()->addDays(5)
        ]);

        $task2 = Task::create([
            'title' => 'FrontEnd Task',
            'description' => 'Complete The LandingPage Task After Completing The BackEnd Task',
            'assignee_id' => $user->id,
            'status' => 'pending',
            'due_date' => now()->addDays(7)
        ]);

        
        $task2->dependencies()->attach($task1->id);
    }
}
