<?php
    /**
     * The application route definitions
     *
     * PHP version 7
     *
     * @package MapPlatform
     * @author  Thimo Braker <thibmorozier@gmail.com>
     * @version 1.0.0
     * @since   First available since Release 1.0.0
     */
    use MapPlatform\Controllers;

    // About page routes
    $app->group('/about', function () {
        // Default route to /about
        $this->get('[/{catchall}]', Controllers\HomeController::class . ':about')->setName('about');
    });

    $app->post('/register', Controllers\AAAController::class . ':register')->setName('register');
    $app->post('/login', Controllers\AAAController::class . ':login')->setName('login');
    $app->get('/logout', Controllers\AAAController::class . ':logout')->setName('logout');

    // IP Pool routes
    $app->group('/ippools', function () {
        $this->get('/customer/{customername}', Controllers\IPPoolController::class . ':getPoolByCustomerName')->setName('ippools-customer');
        $this->get('/netaddr/{netaddr}', Controllers\IPPoolController::class . ':getPoolByNetworkAddress')->setName('ippools-netaddr');
        // Default route to /ippools
        $this->get('[/{catchall}]', Controllers\IPPoolController::class . ':home')->setName('ippools');
    });

    // API routes
    $app->group('/api', function () {
        $this->group('/v1', function () {
            $this->group('/maps', function () {
                $this->group('/user', function () {
                    $this->get('/user/{userId}', Controllers\Api\MapController::class . ':getMapsByUser'); // R
                });

                $this->post('', Controllers\Api\MapController::class . ':addMap');        // C
                $this->get('', Controllers\Api\MapController::class . ':getAllMaps');     // R
                $this->get('/{mapId}', Controllers\Api\MapController::class . ':getMap'); // R
                $this->put('', Controllers\Api\MapController::class . ':updateMap');      // U
                $this->delete('', Controllers\Api\MapController::class . ':deleteMap');   // D
            });

            $this->group('/download', function () {
                $this->get('[/{catchall}]', Controllers\Api\DownloadController::class);
            });

            $this->group('/rating', function () {
                $this->post('', Controllers\Api\RatingController::class . ':addRating');        // C
                $this->get('/{mapId}', Controllers\Api\RatingController::class . ':getRating'); // R
            });

            $this->group('/testscript', function () {
                $this->post('[/{catchall}]', Controllers\Api\MiscController::class . ':testScript');
            });

            $this->group('/resizedefault', function () {
                $this->get('[/{catchall}]', Controllers\Api\MiscController::class . ':resizeDefault');
            });
        });
    });

    // Default route to /home
    $app->get('/[{catchall}]', Controllers\HomeController::class . ':home')->setName('home');
