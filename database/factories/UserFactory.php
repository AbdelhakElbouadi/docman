<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\User;
use Illuminate\Support\Facades\Hash;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {
    return [
        "name" => $faker->name,
        "email" => $faker->unique()->safeEmail,
        "email_verified_at" => now(),
        "password" => Hash::make('secret'),
        "dept_id" => 1,
        "remember_token" => Str::random(10)
    ];
});
