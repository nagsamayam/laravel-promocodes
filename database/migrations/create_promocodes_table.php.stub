<?php

declare(strict_types=1);

use App\Models\User;
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
    public function up()
    {
        $models = config('promocodes.models');

        Schema::create($models['promocodes']['table_name'], function (Blueprint $table) use ($models) {
            $table->id();
            $table->foreignId($models['users']['foreign_id'])
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('code', 20)->unique();
            $table->enum('type', ['flat', 'percent']);
            $table->integer('usages_left')->default(1);
            $table->boolean('bound_to_user')->default(false);
            $table->boolean('multi_use')->default(false);
            $table->json('details')->nullable();
            $table->unsignedBigInteger('min_order_value')->nullable();
            $table->unsignedBigInteger('max_discount')->nullable();
            $table->string('status')->default('inactive');
            $table->foreignIdFor(User::class, 'created_by_admin_id')->nullable();
            $table->foreignIdFor(User::class, 'updated_by_admin_id')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $models = config('promocodes.models');

        Schema::drop($models['promocodes']['table_name']);
    }
};
