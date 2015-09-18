<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2015 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace Mmi\Controller\Request;

class Post extends \Mmi\DataObject {
	
	/**
	 * Konstruktor
	 * @param array $post dane z POST
	 */
	public function __construct(array $post = []) {
		$this->_data = $post;
	}
	
}