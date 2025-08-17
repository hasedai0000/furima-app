<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Purchase extends Model
{
 use HasFactory;

 protected $table = 'purchases';
 protected $keyType = 'string';
 public $incrementing = false;

 protected $fillable = [
  'user_id',
  'item_id',
  'payment_method',
  'postcode',
  'address',
  'building_name',
  'purchased_at',
 ];

 protected $casts = [
  'purchased_at' => 'datetime',
 ];

 protected static function boot()
 {
  parent::boot();

  static::creating(function ($model) {
   if (empty($model->id)) {
    $model->id = Str::uuid();
   }
  });
 }

 public function user()
 {
  return $this->belongsTo(User::class);
 }

 public function item()
 {
  return $this->belongsTo(Item::class);
 }
}
