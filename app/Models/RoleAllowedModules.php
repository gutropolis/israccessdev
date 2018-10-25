<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class RoleAllowedModules extends Eloquent
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
    
	protected $table = 'role_allowed_modules';
	
	/**
     * Define fillable columns
     *
     * @var string
     */
	 
	protected $fillable = array(
	    'role_id',
		'module_id',
		'function_id'
	);
	
	/**
     * Define timestamps
     *
     * @var string
     */
	 
	 public $timestamps = false;
	 
	
	
}
