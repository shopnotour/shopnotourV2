<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('popup_messages', function (Blueprint $table) {
            $table->id();
            $table->string('page_key');                        // কোন page এর জন্য
            $table->string('title')->nullable();               // popup title
            $table->text('message');                           // popup body
            $table->enum('type', ['info', 'success', 'warning', 'danger'])->default('info');
            $table->boolean('is_active')->default(true);       // on/off
            $table->boolean('show_once')->default(false);      // একবার দেখালেই হবে কি না
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_messages');
    }
};
