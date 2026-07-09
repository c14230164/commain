<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('respondent_variable', function (Blueprint $table) {
            $table->foreignId('respondent_id')->constrained()->onDelete('cascade');
            $table->foreignId('variable_id')->constrained()->onDelete('cascade');
            $table->primary(['respondent_id', 'variable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respondent_variable');
    }
};
