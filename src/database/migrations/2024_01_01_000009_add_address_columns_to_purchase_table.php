<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 /**
  * Run the migrations.
  */
 public function up(): void
 {
  Schema::table('purchases', function (Blueprint $table) {
   $table->string('payment_method')->nullable()->after('item_id');
   $table->string('postcode')->nullable()->after('payment_method');
   $table->string('address')->nullable()->after('postcode');
   $table->string('building_name')->nullable()->after('address');
  });
 }

 /**
  * Reverse the migrations.
  */
 public function down(): void
 {
  Schema::table('purchases', function (Blueprint $table) {
   $table->dropColumn(['payment_method', 'postcode', 'address', 'building_name']);
  });
 }
};
