<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'description', 'image', 'show_in_navbar', 'navbar_order', 'parent_id'];

    protected $casts = [
        'show_in_navbar' => 'boolean',
        'navbar_order' => 'integer',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = Str::slug($category->name);
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function scopeNavbarCategories($query)
    {
        return $query->where('show_in_navbar', true)
                    ->whereNull('parent_id')
                    ->orderBy('navbar_order')
                    ->with('children');
    }
}
