<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'profiles';
    
    protected $guarded = ['id'];
    
    protected $fillable = [
        'cod', // code
        'nme', // name
        'hsb', // head_subtitle 
        'mds', // me_desc 
        'msk', // me_skills 
        'ssb', // srv_subtitle 
        'sci', // srv_crd_icon 
        'sct', // srv_crd_title 
        'scd', // srv_crd_desc 
        'created_at',
        'updated_at'
    ];
}