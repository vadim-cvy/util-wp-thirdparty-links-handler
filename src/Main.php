<?php
namespace Cvy\WP\ThirdPartyLinksHandler;

class Main extends \Cvy\DesignPatterns\Singleton
{
  protected function __construct()
  {
    add_filter( 'the_content', fn( string $content ) => $this->handle_thirparty_links( $content ) );
  }

  private function handle_thirparty_links( string $html ) : string
  {
    foreach ( $this->find_thirparty_link_tags( $html ) as $tag )
    {
      $html = str_replace(
        $tag,
        preg_replace( '~^(<a)~', '$1 target="_blank" rel="noopener"', $tag ),
        $html
      );
    }

    return $html;
  }

  private function find_thirparty_link_tags( string $html ) : array
  {
    return array_filter(
      $this->extract_link_tags( $html ),
      fn( $link_tag ) => $this->is_thirparty( $link_tag )
    );
  }

  private function extract_link_tags( string $html ) : array
  {
    preg_match_all( '~<a.+?>~', $html, $matches );

    return $matches[0];
  }

  private function is_thirparty( string $link_tag ) : bool
  {
    preg_match( '~href="([^"]+)"~', $link_tag, $url_matches );

    $url = $url_matches[1] ?? '';

    $our_domain = parse_url( get_site_url(), PHP_URL_HOST );

    $is_our_absolute = strpos( $url, $our_domain ) !== false;

    $is_our_relative = strpos( $url, '/' ) === 0;

    return ! $is_our_absolute && ! $is_our_relative;
  }
}