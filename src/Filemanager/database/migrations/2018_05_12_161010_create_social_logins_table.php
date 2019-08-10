<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class CreateSocialLoginsTable extends Migration {

    protected $table;

    public function __construct()
    {
        $this->table = config('filemanager.social_table', 'social_logins');
        $this->usersTable = config('filemanager.users_table','users');
    }

	public function up()
	{
	    if (! Schema::hasTable($this->table)) {
            Schema::create($this->table, function(Blueprint $table) {
                if (Config::get('filemanager.use_bigInteger', true)) {
                    $table->bigIncrements('id');
                } else {
                    $table->increments('id');
                }
                if (Config::get('filemanager.use_bigInteger', true)) {
                    $table->bigInteger('user_id')->unsigned();
                } else {
                    $table->integer('user_id')->unsigned();
                }
                $table->string('provider');
                $table->string('social_id');
                $table->text('token');
                $table->integer('expires')->nullable();
                $table->text('refresh')->nullable();
                $table->timestamps();
            });
            Schema::table($this->table, function(Blueprint $table) {
                $table->foreign('user_id')->references('id')->on($this->usersTable)
                    ->onDelete('restrict')
                    ->onUpdate('restrict');
            });
        }
    }

	public function down()
	{
        if (Schema::hasTable($this->table)) {
            Schema::table($this->table, function(Blueprint $table) {
                $table->dropForeign($this->table . '_user_id_foreign');
            });
            Schema::drop($this->table);
        }
	}
}