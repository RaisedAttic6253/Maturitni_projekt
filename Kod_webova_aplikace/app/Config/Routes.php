<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Hlavni::index');
$routes->get('teplota', 'Hlavni::teplota'); 
$routes->get('pohyb', 'Hlavni::pohyb');
$routes->get('vlhkost', 'Hlavni::vlhkost'); 
$routes->get('tlak', 'Hlavni::tlak'); 
$routes->get('nadmorska_vyska', 'Hlavni::nadmorska_vyska'); 
$routes->get('hlavni/ziskejDataGrafu', 'Hlavni::ziskejDataGrafu');
$routes->get('hlavni/dataPohybTimeline', 'Hlavni::dataPohybTimeline');
$routes->get('hlavni/aktualniDataDashboard', 'Hlavni::aktualniDataDashboard');

