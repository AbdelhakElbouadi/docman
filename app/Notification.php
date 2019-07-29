<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    //
    protected $fillable = ['sender', 'recipient', 'doc_id', 'version', 'is_read', 'datetime', 'message'];
}
