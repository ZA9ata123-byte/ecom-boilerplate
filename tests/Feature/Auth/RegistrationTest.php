<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase; // هادي كتضمن أن قاعدة البيانات كتكون خاوية قبل كل اختبار

    /** @test */
    public function a_user_can_register(): void
    {
        // 1. البيانات اللي غنصيفطوها
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 2. كنصيفطو طلب POST للـ API ديالنا
        $response = $this->postJson('/api/register', $userData);

        // 3. كنتأكدو من النجاح
        $response->assertStatus(201); // كنتأكدو أن الجواب هو 201 Created

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
        ]); // كنتأكدو أن المستخدم تزاد في قاعدة البيانات
    }
}