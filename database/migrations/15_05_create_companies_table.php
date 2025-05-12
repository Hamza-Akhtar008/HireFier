<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		Schema::create('companies', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('user_id')->unsigned();
			$table->string('name', 191);
			$table->string('logo_path', 255)->nullable();
			$table->text('description')->nullable();
			$table->string('country_code', 2)->nullable();
			$table->bigInteger('city_id')->unsigned()->nullable();
			$table->string('address', 255)->nullable();
			$table->string('phone', 60)->nullable();
			$table->string('fax', 60)->nullable();
			$table->string('email', 100)->nullable();
			$table->string('website', 255)->nullable();
			$table->string('facebook', 255)->nullable();
			$table->string('twitter', 255)->nullable();
			$table->string('linkedin', 255)->nullable();
			$table->string('googleplus', 255)->nullable();
			$table->string('pinterest', 255)->nullable();
			$table->timestamps();
			
			$table->index(['user_id']);
			$table->index(['country_code']);
			$table->index(['city_id']);
			$table->index(['created_at']);
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		Schema::dropIfExists('companies');
	}
};
