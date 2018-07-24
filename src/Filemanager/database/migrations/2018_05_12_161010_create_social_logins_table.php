<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocialLoginsTable extends Migration {

    protected $table;

    public function __construct()
    {
        $this->table = config('filemanager.social_table', 'social_logins');
    }

	public function up()
	{
	    if (! Schema::hasTable($this->table)) {
            Schema::create($this->table, function(Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('provider');
                $table->string('social_id');
                $table->string('token');
                $table->timestamps();
            });
        }
    }

	public function down()
	{
        if (Schema::hasTable($this->table)) {
            Schema::drop($this->table);
        }
	}
}