<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Server;

/**
 * The WebServer class.
 *
 * @since  3.0
 *
 * @deprecated
 */
class WebHttpServer extends HttpServer
{
    // /**
    //  * Property uri.
    //  *
    //  * @var  Uri
    //  */
    // protected $uri;

    // /**
    //  * Method to load the system URI strings for the application.
    //  *
    //  * @param   string $requestUri   An optional request URI to use instead of detecting one from the
    //  *                               server environment variables.
    //  *
    //  * @return  void
    //  *
    //  * @since   2.0
    //  */
    // protected function loadSystemUris($requestUri = null)
    // {
    //     $server = $this->getRequest()->getServerParams();
    //     $uri = $this->getSystemUri($requestUri);
    //
    //     $original = $requestUri ? new Uri($requestUri) : $this->getRequest()->getUri();
    //
    //     // Get the host and path from the URI.
    //     $host = $uri->withQuery('')->withFragment('')->withPath('')->__toString();
    //     $path = rtrim($uri->getPath(), '/\\');
    //     $script = trim(ServerHelper::getValue($server, 'SCRIPT_NAME', ''), '/');
    //
    //     // Check if the path includes "index.php".
    //     if ($script && strpos($path, $script) === 0) {
    //         // Remove the index.php portion of the path.
    //         $path = substr_replace($path, '', strpos($path, $script), strlen($script));
    //         $path = rtrim($path, '/\\');
    //     }
    //
    //     $scriptName = pathinfo($script, PATHINFO_BASENAME);
    //
    //     // Set the base URI both as just a path and as the full URI.
    //     $this->uriData->full = rtrim($original->__toString(), '/');
    //     $this->uriData->current = rtrim($original->withQuery('')->withFragment('')->__toString(), '/');
    //     $this->uriData->script = $scriptName;
    //     $this->uriData->root = $host . $path;
    //     $this->uriData->host = $host;
    //     $this->uriData->path = $path;
    //
    //     // Set the extended (non-base) part of the request URI as the route.
    //     $route = substr_replace($this->uriData->current, '', 0, strlen($this->uriData->root));
    //     $route = ltrim($route, '/');
    //
    //     // Only variables should be passed by reference so we use two lines.
    //     $file = explode('/', $script);
    //     $file = array_pop($file);
    //
    //     if ($file === '' || 0 === strpos($route, $file)) {
    //         $route = trim(substr($route, strlen($file)), '/');
    //     }
    //
    //     $this->uriData->route = $route;
    // }
    //
    // /**
    //  * Get system Uri object.
    //  *
    //  * @param   string $requestUri The request uri string.
    //  * @param   bool   $refresh    Refresh the uri.
    //  *
    //  * @return  PsrUri  The system Uri object.
    //  *
    //  * @since   2.0
    //  */
    // protected function getSystemUri($requestUri = null, $refresh = false)
    // {
    //     if ($this->uri && !$refresh) {
    //         return $this->uri;
    //     }
    //
    //     $uri = $requestUri ? new Uri($requestUri) : $this->getRequest()->getUri();
    //
    //     $server = $this->getRequest()->getServerParams();
    //
    //     // If we are working from a CGI SAPI with the 'cgi.fix_pathinfo' directive disabled we use PHP_SELF.
    //     if (str_contains(PHP_SAPI, 'cgi') && !ini_get('cgi.fix_pathinfo') && !empty($server['REQUEST_URI'])) {
    //         // We aren't expecting PATH_INFO within PHP_SELF so this should work.
    //         $uri = $uri->withPath(rtrim(\dirname((string) ServerHelper::getValue($server, 'PHP_SELF')), '/\\'));
    //     } else {
    //         // Pretty much everything else should be handled with SCRIPT_NAME.
    //         $uri = $uri->withPath(rtrim(\dirname((string) ServerHelper::getValue($server, 'SCRIPT_NAME')), '/\\'));
    //     }
    //
    //     // Clear the unused parts of the requested URI.
    //     $uri = $uri->withFragment('');
    //
    //     return $this->uri = $uri;
    // }
    //
    // /**
    //  * Method to get property UriData
    //  *
    //  * @return  UriData
    //  */
    // public function getUriData()
    // {
    //     return $this->uriData;
    // }
    //
    // /**
    //  * Method to set property uriData
    //  *
    //  * @param   array|UriData $uriData
    //  *
    //  * @return  static  Return self to support chaining.
    //  */
    // public function setUriData($uriData)
    // {
    //     if (!$uriData instanceof UriData) {
    //         $uriData = new UriData($uriData);
    //     }
    //
    //     $this->uriData = $uriData;
    //
    //     return $this;
    // }
}
