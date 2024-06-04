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
        Schema::create('contrat', function (Blueprint $table) {
            $table->id();
            $table->string('clause');
            $table->unsignedBigInteger('document_id');
            $table->foreign('document_id')->references('id')->on('document');
            $table->unsignedBigInteger('employe_id');
            $table->foreign('employe_id')->references('id')->on('employe');
            $table->unsignedBigInteger('entreprise_id');
            $table->foreign('entreprise_id')->references('id')->on('entreprise');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrat');
    }
};
