<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up()
    {
        Schema::table('produksi_bulanan', function (Blueprint $table) {
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::table('produksi_bulanan', function (Blueprint $table) {
            
        });
    }
};
