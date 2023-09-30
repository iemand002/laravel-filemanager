<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterImageUploadTable extends Migration
{
    protected $table;

    public function __construct()
    {
        $this->table = config('filemanager.table','uploads');
    }

    /**
     * Run the migrations.
     *
     */
    public function up()
    {
        if (Schema::hasTable($this->table)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->string('size')->after('dimension')->nullable();
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
            Schema::table($this->table, function (Blueprint $table) {
               $table->dropColumn('size');
            });
        }
    }
}