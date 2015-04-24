<?php

use WordPress\Tabulate\DB\Grants;

class SchemaTest extends TestBase {

	/**
	 * @testdox Tabulate lists all tables in the database (by default, only for users with the 'promote_users' capability).
	 * @test
	 */
	public function no_access() {
		global $current_user;

		// Make sure they can't see anything yet.
		$current_user->remove_cap( 'promote_users' );
		$this->assertFalse( current_user_can( 'promote_users' ) );
		$this->assertEmpty( $this->db->get_table_names() );

		// Now promote them, and try again.
		$current_user->add_cap( 'promote_users' );
		$this->assertTrue( current_user_can( 'promote_users' ) );
		$table_names = $this->db->get_table_names();
		// A WP core table.
		$this->assertContains( $this->wpdb->prefix . 'posts', $table_names );
		// And one of ours.
		$this->assertContains( 'test_table', $table_names );
	}

	/**
	 * @testdox Tables can be linked to each other; one is the referenced table, the other the referencing.
	 * @test
	 */
	public function references() {
		// Make sure the user can edit things.
		global $current_user;
		$current_user->add_role( 'administrator' );
		$grants = new Grants();
		$grants->set(
			array(
				'test_table' => array( Grants::READ => array( 'administrator' ), ),
			)
		);

		// That test_table references test_types
		$test_table = $this->db->get_table( 'test_table' );
		$referenced_tables = $test_table->get_referenced_tables( true );
		$referenced_table = array_pop( $referenced_tables );
		$this->assertEquals( 'test_types', $referenced_table->get_name() );

		// And the other way around.
		$type_table = $this->db->get_table( 'test_types' );
		$referencing_tables = $type_table->get_referencing_tables();
		$referencing_table = array_pop( $referencing_tables );
		$this->assertEquals( 'test_table', $referencing_table->get_name() );
	}

	/**
	 * @testdox A not-null column "is required" but if it has a default value then no value need be set when saving.
	 * @test
	 */
	public function requiredColumns() {
		// 'widget_size' is a not-null column with a default value.
		$test_table = $this->db->get_table( 'test_table' );
		$widget_size_col = $test_table->get_column( 'widget_size' );
		$this->assertFalse( $widget_size_col->is_required() );
		// 'title' is a not-null column with no default.
		$title_col = $test_table->get_column( 'title' );
		$this->assertTrue( $title_col->is_required() );

		// Create a basic record.
		$widget = array(
			'title' => 'Test Item'
		);
		$test_table->save_record( $widget );
		$this->assertEquals( 1, $test_table->count_records() );
		$widget_records = $test_table->get_records();
		$widget_record = array_shift( $widget_records );
		$this->assertEquals(5.6, $widget_record->widget_size());
	}

}
