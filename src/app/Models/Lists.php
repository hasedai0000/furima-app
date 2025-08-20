<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Lists extends Model
{
 use HasFactory;

 protected $table = 'lists';
 protected $keyType = 'string';
 public $incrementing = false;

 protected $fillable = [
  'user_id',
  'item_id',
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
