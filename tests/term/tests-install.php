<?php

class cnTests_Install extends WP_UnitTestCase {

	/**
	 * Validate get_category_by_slug function
	 */
	function test_get_category_by_slug() {

		// create Test Categories
		$testcat = self::factory()->category->create_and_get(
			array(
				'slug' => 'testcat',
				'name' => 'Test Category 1'
			)
		);
		$testcat2 = self::factory()->category->create_and_get(
			array(
				'slug' => 'testcat2',
				'name' => 'Test Category 2'
			)
		);

		// validate category is returned by slug
		$ret_testcat = get_category_by_slug( 'testcat' );
		$this->assertEquals( $testcat->term_id, $ret_testcat->term_id );
		$ret_testcat = get_category_by_slug( 'TeStCaT' );
		$this->assertEquals( $testcat->term_id, $ret_testcat->term_id );

		// validate unknown category returns false
		$this->assertFalse( get_category_by_slug( 'testcat3' ) );

	}

	public function test_category_exists() {

		$term = cnTerm::getBy( 'name', 'Uncategorized', 'category', 0 );

		if ( ! is_wp_error( $term ) ) {

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

		} else {

			$this->assertTrue( FALSE, 'WP Error occurred. Uncategorized category not found.' );
		}

	}
}
