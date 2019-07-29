<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    //
    protected $fillable = ['name', 'description', 'path', 'version', 
    'owner_id', 'status']; 


    public function reviews(){
    	return $this->hasMany('App\Review');
    }

    public function departments(){
    	return $this->belongsToMany('App\Department', 'department_document', 'doc_id', 'dept_id')->using('App\DepartmentDocument');
    }

    public function users(){
    	return $this->belongsToMany('App\Document', 'document_user', 'doc_id', 'user_id')->using('App\DocumentUser');
    }
}
