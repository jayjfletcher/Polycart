<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku');
            $table->unsignedInteger('price')->nullable();
            $table->unsignedInteger('stock')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->string('name');
        });

        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('person_team', function (Blueprint $table) {
            $table->foreignId('person_id')->constrained('people');
            $table->foreignId('team_id')->constrained();
        });
    }
};
