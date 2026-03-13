<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMetadata extends Model
{
    protected $fillable = [
        'slug', 'meta_title', 'meta_description', 'meta_keywords', 
        'canonical_url', 'h1', 'text1', 'h2', 'text2', 
        'schema_markup', 'google_tag_manager', 'ads', 
        'seoable_id', 'seoable_type'
    ];

    public function seoable()
    {
        return $this->morphTo();
    }
}