<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class Tracking extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $table = 'rastreio';
    protected $primaryKey = 'id';
}