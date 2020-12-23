<?php

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

	static function debug($arg, $debug=FALSE){
		echo '<pre>';
		print_r($arg);
		echo '</pre>';

		if($debug){
			die;
		}
	}

}
