<?php
namespace App\Models;
//use Illuminate\Database\Query\Builder@where;
use Illuminate\Database\Eloquent\Model as Eloquent;


class Seats extends Eloquent
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
    protected $table = 'seats';
	
	/**
     * Define fillable columns
     *
     * @var string
     */
	
	protected $fillable = array(
        'unique_id',
        'name', //letter 
        'area', //section
        'row', //range
        'auditorium_id',
        'pos_x',
        'pos_y',
        'price',
        'status'
	);
	
	/**
     * Define timestamps
     *
     * @var string
     */
	 
	 public $timestamps = false;
	
	 
    
    
    
}
