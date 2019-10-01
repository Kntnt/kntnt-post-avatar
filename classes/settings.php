<?php

namespace Kntnt\Post_Avatar;

require_once Plugin::plugin_dir( 'classes/abstract-settings.php' );

class Settings extends Abstract_Settings {

	/**
	 * Returns the settings menu title.
	 */
	protected function menu_title() {
		return __( 'Post Avatar', 'kntnt-post-avatar' );
	}

	/**
	 * Returns the settings page title.
	 */
	protected function page_title() {
		return __( "Kntnt Post Avatar", 'kntnt-post-avatar' );
	}

	/**
	 * Returns all fields used on the settings page.
	 */
	protected function fields() {

		$fields['prefix'] = [
			'type' => 'text',
			'label' => __( "Post's URL prefix", 'kntnt-post-avatar' ),
			'size' => 80,
			'description' => sprintf( __( 'Enter the <em>PREFIX</em> part of this URL <em>%s/<strong>PREFIX</strong>/SLUG</em>, where <em>SLUG</em> is the slug (i.e. "nicename") of a user. If there is a post at the resolved URL and that post has a featured image, that image will be used as the user\'s avatar.', 'kntnt-post-avatar' ), get_site_url() ),
			'default' => '',
		];

		$fields['submit'] = [
			'type' => 'submit',
		];

		return $fields;

	}

}
