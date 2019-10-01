<?php

namespace Kntnt\Post_Avatar;

class Substituter {

	private $ns;

	private $prefix;

	public function __construct() {
		$this->ns = Plugin::ns();
	}

	public function run() {

		$this->prefix = get_site_url( null, Plugin::option( 'prefix' ) );

		add_filter( 'pre_get_avatar_data', [ $this, 'get_avatar_data' ], 10, 2 );

	}

	public function get_avatar_data( $args, $id_or_email ) {

		if ( ! ( $image = $this->image( $id_or_email, $args ) ) ) {
			return $args;
		}

		$args['found_avatar'] = true;
		$args['url'] = $image[0];

		Plugin::log( 'Avatar data for user %s: %s', $id_or_email, $args );

		return $args;

	}

	private function image( $id_or_email, $args ) {

		$slug = $this->user_slug( $id_or_email );
		if ( ! $slug ) {
			Plugin::log( "Couldn't find user identified by '%s'", $id_or_email );
			return null;
		}

		$post_id = $this->post_id( $slug );
		if ( ! $post_id ) {
			Plugin::log( "Couldn't find a post with the slug '%s'", $slug );
			return null;
		}

		$image = $this->featured_image( $post_id, $args );
		if ( ! $image ) {
			Plugin::log( "No featured image in the post with the slug '%s'", $slug );
			return null;
		}

		Plugin::log( "Avatar for user %s: %s", $id_or_email, $image[0] );

		return $image;

	}

	private function user_slug( $id_or_email ) {

		if ( is_numeric( $id_or_email ) ) {
			$id = (int) $id_or_email;
			$user = get_user_by( 'id', $id );
		}
		else if ( is_object( $id_or_email ) ) {
			if ( ! empty( $id_or_email->user_id ) ) {
				$id = (int) $id_or_email->user_id;
				$user = get_user_by( 'id', $id );
			}
		}
		else {
			$user = get_user_by( 'email', $id_or_email );
		}

		if ( ! $user || is_wp_error( $user ) ) {
			return false;
		}

		return $user->user_nicename;

	}

	private function post_id( $slug ) {
		$url = Plugin::str_join( $this->prefix, $slug );
		$post_id = url_to_postid( $url );
		return $post_id;
	}

	private function featured_image( $post_id, $args ) {

		$attachment_id = get_post_thumbnail_id( $post_id );
		if ( ! $attachment_id ) {
			return null;
		}

		return wp_get_attachment_image_src( $attachment_id, [ $args['width'], $args['height'] ] );

	}

}
