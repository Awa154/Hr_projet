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
        Schema::create('fichepaie', function (Blueprint $table) {
            $table->id();
            $table->integer('tmp_hjour');
            $table->integer('tmp_jmois');
            $table->integer('tmp_hsup');
            $table->unsignedBigInteger('document_id');
            $table->foreign('document_id')->references('id')->on('document');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fichepaie');
    }
};
