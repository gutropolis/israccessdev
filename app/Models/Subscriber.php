<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Subscriber extends Eloquent
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
    protected $table = 'subscribers';

	/**
     * Define fillable columns
     *
     * @var string
     */
	
	protected $fillable = array(
	    'subscriber_email',
		'status'
	);
	
	/**
     * Define timestamps
     *
     * @var string
     */
	 
	 public $timestamps = false;
	
    
    
    
}
