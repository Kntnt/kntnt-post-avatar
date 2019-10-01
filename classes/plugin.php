<?php

namespace Kntnt\Post_Avatar;

class Plugin extends Abstract_Plugin {

	public function classes_to_load() {

		return [
			'public' => [
				'init' => [
					'Substituter',
				],
			],
			'admin' => [
				'init' => [
					'Settings',
				],
			],
		];

	}

}
