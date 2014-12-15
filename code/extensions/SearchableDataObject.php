<?php

/**
 * 
 *
 * @author ilateral (http://www.ilateral.co.uk)
 * @package Searchable
 */
class SearchableDataObject extends DataExtension {

	public static function get_extra_config($class, $extensionClass, $args) {
		return array(
			'indexes' => array(
				'SearchFields' => array(
					'type' => 'fulltext',
					'name' => 'SearchFields',
					'value' => $args[0]
				)
			)
		);
	}
	
}
