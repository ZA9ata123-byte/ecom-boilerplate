<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_login_with_correct_credentials(): void
    {
        // 1. كنصاوبو مستخدم حقيقي في قاعدة البيانات
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // 2. كنحاولو ندخلو بالمعلومات الصحيحة
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        // 3. كنتسناو جواب ناجح (200 OK) ونتأكدو أن فيه توكن
        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token']);
    }

    /** @test */
    public function it_returns_an_error_with_incorrect_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // كنحاولو ندخلو بمعلومات غالطة
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        // كنتسناو جواب ديال الخطأ (401 Unauthorized)
        $response->assertStatus(401);
    }
}
