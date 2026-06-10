<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->where('is_active', true)->orderBy('sort_order');
    }

    public function allChildren()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function katalogs()
    {
        return $this->hasMany(Katalog::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }

    // Helper methods
    public function isParent()
    {
        return is_null($this->parent_id);
    }

    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    public function getFullNameAttribute()
    {
        if ($this->parent) {
            return $this->parent->name . ' > ' . $this->name;
        }
        return $this->name;
    }

    public function getBreadcrumbAttribute()
    {
        $breadcrumb = collect([$this]);
        
        $parent = $this->parent;
        while ($parent) {
            $breadcrumb->prepend($parent);
            $parent = $parent->parent;
        }
        
        return $breadcrumb;
    }

    // Get all catalog count including children
    public function getAllCatalogCountAttribute()
    {
        $count = $this->katalogs()->count();
        
        foreach ($this->children as $child) {
            $count += $child->getAllCatalogCountAttribute();
        }
        
        return $count;
    }
}
