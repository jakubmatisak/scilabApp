<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experiment extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name', 'name', 'context', 'output', 'created_by', 'file_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
