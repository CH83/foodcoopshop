<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Plugin;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

/**
 * The default class to use for all routes
 *
 * The following route classes are supplied with CakePHP and are appropriate
 * to set as the default:
 *
 * - Route
 * - InflectedRoute
 * - DashedRoute
 *
 * If no call is made to `Router::defaultRouteClass()`, the class used is
 * `Route` (`Cake\Routing\Route\Route`)
 *
 * Note that `Route` does not do any inflections on URLs which will result in
 * inconsistently cased URLs when used with `:plugin`, `:controller` and
 * `:action` markers.
 *
 */
Router::defaultRouteClass(DashedRoute::class);

Router::scope('/', function (RouteBuilder $routes) {
    
    $routes->connect('/', ['controller' => 'pages', 'action' => 'home']);
    
    $routes->connect('/anmelden', ['controller' => 'customers', 'action' => 'login']);
    $routes->connect('/registrierung', ['controller' => 'customers', 'action' => 'login']);
    $routes->connect('/registrierung/abgeschlossen', ['controller' => 'customers', 'action' => 'registrationSuccessful']);
    $routes->connect('/logout', ['controller' => 'customers', 'action' => 'logout']);
    $routes->connect('/Informationen-ueber-Ruecktrittsrecht', ['controller' => 'carts', 'action' => 'generateCancellationInformationPdf']);
    $routes->connect('/nutzungsbedingungen', ['controller' => 'pages', 'action' => 'termsOfUse']);
    $routes->connect('/datenschutzerklaerung', ['controller' => 'pages', 'action' => 'privacyPolicy']);
    $routes->connect('/nutzungsbedingungen-akzeptieren', ['controller' => 'customers', 'action' => 'acceptUpdatedTermsOfUse']);
    
    $routes->connect('/neue-produkte', ['controller' => 'categories', 'action' => 'newProducts']);
    $routes->connect('/neues-passwort-anfordern', ['controller' => 'customers', 'action' => 'newPasswordRequest']);
    $routes->connect('/neues-passwort-generieren/:changePasswordCode', ['controller' => 'customers', 'action' => 'generateNewPassword']);
    
    $routes->connect('/aktuelles', ['controller' => 'blog_posts', 'action' => 'index']);
    $routes->connect('/aktuelles/*', ['controller' => 'blog_posts', 'action' => 'detail']);
    $routes->connect('/suche/*', ['controller' => 'categories', 'action' => 'search']);
    $routes->connect('/kategorie/*', ['controller' => 'categories', 'action' => 'detail']);
    $routes->connect('/produkt/*', ['controller' => 'products', 'action' => 'detail']);
    $routes->connect('/hersteller', ['controller' => 'manufacturers', 'action' => 'index']);
    $routes->connect('/hersteller/:manufacturerSlug/aktuelles', ['controller' => 'blog_posts', 'action' => 'index']);
    $routes->connect('/hersteller/*', ['controller' => 'manufacturers', 'action' => 'detail']);
    $routes->connect('/content/*', ['controller' => 'pages', 'action' => 'detail']);
    $routes->connect('/warenkorb/anzeigen', ['controller' => 'carts', 'action' => 'detail']);
    $routes->connect('/warenkorb/abschliessen', ['controller' => 'carts', 'action' => 'finish']);
    $routes->connect('/warenkorb/abgeschlossen/*', ['controller' => 'carts', 'action' => 'orderSuccessful']);
    $routes->connect('/warenkorb/:action', ['controller' => 'carts']);
    
    // für normale cake routings (users controller)
    $routes->connect('/:controller/:action');
    
    /**
     * Connect catchall routes for all controllers.
     *
     * Using the argument `DashedRoute`, the `fallbacks` method is a shortcut for
     *    `$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);`
     *    `$routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);`
     *
     * Any route class can be used with this method, such as:
     * - DashedRoute
     * - InflectedRoute
     * - Route
     * - Or your own route class
     *
     * You can remove these routes once you've connected the
     * routes you want in your application.
     */
    $routes->fallbacks(DashedRoute::class);
});
    
/**
 * Load all plugin routes. See the Plugin documentation on
 * how to customize the loading of plugin routes.
 */
Plugin::routes();
