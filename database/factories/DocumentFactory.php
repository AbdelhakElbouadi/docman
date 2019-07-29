<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Document;
use Faker\Generator as Faker;

$factory->define(Document::class, function (Faker $faker) {
    return [
        "name" => $faker->name,
        "description" => $faker->paragraph,
        "path" => $faker->uuid,
        "version" => $faker->ipv4,
        "owner_id" => 1,
        "status" => "NOT_REVIEWED" 
    ];
});
