<?php
    /**
     * The central controller for the map pages
     *
     * PHP version 7
     *
     * @package    MapPlatform
     * @subpackage Controllers
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    namespace MapPlatform\Controllers;

    use \Slim\Http\Request;
    use \Slim\Http\Response;
    use \MapPlatform\Core;
    use \MapPlatform\AbstractClasses\PageController;
    use \DateTime;
    use \InvalidArgumentException;

    /**
     * MapController page controller
     *
     * @package    MapPlatform
     * @subpackage Controllers
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    final class MapController extends PageController
    {
        /**
         * MapController invoker.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function __invoke(Request $request, Response $response, $args)
        {
            return $response;
        }

        /**
         * Show the default page.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function home(Request $request, Response $response, $args)
        {
            return $response;
        }

        /**
         * Show the map details page.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function getMap(Request $request, Response $response, $args)
        {
            $this->container->logger->info("MapPlatform '/map/" . $args['mapId'] . "' route");
            $this->container->security->checkRememberMe();
            $mapId = filter_var($args['mapId'], FILTER_SANITIZE_NUMBER_INT);

            if ($mapId == null) {
                $response->getBody()->write('Taking you back to the homepage');

                return $response->withAddedHeader('Refresh', '1; url=/home');
            } else {
                $pageTitle = 'Map Details';
                $pageID = 0;
                $contentTemplate = 'map.phtml';
                $values['PageCrumbs'] = '<ol class="breadcrumb">
    <li><a href="/home">Home</a></li>
    <li class="active">Map Details</li>
</ol>';
                $values['mapId'] = $mapId;
                $mapItem = $this->getMapItem($mapId);

                if ($mapItem == null) {
                    $response->getBody()->write('Taking you back to the homepage');
                    return $response->withAddedHeader('Refresh', '1; url=/home');
                }

                $mapScreenshots = $this->getScreenshots($mapItem['revId']);
                $values = array_merge($values, $mapItem, $mapScreenshots);
                return $this->container->renderUtils->render($pageTitle, $pageID, $contentTemplate, $response, $values);
            }
        }

        /**
         * Show the map queue page.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function getMapQueue(Request $request, Response $response, $args)
        {
            $this->container->logger->info("MapPlatform '/admin_queue' route");
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group < 9)) {
                $response->getBody()->write('Taking you back to the homepage');
                return $response->withAddedHeader('Refresh', '1; url=/home');
            } else {
                $pageTitle = 'Queue';
                $pageID = 4;
                $contentTemplate = 'admin_queue.phtml';
                $values['PageCrumbs'] = '<ol class="breadcrumb">
    <li><a href="/home">Home</a></li>
    <li class="active">Queue</li>
</ol>';
                return $this->container->renderUtils->render($pageTitle, $pageID, $contentTemplate, $response, $values);
            }
        }

        /**
         * Show the new map page.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function newMap(Request $request, Response $response, $args)
        {
            $this->container->logger->info("MapPlatform '/map/new' route");
            $this->container->security->checkRememberMe();

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group <= 0)) {
                $response->getBody()->write('Taking you back to the homepage');
                return $response->withAddedHeader('Refresh', '1; url=/home');
            } else {
                $database = $this->container->dataBase->PDO;
                $mapTypeArr = [];

                try {
                    $query = $database->select(['map_type_pk', 'map_type_name'])
                        ->from('MapTypes')
                        ->orderBy('map_type_pk', 'ASC');
                    $stmt = $query->execute();
                    $resultArr = $stmt->fetchall();

                    foreach ($resultArr as $mapType) {
                        $mapTypeArr[$mapType['map_type_pk']] = $mapType['map_type_name'];
                    }
                } catch (Exception $ex) {
                    $this->container->logger->error('getMapsByUser -> ex = ' . $ex);
                }

                $pageTitle = 'New Map';
                $pageID = 3;
                $contentTemplate = 'map_new.phtml';
                $values['PageCrumbs'] = '<ol class="breadcrumb">
    <li><a href="/dashboard">Dashboard</a></li>
    <li class="active">New Map</li>
</ol>';
                $values['mapTypes'] = $this->container->formattingUtils->arrayToOptions($mapTypeArr, True);
                return $this->container->renderUtils->render($pageTitle, $pageID, $contentTemplate, $response, $values);
            }
        }

        /**
         * Show the map files update page.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function updateMapFiles(Request $request, Response $response, $args)
        {
            $this->container->logger->info("MapPlatform '/map/" . $args['mapId'] . "/updatefiles' route");
            $this->container->security->checkRememberMe();
            $mapId = filter_var($args['mapId'], FILTER_SANITIZE_NUMBER_INT);

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group <= 0) || ($mapId == null)) {
                $response->getBody()->write('Taking you back to the homepage');
                return $response->withAddedHeader('Refresh', '1; url=/home');
            } else {
                $mapItem = $this->getMapItemMinimal($mapId);

                if ($_SESSION['user']->id != $mapItem['userId']) {
                    $response->getBody()->write('Taking you back to the homepage');
                    return $response->withAddedHeader('Refresh', '1; url=/home');
                } else {
                    $pageTitle = 'Update Map Files';
                    $pageID = 3;
                    $contentTemplate = 'map_update_files.phtml';
                    $values['PageCrumbs'] = '<ol class="breadcrumb">
    <li><a href="/home">Home</a></li>
    <li><a href="/map/' . $mapId . '">Map Details</a></li>
    <li class="active">Update Map Files</li>
</ol>';
                    $values['mapId'] = $args['mapId'];
                    $values = array_merge($values, $mapItem);
                    return $this->container->renderUtils->render($pageTitle, $pageID, $contentTemplate, $response, $values);
                }
            }
        }

        /**
         * Show the map info update page.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function updateMapInfo(Request $request, Response $response, $args)
        {
            $this->container->logger->info("MapPlatform '/map/" . $args['mapId'] . "/updateinfo' route");
            $this->container->security->checkRememberMe();
            $mapId = filter_var($args['mapId'], FILTER_SANITIZE_NUMBER_INT);

            if (($_SESSION['user']->id == -1) || ($_SESSION['user']->group <= 0) || ($mapId == null)) {
                $response->getBody()->write('Taking you back to the homepage');
                return $response->withAddedHeader('Refresh', '1; url=/home');
            } else {
                $mapItem = $this->getMapItemMinimal($mapId);

                if ($_SESSION['user']->id != $mapItem['userId']) {
                    $response->getBody()->write('Taking you back to the homepage');
                    return $response->withAddedHeader('Refresh', '1; url=/home');
                } else {
                    $pageTitle = 'Update Map Info';
                    $pageID = 3;
                    $contentTemplate = 'map_update_info.phtml';
                    $values['PageCrumbs'] = '<ol class="breadcrumb">
    <li><a href="/home">Home</a></li>
    <li><a href="/map/' . $mapId . '">Map Details</a></li>
    <li class="active">Update Map Info</li>
</ol>';
                    $values['mapId'] = $mapId;
                    $values = array_merge($values, $mapItem);
                    return $this->container->renderUtils->render($pageTitle, $pageID, $contentTemplate, $response, $values);
                }
            }
        }

        private function getMapItem($aMapId) {
            $database = $this->container->dataBase->PDO;
            $query = 'SET @mapid = :mapid;';
            $stmt = $database->prepare($query);
            $stmt->bindParam(':mapid', $aMapId);
            $stmt->execute();
            $query = $database->select([
                    'Maps.map_name',
                    'Maps.map_downloads',
                    'Revisions.rev_pk',
                    'Revisions.rev_status_fk',
                    'Revisions.rev_map_description_short',
                    'Revisions.rev_map_description',
                    'Revisions.rev_upload_date',
                    'Revisions.rev_map_version',
                    'Users.user_pk',
                    'Users.user_name',
                    'MapTypes.map_type_name',
                    'ROUND(AVG(CAST(Ratings.rating_amount AS DECIMAL(12,2))), 1) AS avg_rating',
                    'IFNULL((SELECT COUNT(*) FROM Ratings WHERE rating_amount = 1 AND map_fk = @mapid), 0) AS rating_one',
                    'IFNULL((SELECT COUNT(*) FROM Ratings WHERE rating_amount = 2 AND map_fk = @mapid), 0) AS rating_two',
                    'IFNULL((SELECT COUNT(*) FROM Ratings WHERE rating_amount = 3 AND map_fk = @mapid), 0) AS rating_three',
                    'IFNULL((SELECT COUNT(*) FROM Ratings WHERE rating_amount = 4 AND map_fk = @mapid), 0) AS rating_four',
                    'IFNULL((SELECT COUNT(*) FROM Ratings WHERE rating_amount = 5 AND map_fk = @mapid), 0) AS rating_five'
                ])
                ->from('Maps')
                ->leftJoin('Revisions', 'Revisions.map_fk', '=', 'Maps.map_pk')
                ->leftJoin('Users', 'Users.user_pk', '=', 'Maps.user_fk')
                ->leftJoin('MapTypes', 'MapTypes.map_type_pk', '=', 'Maps.map_type_fk')
                ->leftJoin('Ratings', 'Ratings.map_fk', '=', 'Maps.map_pk')
                //->where('Revisions.rev_status_fk', '=', 1) // Made a better version :)
                ->whereNull('Revisions.rev_superseded_by_rev_fk', 'AND')
                ->where('Maps.map_visible', '=', 1, 'AND')
                ->where('Maps.map_pk', '=', $aMapId, 'AND');
            $stmt = $query->execute();
            $mapItem = $stmt->fetch();

            if (
                $mapItem != null &&
                $mapItem['map_name'] != null &&
                (
                    $mapItem['rev_status_fk'] == 1 ||
                    (
                        $_SESSION['user']->id == $mapItem['user_pk'] ||
                        $_SESSION['user']->group >= 9
                    )
                )
            ) {
                $lastChangeDate = new DateTime($mapItem['rev_upload_date']);
                return [
                    'mapName' => $mapItem['map_name'],
                    'mapDownloads' => intval($mapItem['map_downloads']),
                    'revId' => $mapItem['rev_pk'],
                    'revMapDescriptionShort' => $mapItem['rev_map_description_short'],
                    'revMapDescription' => $mapItem['rev_map_description'],
                    'revUploadDate' => $lastChangeDate->format('Y-m-d H:i'),
                    'revMapVersion' => $mapItem['rev_map_version'],
                    'userId' => $mapItem['user_pk'],
                    'userName' => $mapItem['user_name'],
                    'mapTypeName' => $mapItem['map_type_name'],
                    'avgRating' => ($mapItem['avg_rating'] === null ? 'n/a' : floatval($mapItem['avg_rating'])),
                    'ratingOne' => intval($mapItem['rating_one']),
                    'ratingTwo' => intval($mapItem['rating_two']),
                    'ratingThree' => intval($mapItem['rating_three']),
                    'ratingFour' => intval($mapItem['rating_four']),
                    'ratingFive' => intval($mapItem['rating_five'])
                ];
            } else {
                return null;
            }
        }

        private function getMapItemMinimal($aMapId) {
            $database = $this->container->dataBase->PDO;
            $query = $database->select([
                    'Maps.map_name',
                    'Revisions.rev_pk',
                    'Revisions.rev_status_fk',
                    'Revisions.rev_map_description_short',
                    'Revisions.rev_map_description',
                    'Revisions.rev_upload_date',
                    'Revisions.rev_map_version',
                    'Users.user_pk',
                    'MapTypes.map_type_name'
                ])
                ->from('Maps')
                ->leftJoin('Revisions', 'Revisions.map_fk', '=', 'Maps.map_pk')
                ->leftJoin('Users', 'Users.user_pk', '=', 'Maps.user_fk')
                ->leftJoin('MapTypes', 'MapTypes.map_type_pk', '=', 'Maps.map_type_fk')
                //->where('Revisions.rev_status_fk', '=', 1) // Made a better version :)
                ->whereNull('Revisions.rev_superseded_by_rev_fk', 'AND')
                /* ->where('Maps.map_visible', '=', 1, 'AND') // Disabled for possible use later on */
                ->where('Maps.map_pk', '=', $aMapId, 'AND');
            $stmt = $query->execute();
            $mapItem = $stmt->fetch();

            if (
                $mapItem != null &&
                $mapItem['map_name'] != null &&
                (
                    $mapItem['rev_status_fk'] == 1 ||
                    (
                        $_SESSION['user']->id == $mapItem['user_pk'] ||
                        $_SESSION['user']->group >= 9
                    )
                )
            ) {
                $lastChangeDate = new DateTime($mapItem['rev_upload_date']);
                return [
                    'mapName' => $mapItem['map_name'],
                    'revId' => $mapItem['rev_pk'],
                    'revMapDescriptionShort' => $mapItem['rev_map_description_short'],
                    'revMapDescription' => $mapItem['rev_map_description'],
                    'revUploadDate' => $lastChangeDate->format('Y-m-d H:i'),
                    'revMapVersion' => $mapItem['rev_map_version'],
                    'userId' => $mapItem['user_pk'],
                    'mapTypeName' => $mapItem['map_type_name']
                ];
            } else {
                return null;
            }
        }

        private function getScreenshots($aRevId) {
            $database = $this->container->dataBase->PDO;
            $query = $database->select([
                    'screen_pk',
                    'screen_title',
                    'screen_alt',
                    'screen_order'
                ])
                ->from('Screenshots')
                ->where('rev_fk', '=', $aRevId)
                ->orderBy('screen_order', 'ASC');
            $stmt = $query->execute();
            $screenshotItems = $stmt->fetchall();
            $carouselItemsNew = [];

            if (is_array($screenshotItems) && count($screenshotItems) > 0) {
                $firstItem = true;

                foreach ($screenshotItems as $screenshotItem) {
                    if ($firstItem) {
                        $carouselIndicators = '<li data-target="#screenshot_carousel" data-slide-to="' . $screenshotItem['screen_order'] . '" class="active"></li>';
                        $carouselItems = '<div class="item active">
    <img src="/images/' . $aRevId . '/' . $screenshotItem['screen_pk'] . '" alt="' . $screenshotItem['screen_alt'] . '" />
    <div class="carousel-caption">' . $screenshotItem['screen_title'] . '</div>
</div>';
                        $firstItem = false;
                    } else {
                        $carouselIndicators .= '<li data-target="#screenshot_carousel" data-slide-to="' . $screenshotItem['screen_order'] . '"></li>';
                        $carouselItems .= '<div class="item">
    <img src="/images/' . $aRevId . '/' . $screenshotItem['screen_pk'] . '" alt="' . $screenshotItem['screen_alt'] . '" />
    <div class="carousel-caption">' . $screenshotItem['screen_title'] . '</div>
</div>';
                    }

                    $carouselItemsNew[] = (object)[
                        'title' => $screenshotItem['screen_title'],
                        'href' => '/images/' . $aRevId . '/' . $screenshotItem['screen_pk'],
                        'type' => 'image/png'
                    ];
                }
            } else {
                $carouselIndicators = '<li data-target="#screenshot_carousel" data-slide-to="0" class="active"></li>' .
                                      '<li data-target="#screenshot_carousel" data-slide-to="1"></li>';
                $carouselItems = '<div class="item active">
    <img src="/images/default/kp_2016-08-30_21-29-44.png" alt="Knights Province Image 1">
    <div class="carousel-caption">A first look at combat</div>
</div>
<div class="item">
    <img src="/images/default/kp_2016-09-03_18-34-31.png" alt="Knights Province Image 2">
    <div class="carousel-caption">A basic village</div>
</div>';
                $carouselItemsNew[] = (object)[
                    'title' => 'A first look at combat',
                    'href' => '/images/default/kp_2016-08-30_21-29-44.png',
                    'type' => 'image/png'
                ];
                $carouselItemsNew[] = (object)[
                    'title' => 'A basic village',
                    'href' => '/images/default/kp_2016-09-03_18-34-31.png',
                    'type' => 'image/png'
                ];
            }

            return [
                'carouselIndicators' => $carouselIndicators,
                'carouselItems' => $carouselItems,
                'carouselItemsNew' => json_encode($carouselItemsNew, JSON_PRETTY_PRINT)
            ];
        }
    }
