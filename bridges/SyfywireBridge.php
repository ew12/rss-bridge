<?php
class SyfywireBridge extends BridgeAbstract {
	const NAME = 'syfy-wire';
	const URI = 'https://www.syfy.com/';
	const DESCRIPTION = 'syfy.com used to have a rss feed, but that feed is almost 1 year dead now(202209). So here we go.';
	const CACHE_TIMEOUT = 3600;

	public function collectData() {
		$html = getSimpleHTMLDOM ( self::URI.'syfy-wire' );
		
		foreach ( $html->find ( '.card-feed article' ) as $article ) {
			$item = [];
			if ( $article->hasClass ( 'promo' ) === false ) {
				$item['enclosures'] = array ( self::URI.$article->find('img', 0)->getAttribute('src') );
				$item['uri'] = $article->find ( 'a', 0 ) ->href;
				$item['title'] = $article->find ( 'h2.headline', 0 )->innertext();
				$item['timestamp'] = $article->find( '.date', 0 )->getAttribute ( 'datetime' );
				$item['author'] = $article->find ( '.author-name', 0 )->innertext();
				$item['content'] = $article->find ( '.description' , 0 )->innertext();
				$this->items[] = $item;
			}
		}
	}
}
