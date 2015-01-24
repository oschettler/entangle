<?php namespace Entangle;

use Granada\ORM;
use Granada\Model;

class App
{
	/**
	 * Similar to dispatch.scope, but keep values as stack
	 */
	public static function stack($name, $value = null)
	{

		static $_stash = array();

		if ($value === null) {
			return isset($_stash[$name]) ? array_pop($_stash[$name]) : NULL;
		}

		if (!isset($_stash[$name])) {
			$_stash[$name] = array();
		}
		return array_push($_stash[$name], $value);
	}

	public static function bootstrap()
	{
		config('dispatch.views', 'views');

		$here = __DIR__ . '/../..';
		if ( ! file_exists( "{$here}/settings.ini" ) ) {
			if ( strpos( $here, 'phar://' ) === 0 ) {
				$here = substr( $here, 7 );
				$here = substr( $here, 0, strpos( $here, '/entangle.phar' ) );

				on( 'GET', '/js/:file', function () {
					header( 'Content-type: application/javascript' );
					readfile( 'js/' . params( 'file' ) );
				} );
				on( 'GET', '/css/:file', function () {
					header( 'Content-type: text/css' );
					readfile( 'css/' . params( 'file' ) );
				} );
			}
			file_put_contents("{$here}/settings.ini",
				"; entangle! - https://entangle.de\n; Enter config options here\n"
			);
		}
		config('source', "{$here}/settings.ini");

		if (!config('db.name')) {
			config('db.name', "sqlite:{$here}/entangle.sqlite");
		}

		$needs_init = FALSE;
		if (!file_exists(preg_replace('/^sqlite:/', '', config('db.name')))) {
			$needs_init = TRUE;
		}

		ORM::configure(config('db.name'));
		ORM::configure('return_result_sets', TRUE);
		Model::$auto_prefix_models = '\\Model\\';

		if ($needs_init) {
			unset($_SESSION['user']);
			$db = ORM::get_db();
			foreach (explode(';', file_get_contents('db-entangle-sqlite.sql')) as $sql) {
				$db->exec($sql);
			}
			flash('success', 'Database has been set up. Now register your account');
			redirect('/user/register');
		}

		/*
		 * Database logging for the super user
		 */
		if (session('user') && $_SESSION['user']->id == 1) {
			ORM::configure('logging', true);
		}
	}
}