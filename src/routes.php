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
    use \MapPlatform\Controllers;

    // About page routes
    $app->group('/about', function () {
        // Default route to /about
        $this->get('[/{catchall}]', Controllers\HomeController::class . ':about')->setName('about');
    });

    // AAA routes
    $app->post('/register', Controllers\AAAController::class . ':register')->setName('register');
    $app->post('/login', Controllers\AAAController::class . ':login')->setName('login');
    $app->get('/logout', Controllers\AAAController::class . ':logout')->setName('logout');

    // Dashboard routes
    $app->get('/dashboard', Controllers\DashboardController::class . ':home')->setName('dashboard');

    $app->group('/profile', function() {
        $this->get('/edit', Controllers\ProfileController::class . ':editProfile')->setName('editProfile');
        $this->post('/edit', Controllers\ProfileController::class . ':saveProfile')->setName('saveProfile');
        $this->get('/{userId}', Controllers\ProfileController::class . ':getProfile')->setName('getProfile');
    });

    // Map routes
    $app->group('/map', function () {
        $this->get('/new', Controllers\MapController::class . ':newMap')->setName('newMap');
        $this->group('/{mapId}', function () {
            $this->get('', Controllers\MapController::class . ':getMap')->setName('getMap');
            $this->get('/updateinfo', Controllers\MapController::class . ':updateMapInfo')->setName('updateMapInfo');
            $this->get('/updatefiles', Controllers\MapController::class . ':updateMapFiles')->setName('updateMapFiles');
        });
    });

    $app->group('/admin_', function () {
        $this->get('flags', Controllers\FlagController::class . ':getFlags')->setName('adminFlags');
    });

    // Map routes
    $app->group('/images', function () {
        $this->get('/default/{imageName}', Controllers\ImageController::class . ':getDefaultImage')->setName('getDefaultImage');
        $this->get('/{revId}/{screenId}', Controllers\ImageController::class . ':getMapImage')->setName('getMapImage');
    });

    // API routes
    $app->group('/api', function () {
        $this->group('/v1', function () {
            $this->group('/maps', function () {
                $this->post('', Controllers\Api\MapController::class . ':addMap');                      // C
                $this->get('', Controllers\Api\MapController::class . ':getAllMaps');                   // R
                $this->get('/download/{revId}', Controllers\Api\MapController::class . ':downloadMap'); // R
                $this->get('/user/{userId}', Controllers\Api\MapController::class . ':getMapsByUser');  // R

                $this->group('/{mapId}', function () {
                    $this->get('', Controllers\Api\MapController::class . ':getMap');                      // R
                    $this->post('/updateinfo', Controllers\Api\MapController::class . ':updateMapInfo');   // U
                    $this->post('/updatefiles', Controllers\Api\MapController::class . ':updateMapFiles'); // U
                    $this->delete('', Controllers\Api\MapController::class . ':deleteMap');                // D
                });
            });

            $this->group('/flags', function () {
                $this->post('/map/{revId}', Controllers\Api\FlagController::class . ':flagMap'); // C
                $this->get('/queue', Controllers\Api\FlagController::class . ':getQueue');       // R
                $this->get('/mine', Controllers\Api\FlagController::class . ':getMine');         // R
            });

            $this->group('/rating', function () {
                $this->post('/{mapId}', Controllers\Api\RatingController::class . ':addRating'); // C
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
