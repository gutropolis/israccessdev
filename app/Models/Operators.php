<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Operators extends Eloquent
{
    /**
     * Define table primary key
     *
     * @var string
     */
    protected $primaryKey = 'op_id';

    /**
     * Define table name
     *
     * @var string
     */
    protected $table = 'operator';
    
	/**
     * Define fillable columns
     *
     * @var string
     */
	
	protected $fillable = array(
	    'op_fullname',
		'op_fname',
        'op_lname',
	    'op_email',
		'op_phone',
		'password'
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
    
    
    /* public function view(){	
         echo "abjsklajfklajslkf";
	 } */
    
}
