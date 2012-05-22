<?php

# Copyright (C) 2008    John Reese
# Copyright (C) 2011    Reinhard Holler
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

require_once( 'columns_api.php' );

class TimecardEstimateColumn extends MantisColumn {

	public $title = '';

	public $column = "estimate";
	public $sortable = true;

	private $estimate_cache = array();

	public function __construct() {
		plugin_push_current( 'Timecard' );

		$this->title = plugin_lang_get( 'estimate' );

		plugin_pop_current();
	}

	public function cache( $p_bugs ) {
		if ( count( $p_bugs ) < 1 ) {
			return;
		}

		foreach ( $p_bugs as $t_bug ) {
			$t_bug_estimate = TimecardBug::load( $t_bug->id );
			$t_bug_estimate->calculate();
			if ( $t_bug_estimate->estimate > 0 ) {
			    $this->estimate_cache[ $t_bug->id ] = "$t_bug_estimate->spent/$t_bug_estimate->estimate";
				if ( $t_bug_estimate->spent > $t_bug_estimate->estimate ) {
					$this->estimate_cache[ $t_bug->id ] = '<span class="negative">' . $this->estimate_cache[ $t_bug->id ] . '</span>';
				}
			}
		}
	}

	public function sortquery( $p_dir ) {
		plugin_push_current( 'Timecard' );
		$t_estimate_table = plugin_table( 'estimate' );
		plugin_pop_current();
		$t_bug_table = db_get_table( 'mantis_bug_table' );
		return array('join' => "LEFT JOIN $t_estimate_table ON $t_estimate_table.bug_id = $t_bug_table.id",
			'order' => "$t_estimate_table.estimate $p_dir", );
	}

	public function display( $p_bug, $p_columns_target ) {
		plugin_push_current( 'Timecard' );

		if ( isset( $this->estimate_cache[ $p_bug->id ] ) ) {
			echo $this->estimate_cache[ $p_bug->id ], plugin_lang_get( 'hours' );
		}

		plugin_pop_current();
	}

}

