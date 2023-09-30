<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class CreateImageTransformsTable extends Migration
{
    protected $table;
    /**
     * @var mixed
     */
    private $uploadsTable;

    public function __construct()
    {
        $this->table = Config::get('filemanager.transforms_table', 'uploads_transforms');
        $this->uploadsTable = Config::get('filemanager.table', 'uploads');
    }

    /**
     * Run the migrations.
     *
     */
    public function up()
    {
        if (!Schema::hasTable($this->table)) {
            Schema::create($this->table, function (Blueprint $table) {
                if (Config::get('filemanager.use_bigInteger', true)) {
                    $table->bigIncrements('id');
                    $table->bigInteger('upload_id')->unsigned();
                } else {
                    $table->increments('id');
                    $table->integer('upload_id')->unsigned();
                }
                $table->string('transform');
                $table->string('dimension')->nullable();
                $table->timestamps();
            });
            Schema::table($this->table, function(Blueprint $table) {
                $table->foreign('upload_id')->references('id')->on($this->uploadsTable)
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
                $table->dropForeign($this->table . '_upload_id_foreign');
            });
            Schema::drop($this->table);
        }
    }
}