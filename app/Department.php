<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    
    public function documents(){
    	return $this->belongsToMany('App\Document', 'department_document', 'dept_id', 'doc_id')->using('App\DepartmentDocument');
    }
}
