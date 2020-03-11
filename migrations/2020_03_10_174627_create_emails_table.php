<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateEmailsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('from', 100);
            $table->string('to', 100);
            $table->longText('text')->nullable()->comment('邮件纯文本');
            $table->longText('html')->nullable()->comment('邮件html文本');
            $table->longText('date')->comment('邮件寄送时间');
            $table->longText('subject')->nullable()->comment('邮件标题');
            $table->integer('is_read')->default(0)->comment('0未读1已读');   
            $table->string('from_name')->nullable()->comment('邮件来源姓名');
            $table->string('to_name')->nullable()->comment('邮件寄送姓名');
            $table->string('eml')->comment('eml文件路径');   
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
}
