<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Item extends Model
{
 use HasFactory;

 protected $keyType = 'string';
 public $incrementing = false;

 protected $fillable = [
  'user_id',
  'name',
  'brand_name',
  'description',
  'price',
  'condition',
  'img_url',
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

 public function categories()
 {
  return $this->belongsToMany(Category::class, 'item_categories');
 }

 public function likes()
 {
  return $this->hasMany(Like::class);
 }

 public function comments()
 {
  return $this->hasMany(Comment::class);
 }

 public function purchases()
 {
  return $this->hasMany(Purchase::class);
 }

 public function lists()
 {
  return $this->hasMany(Lists::class);
 }
}
