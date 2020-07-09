<?php

use ElementorPro\Modules\ThemeBuilder\Documents\Theme_Section_Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class customGrid extends Theme_Section_Document {

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['condition_type'] = '';
		$properties['location'] = 'custom_grid';

		return $properties;
	}

	public function get_name() {
		return 'custom_grid';
	}

	public static function get_title() {
		return __( 'Custom Grid', 'ele-custom-skin' );
	}

  protected static function get_editor_panel_categories() {
		$categories = [
			'ele-custom-grid' => [
				'title' => __( 'Custom Grid', 'elementor-pro' ),
			],
		];
    return $categories + parent::get_editor_panel_categories();
	
  }

/*

I want a preview like the template not default

*/
  
 
}

