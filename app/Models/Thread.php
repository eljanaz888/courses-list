<?php

// app/Models/Thread.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    protected $fillable = ['title', 'body', 'course_id'];

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }
}
