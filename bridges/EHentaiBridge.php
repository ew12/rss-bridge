<?php

/**
 * Good resource on API return values (Ex: illustType):
 * https://hackage.haskell.org/package/pixiv-0.1.0/docs/Web-Pixiv-Types.html
 */
class EHentaiBridge extends BridgeAbstract
{
    const NAME = 'e-hentai';
    const URI = 'https://e-hentai.org/';
    const CACHE_TIMEOUT = 86400; // 24h
    const DESCRIPTION = 'Rssfied general search and tag search on e-hentai';
    const CONFIGURATION = [
        'cookie' => [
            'required' => false,
            'defaultValue' => null
        ],
        'proxy_url' => [
            'required' => false,
            'defaultValue' => null
        ]
    ];

    const PARAMETERS = [
        'global' => [
            'posts' => [
                'name' => 'Post Limit',
                'type' => 'number',
                'defaultValue' => '10'
            ],
        ],
        'Tag' => [
            'tag' => [
                'name' => 'tag search',
                'exampleValue' => 'artist:',
                'required' => true
            ]
        ],
        'Search' => [
            'search' => [
                'name' => 'general search',
                'exampleValue' => '',
                'required' => true
            ]
        ],
        'User' => [
            'uploader' => [
                'name' => 'username',
                'exampleValue' => '11',
                'required' => true
            ]
        ]
    ];

    public function getURI()
    {
        switch ($this->queriedContext) {
            case 'Tag':
                $uri = static::URI . 'tag/' . urlencode($this->getInput('tag') ?? '');
                break;
            case 'Search':
                $uri = static::URI . '?f_search=' . urlencode($this->getInput('search') ?? '');
                break;
            case 'User':
                $uri = static::URI . 'uploader/' . $this->getInput('userid');
                break;
            default:
                return parent::getURI();
        }
        return $uri;
    }

    public function collectData()
    {
        $this->checkOptions();
        $proxy_url = $this->getOption('proxy_url');
        $proxy_url = $proxy_url ? rtrim($proxy_url, '/') : null;

		$content = getSimpleHTMLDOM ( $this->getURI );

        //$content = array_slice($content, 0, $this->getInput('posts'));

        foreach ($content->find ( 'table.itg.gltc tr' ) as $result) {

            $item = [];
            if ( $result -> find ( 'img', 0 ) !== null ) {
                $item['enclosures'] = ( $result -> find ( 'img', 0 ) ->hasAttribute ( 'data-src' ) ) ? array ( $result->find('img', 0)->getAttribute('data-src') ) : array ( URI.$result -> find ( 'img', 0 ) -> getAttribute ( 'src' ) );
                //$item [ 'uri' ] = $result -> find ( 'gl3c glname', 0 ) -> find ( 'a', 0 ) -> href;
                $item [ 'uri' ] = $result -> find ( 'a', 1 ) -> href;
                $item [ 'title' ] = $result -> find ( 'glink', 0 ) -> innertext ();
                $item [ 'timestamp' ] = $result -> find ( 'div[onclick][!class]', 0 ) -> innertext ();
                $item [ 'author' ] = $result -> find ( 'td.gl4c.glhide div a', 0 ) -> innertext ();
                $item [ 'content' ] = str_replace ( "\n", '<br /', $result -> find ( '.glink + div', 0 ) -> text () . "&nbsp;&nbsp;" . $result -> find ( '.gl4c.glhide div + div', 0 ) -> text () );
            }
            
            $this->items[] = $item;
        }
    }

    private function checkOptions()
    {
        $proxy = $this->getOption('proxy_url');
        if ($proxy) {
            if (
                !(strlen($proxy) > 0 && preg_match('/https?:\/\/.*/', $proxy))
            ) {
                returnServerError('Invalid proxy_url value set. The proxy must include the HTTP/S at the beginning of the url.');
            }
        }
    }
}
