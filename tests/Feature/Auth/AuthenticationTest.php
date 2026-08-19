<?php

use App\Models\User;
use App\Models\GuruBk;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route($user->dashboardRoute(), absolute: false));
});

test('approved guru can authenticate using nip', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_GURU,
        'status' => User::STATUS_APPROVED,
        'username' => '081234567890',
        'email' => null,
    ]);

    GuruBk::create([
        'user_id' => $user->id,
        'no_hp' => '081234567890',
        'nip' => '1987654321001',
        'jabatan' => 'Guru BK',
    ]);

    $response = $this->post('/login', [
        'selected_role' => User::ROLE_GURU,
        'login_id' => '1987654321001',
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('guru.dashboard', absolute: false));
});

test('approved guru can authenticate using formatted nip', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_GURU,
        'status' => User::STATUS_APPROVED,
        'username' => '081234567891',
        'email' => null,
    ]);

    GuruBk::create([
        'user_id' => $user->id,
        'no_hp' => '081234567891',
        'nip' => '1987654321002',
        'jabatan' => 'Guru BK',
    ]);

    $response = $this->post('/login', [
        'selected_role' => User::ROLE_GURU,
        'login_id' => '1987-6543-21002',
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('guru.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
