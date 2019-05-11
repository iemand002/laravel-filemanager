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
        $this->table = config('filemanager.table','uploads');
        $this->usersTable = config('filemanager.users_table','users');
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
                $table->string('dimension')->after('provider')->nullable();
                $table->integer('added_by_id')->after('provider')->unsigned()->nullable();
                $table->dateTime('time_taken')->after('added_by_id')->nullable();
            });
            Schema::table($this->table, function(Blueprint $table) {
                $table->foreign('added_by_id')->references('id')->on($this->usersTable)
                    ->onDelete('restrict')
                    ->onUpdate('restrict');
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
            Schema::table($this->table, function(Blueprint $table) {
                $table->dropForeign($this->table . '_added_by_id_foreign');
            });
            Schema::table($this->table, function (Blueprint $table) {
               $table->dropColumn('key');
               $table->dropColumn('provider');
               $table->dropColumn('added_by_id');
               $table->dropColumn('time_taken');
            });
        }
    }
}
