<?php 

namespace Components\Translatable;

use Illuminate\Database\Eloquent\Model;
use Components\Translatable\Translation;
use Exception;

/**
* Translation
*/
class TranslationFactory 
{
	
	public static function make($table, $related = null, $key = 'id')
	{
		if (is_null($table)) throw new Exception("Translation table not set");
			
		$model = new Translation;
		$model->setTable($table);
		$model->setKeyName($key);
		$model->setRelated($related);

		return $model;
	}

}