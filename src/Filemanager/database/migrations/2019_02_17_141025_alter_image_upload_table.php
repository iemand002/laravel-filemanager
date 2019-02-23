<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class AlterImageUploadTable extends Migration
{
    protected $table;

    public function __construct()
    {
        $this->table = Config::get('filemanager.table','uploads');
    }

    /**
     * Run the migrations.
     *
     */
    public function up()
    {
        if (Schema::hasTable($this->table)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->string('key')->after('mimeType')->nullable();
                $table->string('provider')->after('key')->nullable();
                $table->dateTime('time_taken')->after('provider')->nullable();
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
               $table->dropColumn('key');
               $table->dropColumn('provider');
               $table->dropColumn('time_taken');
            });
        }
    }
}
