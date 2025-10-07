<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_role_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->string('azure_group_id', 36);
            $table->string('azure_group_name', 100)->nullable(); // ← NEW LINE
            $table->timestamps();
            
            $table->unique(['module_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_role_groups');
    }
};