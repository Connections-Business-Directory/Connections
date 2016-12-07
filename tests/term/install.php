<?php

class cnTests_Install extends WP_UnitTestCase {
	/**
	 * @ticket 30975
	 */
	public function test_category_exists() {

		$term = cnTerm::getBy( 'name', 'Uncategorized', 'category', 0 );

		$this->assertEquals(
			array(
				'name' => 'Uncategorized',
				'slug' => 'uncategorized',
			),
			array(
				'name' => $term->name,
				'slug' => $term->slug,
			),
			'Uncategorized category not found.'
		);
	}
}
