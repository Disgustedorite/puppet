<?php

define( 'MW_NO_SESSION', 1 );

require_once '/srv/mediawiki/config/initialise/WikiTideFunctions.php';
require WikiTideFunctions::getMediaWiki( 'includes/WebStart.php' );

use MediaWiki\MediaWikiServices;

function streamSitemapIndex() {
	global $wgDBname, $wmgUploadHostname;
	wfResetOutputBuffers();

	$url = "https://{$wmgUploadHostname}/{$wgDBname}/sitemaps/sitemap.xml";

	$req = RequestContext::getMain()->getRequest();
	if ( $req->getHeader( 'X-Sitemap-Loop' ) !== false ) {
		header( 'HTTP/1.1 500 Internal Server Error' );
		return;
	}

	$client = MediaWikiServices::getInstance()
		->getHttpRequestFactory()
		->create( $url );
	$client->setHeader( 'X-Sitemap-Loop', '1' );

	$status = $client->execute();
	if ( !$status->isOK() ) {
		header( 'HTTP/1.1 404 Not Found' );
		return;
	}

	$content = $client->getContent();
	header( 'Content-Length: ' . strlen( $content ) );
	header( 'Content-Type: ' . $client->getResponseHeader( 'Content-Type' ) );

	echo $content;
}

function streamSitemapFile() {
	global $wgDBname, $wmgUploadHostname;
	wfResetOutputBuffers();

	$filename = basename( $_SERVER['REQUEST_URI'] );

	$url = "https://{$wmgUploadHostname}/{$wgDBname}/sitemaps/{$filename}";

	$req = RequestContext::getMain()->getRequest();
	if ( $req->getHeader( 'X-Sitemap-Loop' ) !== false ) {
		header( 'HTTP/1.1 500 Internal Server Error' );
		return;
	}

	$client = MediaWikiServices::getInstance()
		->getHttpRequestFactory()
		->create( $url );
	$client->setHeader( 'X-Sitemap-Loop', '1' );

	$status = $client->execute();
	if ( !$status->isOK() ) {
		header( 'HTTP/1.1 404 Not Found' );
		return;
	}

	$content = $client->getContent();
	header( 'Content-Type: ' . $client->getResponseHeader( 'Content-Type' ) );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Content-Length: ' . strlen( $content ) );

	readfile( $url );
}

if ( !str_contains( $_SERVER['REQUEST_URI'], '/sitemaps/' ) ) {
	streamSitemapIndex();
} else {
	streamSitemapFile();
}
