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

    // IP Pool routes
    $app->group('/ippools', function () {
        $this->get('/customer/{customername}', Controllers\IPPoolController::class . ':getPoolByCustomerName')->setName('ippools-customer');
        $this->get('/netaddr/{netaddr}', Controllers\IPPoolController::class . ':getPoolByNetworkAddress')->setName('ippools-netaddr');
        // Default route to /ippools
        $this->get('[/{catchall}]', Controllers\IPPoolController::class . ':home')->setName('ippools');
    });

    // Virtual machine inventory routes
    $app->group('/vminventory', function () {
        // Default route to /vminventory
        $this->get('[/{catchall}]', Controllers\VMInventoryController::class . ':home')->setName('vminventory');
    });

    $app->group('/api', function () {
        $this->group('/v1', function () {
            $this->get('/custlist', Controllers\Api\CustomerController::class);

            $this->group('/utils', function () {
                $this->get('/devicetypes', Controllers\Api\UtilityController::class . ':getDeviceTypes');
            });

            $this->group('/ippools', function () {
                $this->group('/customer', function () {
                    $this->post('', Controllers\Api\IPPoolController::class . ':addRange');                            // C
                    $this->get('/{customername}', Controllers\Api\IPPoolController::class . ':getPoolByCustomerName'); // R
                    $this->put('', Controllers\Api\IPPoolController::class . ':updateRange');                          // U
                    $this->delete('', Controllers\Api\IPPoolController::class . ':deleteRange');                       // D
                });

                $this->group('/netaddr', function () {
                    $this->post('', Controllers\Api\IPPoolController::class . ':addIPAddress');                     // C
                    $this->get('/{netaddr}', Controllers\Api\IPPoolController::class . ':getPoolByNetworkAddress'); // R
                    $this->put('', Controllers\Api\IPPoolController::class . ':updateIPAddress');                   // U
                    $this->delete('', Controllers\Api\IPPoolController::class . ':deleteIPAddress');                // D
                });

                // Default route to /api/v1/ippools
                $this->get('[/{catchall}]', Controllers\Api\IPPoolController::class . ':getAll'); // R
            });

        });
    });

    // Default route to /home
    $app->get('/[{catchall}]', Controllers\HomeController::class);
