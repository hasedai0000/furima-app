<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
 use HasFactory;

 protected $table = 'categories';
 protected $keyType = 'string';
 public $incrementing = false;

 protected $fillable = [
  'name',
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

 public function items()
 {
  return $this->belongsToMany(Item::class, 'item_categories');
 }
}
