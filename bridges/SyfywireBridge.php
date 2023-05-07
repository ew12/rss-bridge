<?php
class SyfywireBridge extends BridgeAbstract {
	const NAME = 'syfy-wire';
	const URI = 'https://www.syfy.com';
	const DESCRIPTION = 'syfy.com used to have a rss feed, but that feed is almost 1 year dead now(202209). So here we go.';
	const CACHE_TIMEOUT = 3600;

	public function collectData() {
		$html = getSimpleHTMLDOM ( self::URI.'/syfy-wire' );
		
		foreach ( $html->find ( '.card-feed article' ) as $article ) {
			$item = [];
			if ( $article->hasClass ( 'promo' ) === false || $article->hasClass ( 'teaser--related-posts' ) === false ) {
				$item['enclosures'] = array ( self::URI.$article->find('img', 0)->getAttribute('src') );
				$item['uri'] = self::URI.$article->find ( 'a', 0 ) ->href;
				$item['title'] = $article->find ( 'h2.headline', 0 )->innertext();
				$item['timestamp'] = $article->find( '.date', 0 )->getAttribute ( 'datetime' ) if $article->find ( '.date', 0 ) !== null else '';
				$item['author'] = $article->find ( '.author-name', 0 )->innertext() if $article->find ( '.author-name', 0 ) !== null else '';
				$item['content'] = $article->find ( '.description' , 0 )->innertext() if $article->find ( '.description', 0 ) !== null else '';
				$result = array_filter ( $item, function ( $val ) { return $val !== ''; });
				$this->items[] = $item if count ( $result ) == count ( $item );
			}
		}
	}
}
