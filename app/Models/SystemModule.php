<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class SystemModule extends Eloquent
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
	 
    protected $table = 'system_modules';
	
	/**
     * Define fillable columns
     *
     * @var string
     */
	
	protected $fillable = array(
	    'module_name',
		'status',
	);
	
	/**
     * Define timestamps
     *
     * @var string
     */
	 
	 public $timestamps = false;
	 
	
	 
 
}
