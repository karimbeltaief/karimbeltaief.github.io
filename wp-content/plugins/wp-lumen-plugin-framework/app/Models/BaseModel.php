<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
  // @see config/database.php 
  protected $connection = 'mysql';
}