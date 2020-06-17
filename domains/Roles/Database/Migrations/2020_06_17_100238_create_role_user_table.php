<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleUserTable extends Migration
{
    public function up(): void
    {
        Schema::create('role_user', static function (Blueprint $table) {
            $table->foreignId('user_id')
                ->index()
                ->constrained();

            $table->foreignId('role_id')
                ->index()
                ->constrained();
        });
    }
}
