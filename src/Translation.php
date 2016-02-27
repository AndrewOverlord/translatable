<?php 

namespace Components\Translatable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
* Translation
*/
class Translation extends Model
{
	
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table;

    /**
     * [$related description]
     * @var string
     */
    protected $related;

    /**
     * Get class off model to belongs the translation 
     * @return string
     */
    public function getRelated()
    {
    	return $this->related;
    }

    /**
     * Set related class
     * @param string $class
     */
    public function setRelated($class)
    {
    	$this->related = $class;
    }




    /*public function source()
    {
    	return $this->belongsTo($this->related);
    }*/

}