<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'level',
        'language',
        'price',
        'currency',
    ];

    //INSTRUCTOR CAN CREATE A COURSE

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    //STUDENT CAN ENROLL IN A COURSE

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_enrollments', 'course_id', 'user_id');
    }

    //COURSE CAN HAVE MANY THREADS

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }
}
