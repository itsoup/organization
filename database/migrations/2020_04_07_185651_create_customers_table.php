<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    public function up(): void
    {
        Schema::create('customers', static function (Blueprint $table) {
            $table->id();
            $table->string('vat_number')->unique();
            $table->string('name')->index();
            $table->text('address')->nullable();
            $table->string('country', 2)->index();
            $table->string('logo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
