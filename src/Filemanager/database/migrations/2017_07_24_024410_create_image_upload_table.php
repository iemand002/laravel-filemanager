<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImageUploadTable extends Migration
{
    public function __construct()
    {
        $this->table = Config::get('filemanager.table');
    }

    /**
     * Run the migrations.
     *
     */
    public function up()
    {
        if (! Schema::hasTable($this->table)) {
            Schema::create($this->table, function (Blueprint $table) {
                $table->increments('id');
                $table->string('filename')->nullable();
                $table->string('folder')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     */
    public function down()
    {
        if (Schema::hasTable($this->table)) {
            Schema::drop($this->table);
        }
    }
}
