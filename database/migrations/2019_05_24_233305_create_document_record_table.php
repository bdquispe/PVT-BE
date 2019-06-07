<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('procedure_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('record_type_id');
            $table->unsignedBigInteger('wf_state_id');
            $table->unsignedBigInteger('recordable_id');
            $table->string('recordable_type');
            $table->text('message');
            $table->dateTime('date');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('record_type_id')->references('id')->on('record_types');
            $table->foreign('wf_state_id')->references('id')->on('wf_states');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('procedure_records');
    }
}
