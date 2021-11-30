<?php

namespace Code_Manager {

	class Code_Manager_Preview {

		/**
		 * Holds all current user/code_id combinations that are preview enabled
		 *
		 * @var array
		 */
		private static $preview        = [];
		private static $preview_loaded = false;

		/**
		 * Load preview code ids for current user
		 */
		private static function load_user_preview_codes() {
			if ( ! self::$preview_loaded ) {
				$code_manager_preview_code_ids = get_user_meta(
					get_current_user_id(),
					'code_manager_preview_code_ids',
					true
				);

				if ( false !== $code_manager_preview_code_ids && is_array( $code_manager_preview_code_ids ) ) {
					self::$preview = $code_manager_preview_code_ids;
				}

				self::$preview_loaded = true;
			}
		}

		/**
		 * Return preview code ids for current user
		 *
		 * @return array
		 */
		public static function get_user_preview_codes() {
			self::load_user_preview_codes();

			return self::$preview;
		}

		/**
		 * Check if preview for given code id is enabled for current user
		 *
		 * @param $code_id
		 */
		public static function is_code_id_preview_enabled( $code_id ) {
			self::load_user_preview_codes();

			return is_array( self::$preview ) && in_array( $code_id, self::$preview );
		}

		/**
		 * Enable preview mode for given code id for current user
		 *
		 * @param $code_id
		 */
		public static function add_user_preview_code_id( $code_id ) {
			if ( is_admin() ) {
				self::load_user_preview_codes();

				if ( is_array( self::$preview ) && ! in_array( $code_id, self::$preview ) ) {
					array_push( self::$preview, $code_id );

					update_user_meta( get_current_user_id(), 'code_manager_preview_code_ids', self::$preview );
				}
			}
		}

		/**
		 * Disable preview mode for given code id for current user
		 *
		 * @param $code_id
		 */
		public static function remove_user_preview_code_id( $code_id ) {
			if ( is_admin() ) {
				self::load_user_preview_codes();

				if ( is_array( self::$preview ) ) {
					if ( ( $key = array_search( $code_id, self::$preview ) ) !== false ) {
						unset( self::$preview[ $key ] );
					}

					update_user_meta( get_current_user_id(), 'code_manager_preview_code_ids', self::$preview );
				}
			}
		}

	}

}