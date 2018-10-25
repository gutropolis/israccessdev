<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class SystemRole extends Eloquent
{
    /**
     * Define table primary key
     *
     * @var string
     */
    
	protected $primaryKey = 'id';

    /**
     * Define table name
     *
     * @var string
     */
    
	protected $table = 'roles';
	
	/**
     * Define fillable columns
     *
     * @var string
     */
	 
	protected $fillable = array(
	    'title',
		'status',
	);
	
	/**
     * Define timestamps
     *
     * @var string
     */
	 
	 public $timestamps = false;
	 
	 /**
     * Define the relationship 
     *
     * @var string
     */
	 
	 public function userRoles(){
		 return $this->belongsTo('App\Models\SystemRole');
	 }
	
	
}
