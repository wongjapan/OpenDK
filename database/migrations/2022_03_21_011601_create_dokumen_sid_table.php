<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDokumenSidTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dokumen_sid', function (Blueprint $table) {
            $table->integer('id_sid');
            $table->integer('data_desa_id');
            $table->string('path', 255);
            $table->dateTime('imported_at')->nullable(true);
            $table->timestamps();
            $table->unique( ['data_desa_id', 'id_sid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dokumen_sid');
    }
}
