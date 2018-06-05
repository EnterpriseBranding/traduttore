<?php
/**
 * ProjectLocator class.
 *
 * @since 2.0.0
 */

namespace Required\Traduttore;

use GP;
use GP_Project;
use PO;

/**
 * Helper class to find a GlotPress project based on path, ID, or GitHub repository URL.
 *
 * @since 2.0.0
 */
class ProjectLocator {
	/**
	 * @var string|int Project information.
	 */
	protected $project;

	/**
	 * ProjectLocator constructor.
	 *
	 * @param string|int $project Project information.
	 */
	public function __construct( $project ) {
		$this->project = $project;
	}

	/**
	 * Returns the found project.
	 *
	 * @return GP_Project|false GlotPress project on success, false otherwise.
	 */
	public function get_project() {
		$project = GP::$project->by_path( $this->project );

		if ( is_numeric( $this->project ) ) {
			$project = GP::$project->get( (int) $this->project );
		}

		if ( ! $project ) {
			$project = GitHubUpdater::find_project( $this->project );
		}

		return $project;
	}

	/**
	 * Finds a GlotPress project by a GitHub repository URL, e.g. https://github.com/wearerequired/required-valencia.
	 *
	 * @since 2.0.0
	 * @return false|GP_Project Project on success, false otherwise.
	 */
	protected function find_project() {
		global $wpdb;

		$table = GP::$project->table;

		$query = $wpdb->prepare( "SELECT * FROM $table WHERE source_url_template LIKE %s LIMIT 1", '%' . $wpdb->esc_like( $this->project ) . '%' );

		return GP::$project->coerce( $wpdb->get_row( $query ) );
	}
}
