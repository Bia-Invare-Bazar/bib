<?php

namespace App\Model\v1;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'userID';
    public $timestamps = false;

    protected $fillable=[
        'userName',
        'email',
        'password',
        'mobile'
    ];


}
