<?php

namespace Kntnt\Post_Avatar;

class Substituter {

	private $ns;

	private $prefix;

	private $allow_image_size_creation = false;

	public function __construct() {
		$this->ns = Plugin::ns();
	}

	public function run() {

		$this->prefix = get_site_url( null, Plugin::option( 'prefix' ) );

		add_filter( 'pre_get_avatar_data', [ $this, 'get_avatar_data' ], 10, 2 );
		add_filter( 'image_downsize', [ $this, 'image_downsize' ], 10, 3 );

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

	public function image_downsize( $image, $id, $size ) {

		if ( $this->allow_image_size_creation && $this->is_custom_image_size( $id, $size ) ) {
			$image = $this->create_image_size( $id, $size );
		}

		return $image;

	}

	private function image( $id_or_email, $args ) {

		$slug = $this->user_slug( $id_or_email );
		if ( ! $slug ) {
			Plugin::log( "Couldn't find user identified by '%s'", $id_or_email );
			return false;
		}

		$post_id = $this->post_id( $slug );
		if ( ! $post_id ) {
			Plugin::log( "Couldn't find a post with the slug '%s'", $slug );
			return false;
		}

		$image = $this->featured_image( $post_id, $args );
		if ( ! $image ) {
			Plugin::log( "No featured image in the post with the slug '%s'", $slug );
			return false;
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
			return false;
		}

		$this->allow_image_size_creation = true;
		$image = wp_get_attachment_image_src( $attachment_id, [ $args['width'], $args['height'] ] );
		$this->allow_image_size_creation = false;

		return $image;

	}

	private function is_custom_image_size( $id, $size ) {
		return is_array( $size ) && ! $this->image_size_exists( $id, $size ) ? true : false;
	}

	private function image_size_exists( $id, $size ) {

		$meta = wp_get_attachment_metadata( $id );

		foreach ( $meta['sizes'] as $available_size ) {
			if ( $size[0] == $available_size['width'] && $size[1] == $available_size['height'] ) {
				return true;
			}
		}

		return false;

	}

	private function create_image_size( $id, $size ) {

		$width = $size[0];
		$height = $size[1];

		if ( $image = $this->generate_image( get_attached_file( $id ), $width, $height, true ) ) {

			$path = $image['path'];
			unset( $image['path'] );

			$meta = wp_get_attachment_metadata( $id );
			$meta['sizes']["$this->ns-${width}x${height}"] = $image;
			$res = wp_update_attachment_metadata( $id, $meta );

			return [
				$this->convert_abspath_to_url( $path ),
				$width,
				$height,
				true,
			];

		}

		return false;

	}

	private function generate_image( $original_image, $width, $height, $crop ) {

		$img = wp_get_image_editor( $original_image );
		if ( is_wp_error( $img ) ) {
			Plugin::log( '%s', $img->get_error_message );
			return false;
		}

		$res = $img->resize( $width, $height, $crop );
		if ( is_wp_error( $res ) ) {
			Plugin::log( '%s', $res->get_error_message );
			return false;
		}

		$path = $img->generate_filename();

		$res = $img->save( $path );
		if ( is_wp_error( $res ) ) {
			Plugin::log( '%s', $res->get_error_message );
			return false;
		}

		Plugin::log( 'Created image: %s', $res['path'] );

		return $res;

	}

	private function convert_abspath_to_url( $path ) {
		return str_replace( Plugin::rel_wp_dir(), get_site_url() . '/', $path );
	}

}
