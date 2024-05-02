<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class TrackingHistory extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $table = 'rastreio_historico';
    protected $primaryKey = 'id';
}