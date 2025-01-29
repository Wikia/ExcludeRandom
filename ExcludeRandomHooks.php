<?php

use MediaWiki\Config\Config;
use MediaWiki\Hook\SpecialRandomGetRandomTitleHook;
use Wikimedia\Rdbms\ILoadBalancer;

readonly class ExcludeRandomHooks implements SpecialRandomGetRandomTitleHook {

	public function __construct(
		private Config $config,
		private ILoadBalancer $dbLoadBalancer,
	) {
	}

	public function onSpecialRandomGetRandomTitle( &$randstr, &$isRedir, &$namespaces, &$extra, &$title ): true {
		$wgExcludeRandomPages = $this->config->get( 'ExcludeRandomPages' );
		if ( !is_array( $wgExcludeRandomPages ) || empty( $wgExcludeRandomPages ) ) {
			return true;
		}

		$db = $this->dbLoadBalancer->getConnection( DB_REPLICA );
		foreach ( $wgExcludeRandomPages as $cond ) {
			$pattern = $db->addQuotes( $cond );
			$pattern = str_replace(
				[ '_', '%', ' ', '*' ],
				[ '\_', '\%', '\_', '%' ],
				$pattern
			);
			$extra[] = "`page_title` NOT LIKE $pattern";
		}

		return true;
	}
}
