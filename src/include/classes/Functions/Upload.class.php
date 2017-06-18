<?php
    namespace Functions;
    use \ZipArchive;
    use \Exception;
    use \Imagick;
    use \App\Logger;

    class Upload
    {
        private $utils = null;

        public function __construct(&$utilsClass) {
            $this -> utils = $utilsClass;
        }

        public function getContent(&$dbHandler) {
            global $config, $request, $logger;
            $logger -> log('Upload::getContent -> start()', Logger::DEBUG);
            $logger -> log('POST = ' . print_r($_POST, True), Logger::DEBUG);
            $logger -> log('FILES = ' . print_r($_FILES, True), Logger::DEBUG);

            try {
                if (!Empty($request['call_parts'][1])) {
                    $mapVersion = filter_input(INPUT_POST, 'newMapRevVersion', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_BACKTICK ||
                                                                                                       FILTER_FLAG_ENCODE_LOW ||
                                                                                                       FILTER_FLAG_ENCODE_HIGH ||
                                                                                                       FILTER_FLAG_ENCODE_AMP);

                    if (Empty($_FILES['newMapRevMapFile']['name']) ||
                        Empty($_FILES['newMapRevDatFile']['name']) ||
                        Empty($mapVersion)) {
                        $logger -> log('FILES->newMapRevMapFile->name Empty? = ' . print_r(Empty($_FILES['newMapRevMapFile']['name']), True), Logger::DEBUG);
                        $logger -> log('FILES->newMapRevDatFile->name Empty? = ' . print_r(Empty($_FILES['newMapRevDatFile']['name']), True), Logger::DEBUG);
                        $logger -> log('POST->newMapRevVersion Empty? = ' .        print_r(Empty($mapVersion), True),                         Logger::DEBUG);
                        throw new Exception('Invalid request, inputs missing');
                    };

                    $mapItem     = null;
                    $mapId       = IntVal($request['call_parts'][1]);
                    $mapInfoFunc = new \Functions\MapInfo($this -> utils);
                    $mapItem     = $mapInfoFunc -> getMapDetails($dbHandler, $mapId);

                    if (Empty($mapItem) || $mapItem['data']['rev_map_version'] === $mapVersion)
                        throw new Exception('Map versions are identical, please change it.');

                    $mapArchive      = new ZipArchive();
                    $mapName         = $mapItem['data']['map_name'];
                    $mapDescShort    = $mapItem['data']['rev_map_description_short'];
                    $mapDescFull     = $mapItem['data']['rev_map_description'];
                    $mapDirInArchive = $mapName . '/';
                    $mapDirOnDisk    = $config['files']['uploadDir'] . '/' . $mapName . '/' . $mapVersion . '/';

                    // Create the directory that will hold our newly created ZIP archive
                    $this -> utils -> mkdirRecursive(APP_DIR . $mapDirOnDisk);

                    // Try to create the new archive
                    if (!$mapArchive -> open(APP_DIR . $mapDirOnDisk . $mapName . '.zip', ZIPARCHIVE::CREATE))
                        throw new Exception('Unable to create the archive');

                    // Create a new directory
                    $mapArchive -> addEmptyDir($mapDirInArchive);
                    // Add the required files
                    $mapArchive -> addFile($_FILES['newMapRevMapFile']['tmp_name'], $mapDirInArchive . $mapName . '.map');
                    $mapArchive -> addFile($_FILES['newMapRevDatFile']['tmp_name'], $mapDirInArchive . $mapName . '.dat');

                    if (!Empty($_FILES['newMapRevScriptFile']['name']))
                        $mapArchive -> addFile($_FILES['newMapRevScriptFile']['tmp_name'], $mapDirInArchive . $mapName . '.script');

                    // Because PHP uses an odd manner of stacking multiple files into an array we will re-array them here
                    if (!Empty($_FILES['newMapRevLibxFiles']['name'][0])) {
                        $libxFiles = $this -> utils -> reArrayFiles($_FILES['newMapRevLibxFiles']);

                        // Add the files
                        foreach ($libxFiles as $libxFile) {
                            $fileBitsArr   = Explode('.', $libxFile['name']);
                            $fileBitsCount = count($fileBitsArr);
                            $fileExtention = '.' . $fileBitsArr[$fileBitsCount - 2] . '.libx'; // Get the language part as well
                            $mapArchive -> addFile($libxFile['tmp_name'], $mapDirInArchive . $mapName . $fileExtention);
                        };
                    };

                    $mapArchive -> close();

                    $insertRevQuery = 'INSERT INTO ' . PHP_EOL .
                                      '    `Revisions` (`map_fk`, `rev_map_file_name`, `rev_map_file_path`, `rev_map_version`, ' .
                                      '`rev_map_description_short`, `rev_map_description`, `rev_status_fk`) '. PHP_EOL .
                                      'VALUES ' . PHP_EOL .
                                      '    (:mapid, :filename, :filepath, :mapversion, :mapdescshort, :mapdescfull, :revstatusid);';
                    $dbHandler -> PrepareAndBind($insertRevQuery, Array('mapid'        => $mapId,
                                                                        'filename'     => $mapName . '.zip',
                                                                        'filepath'     => $mapDirOnDisk,
                                                                        'mapversion'   => $mapVersion,
                                                                        'mapdescshort' => $mapDescShort,
                                                                        'mapdescfull'  => $mapDescFull,
                                                                        'revstatusid'  => 1));
                    $dbHandler -> Execute();
                    $revId = $dbHandler -> GetLastInsertId();
                    $dbHandler -> Clean();

                    if ($revId == null)
                        throw new Exception('Could not add the map to the database');

                    $updateQuery = 'UPDATE ' . PHP_EOL .
                                   '    `Revisions` '. PHP_EOL .
                                   'SET ' . PHP_EOL .
                                   '    `rev_status_fk` = 3 '. PHP_EOL .
                                   'WHERE ' . PHP_EOL .
                                   '    `rev_pk` = :maprevid;';
                    $dbHandler -> PrepareAndBind($updateQuery, Array('maprevid' => $mapItem['data']['rev_pk']));
                    $dbHandler -> Execute();
                    $dbHandler -> Clean();

                    $content['status']  = 'Success';
                    $content['message'] = 'Map has been added successfully!<br />' . PHP_EOL .
                                          'Redirecting you now.';
                    $content['data']    = $mapId;
                } else {
                    $mapName      = filter_input(INPUT_POST, 'mapName', FILTER_SANITIZE_STRING,      FILTER_FLAG_STRIP_BACKTICK ||
                                                                                                     FILTER_FLAG_ENCODE_LOW ||
                                                                                                     FILTER_FLAG_ENCODE_HIGH ||
                                                                                                     FILTER_FLAG_ENCODE_AMP);
                    $mapVersion   = filter_input(INPUT_POST, 'mapVersion', FILTER_SANITIZE_STRING,   FILTER_FLAG_STRIP_BACKTICK ||
                                                                                                     FILTER_FLAG_ENCODE_LOW ||
                                                                                                     FILTER_FLAG_ENCODE_HIGH ||
                                                                                                     FILTER_FLAG_ENCODE_AMP);
                    $mapDescShort = filter_input(INPUT_POST, 'mapDescShort', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_BACKTICK ||
                                                                                                     FILTER_FLAG_ENCODE_LOW ||
                                                                                                     FILTER_FLAG_ENCODE_HIGH ||
                                                                                                     FILTER_FLAG_ENCODE_AMP);
                    $mapDescFull  = filter_input(INPUT_POST, 'mapDescFull', FILTER_SANITIZE_STRING,  FILTER_FLAG_STRIP_BACKTICK ||
                                                                                                     FILTER_FLAG_ENCODE_LOW ||
                                                                                                     FILTER_FLAG_ENCODE_HIGH ||
                                                                                                     FILTER_FLAG_ENCODE_AMP);
                    $mapType      = IntVal(filter_input(INPUT_POST, 'mapType', FILTER_SANITIZE_NUMBER_INT));

                    if (Empty($_FILES['mapFile']['name']) ||
                        Empty($_FILES['datFile']['name']) ||
                        Empty($mapName) ||
                        ($mapType < 0) ||
                        Empty($mapVersion) ||
                        Empty($mapDescShort) ||
                        Empty($mapDescFull)) {
                        $logger -> log('FILES->mapFile->name Empty? = ' . print_r(Empty($_FILES['mapFile']['name']), True), Logger::DEBUG);
                        $logger -> log('FILES->datFile->name Empty? = ' . print_r(Empty($_FILES['datFile']['name']), True), Logger::DEBUG);
                        $logger -> log('POST->mapName Empty? = ' .        print_r(Empty($mapName), True),                   Logger::DEBUG);
                        $logger -> log('POST->mapType < 0? = ' .          print_r(($mapType < 0), True),                    Logger::DEBUG);
                        $logger -> log('POST->mapVersion Empty? = ' .     print_r(Empty($mapVersion), True),                Logger::DEBUG);
                        $logger -> log('POST->mapDescShort Empty? = ' .   print_r(Empty($mapDescShort), True),              Logger::DEBUG);
                        $logger -> log('POST->mapDescFull Empty? = ' .    print_r(Empty($mapDescFull), True),               Logger::DEBUG);
                        throw new Exception('Invalid request, inputs missing');
                    };

                    $mapArchive      = new ZipArchive();
                    $mapDirInArchive = $mapName . '/';
                    $mapDirOnDisk    = $config['files']['uploadDir'] . '/' . $mapName . '/' . $mapVersion . '/';

                    $selectQuery = 'SELECT ' . PHP_EOL .
                                   '    COUNT(*) AS map_count ' . PHP_EOL .
                                   'FROM ' . PHP_EOL .
                                   '    `Maps` ' . PHP_EOL .
                                   'WHERE ' . PHP_EOL .
                                   '    `map_name` = :mapname;';
                    $dbHandler -> PrepareAndBind($selectQuery, Array('mapname' => $mapName));
                    $mapCount = $dbHandler -> ExecuteAndFetch();

                    if ($mapCount['map_count'] > 0)
                        throw new Exception('Map already exists!');

                    // Create the directory that will hold our newly created ZIP archive
                    $this -> utils -> mkdirRecursive(APP_DIR . $mapDirOnDisk);

                    // Try to create the new archive
                    if (!$mapArchive -> open(APP_DIR . $mapDirOnDisk . $mapName . '.zip', ZIPARCHIVE::CREATE))
                        throw new Exception('Unable to create the archive');

                    // Create a new directory
                    $mapArchive -> addEmptyDir($mapDirInArchive);
                    // Add the required files
                    $mapArchive -> addFile($_FILES['mapFile']['tmp_name'], $mapDirInArchive . $mapName . '.map');
                    $mapArchive -> addFile($_FILES['datFile']['tmp_name'], $mapDirInArchive . $mapName . '.dat');

                    if (!Empty($_FILES['scriptFile']['name']))
                        $mapArchive -> addFile($_FILES['scriptFile']['tmp_name'], $mapDirInArchive . $mapName . '.script');

                    // Because PHP uses an odd manner of stacking multiple files into an array we will re-array them here
                    if (!Empty($_FILES['libxFiles']['name'][0])) {
                        $libxFiles = $this -> utils -> reArrayFiles($_FILES['libxFiles']);

                        // Add the files
                        foreach ($libxFiles as $libxFile) {
                            $fileBitsArr   = Explode('.', $libxFile['name']);
                            $fileBitsCount = count($fileBitsArr);
                            $fileExtention = '.' . $fileBitsArr[$fileBitsCount - 2] . '.libx'; // Get the language part as well
                            $mapArchive -> addFile($libxFile['tmp_name'], $mapDirInArchive . $mapName . $fileExtention);
                        };
                    };

                    $mapArchive -> close();

                    $insertMapQuery = 'INSERT INTO ' . PHP_EOL .
                                      '    `Maps` (`map_name`, `user_fk`, `map_Type_fk`) '. PHP_EOL .
                                      'VALUES ' . PHP_EOL .
                                      '    (:mapname, :userid, :maptypeid);';
                    $dbHandler -> PrepareAndBind($insertMapQuery, Array('mapname'   => $mapName,
                                                                        'userid'    => $_SESSION['user'] -> id,
                                                                        'maptypeid' => $mapType));
                    $dbHandler -> Execute();
                    $mapId = $dbHandler -> GetLastInsertId();
                    $dbHandler -> Clean();

                    if ($mapId == null)
                        throw new Exception('Could not add the map to the database');

                    $insertRevQuery = 'INSERT INTO ' . PHP_EOL .
                                      '    `Revisions` (`map_fk`, `rev_map_file_name`, `rev_map_file_path`, `rev_map_version`, ' .
                                      '`rev_map_description_short`, `rev_map_description`, `rev_status_fk`) '. PHP_EOL .
                                      'VALUES ' . PHP_EOL .
                                      '    (:mapid, :filename, :filepath, :mapversion, :mapdescshort, :mapdescfull, :revstatusid);';
                    $dbHandler -> PrepareAndBind($insertRevQuery, Array('mapid'        => $mapId,
                                                                        'filename'     => $mapName . '.zip',
                                                                        'filepath'     => $mapDirOnDisk,
                                                                        'mapversion'   => $mapVersion,
                                                                        'mapdescshort' => $mapDescShort,
                                                                        'mapdescfull'  => $mapDescFull,
                                                                        'revstatusid'  => 1));
                    $dbHandler -> Execute();
                    $revId = $dbHandler -> GetLastInsertId();
                    $dbHandler -> Clean();

                    if ($revId == null)
                        throw new Exception('Could not add the map to the database');

                    if (!$this -> uploadImages($dbHandler, $mapName, $mapDirOnDisk, $revId))
                        throw new Exception('Could not add the screenshots to the map');

                    $content['status']  = 'Success';
                    $content['message'] = 'Map has been added successfully!<br />' . PHP_EOL .
                                          'Redirecting you now.';
                    $content['data']    = $mapId;
                };
            } catch (Exception $e) {
                $content['status']  = 'Error';
                $content['message'] = $e -> getMessage();
            };

            return $content;
        }

        private function uploadImages(&$dbHandler, $mapName, $mapDir, $revId, $oldRevId = null) {
            global $config;

            try {
                $cleanMapName    = $this -> utils -> cleanInput($mapName, True);
                $imageOrderNum   = 0;
                $screenshotFiles = Array();

                if ($oldRevId !== null) {
                    $selectQuery = 'SELECT ' . PHP_EOL .
                                   '    * ' . PHP_EOL .
                                   'FROM ' . PHP_EOL .
                                   '    `Screenshots` ' . PHP_EOL .
                                   'WHERE ' . PHP_EOL .
                                   '    `rev_fk` = :revid ' . PHP_EOL .
                                   'ORDER BY ' . PHP_EOL .
                                   '    `screen_order` ASC;';
                    $dbHandler -> PrepareAndBind($selectQuery, Array('revid' => $oldRevId));
                    $oldScreenshotFiles = $dbHandler -> ExecuteAndFetchAll();
                    $dbHandler -> Clean();
                } else {
                    $oldScreenshotFiles = Array();
                };

                if (!Empty($_FILES['screenshotFileOne']['tmp_name'])) {
                    $detectedType = exif_imagetype($_FILES['screenshotFileOne']['tmp_name']);
                    $validFile    = in_array($detectedType, $config['images']['allowedTypes']);

                    if ($validFile) {
                        $_FILES['screenshotFileOne']['imageTitle']    = (Empty(filter_input(INPUT_POST,
                                                                                            'screenshotTitleOne',
                                                                                            FILTER_SANITIZE_STRING,
                                                                                            FILTER_FLAG_STRIP_BACKTICK ||
                                                                                            FILTER_FLAG_STRIP_LOW ||
                                                                                            FILTER_FLAG_STRIP_HIGH ||
                                                                                            FILTER_FLAG_STRIP_AMP))
                                                                         ? $cleanMapName . '-' . $imageOrderNum
                                                                         : filter_input(INPUT_POST,
                                                                                        'screenshotTitleOne',
                                                                                        FILTER_SANITIZE_STRING,
                                                                                        FILTER_FLAG_STRIP_BACKTICK ||
                                                                                        FILTER_FLAG_STRIP_LOW ||
                                                                                        FILTER_FLAG_STRIP_HIGH ||
                                                                                        FILTER_FLAG_STRIP_AMP));
                        $_FILES['screenshotFileOne']['imageOrderNum'] = $imageOrderNum;
                        $_FILES['screenshotFileOne']['imageType']     = $detectedType;
                        $screenshotFiles[]                            = $_FILES['screenshotFileOne'];
                        $imageOrderNum++;
                    };
                };

                if (!Empty($_FILES['screenshotFileTwo']['tmp_name'])) {
                    $detectedType = exif_imagetype($_FILES['screenshotFileTwo']['tmp_name']);
                    $validFile    = in_array($detectedType, $config['images']['allowedTypes']);

                    if ($validFile) {
                        $_FILES['screenshotFileTwo']['imageTitle']    = (Empty(filter_input(INPUT_POST,
                                                                                            'screenshotTitleTwo',
                                                                                            FILTER_SANITIZE_STRING,
                                                                                            FILTER_FLAG_STRIP_BACKTICK ||
                                                                                            FILTER_FLAG_STRIP_LOW ||
                                                                                            FILTER_FLAG_STRIP_HIGH ||
                                                                                            FILTER_FLAG_STRIP_AMP))
                                                                         ? $cleanMapName . '-' . $imageOrderNum
                                                                         : filter_input(INPUT_POST,
                                                                                        'screenshotTitleTwo',
                                                                                        FILTER_SANITIZE_STRING,
                                                                                        FILTER_FLAG_STRIP_BACKTICK ||
                                                                                        FILTER_FLAG_STRIP_LOW ||
                                                                                        FILTER_FLAG_STRIP_HIGH ||
                                                                                        FILTER_FLAG_STRIP_AMP));
                        $_FILES['screenshotFileTwo']['imageOrderNum'] = $imageOrderNum;
                        $_FILES['screenshotFileTwo']['imageType']     = $detectedType;
                        $screenshotFiles[]                            = $_FILES['screenshotFileTwo'];
                        $imageOrderNum++;
                    };
                };

                if (!Empty($_FILES['screenshotFileThree']['tmp_name'])) {
                    $detectedType = exif_imagetype($_FILES['screenshotFileThree']['tmp_name']);
                    $validFile    = in_array($detectedType, $config['images']['allowedTypes']);

                    if ($validFile) {
                        $_FILES['screenshotFileThree']['imageTitle']    = (Empty(filter_input(INPUT_POST,
                                                                                              'screenshotTitleThree',
                                                                                              FILTER_SANITIZE_STRING,
                                                                                              FILTER_FLAG_STRIP_BACKTICK ||
                                                                                              FILTER_FLAG_STRIP_LOW ||
                                                                                              FILTER_FLAG_STRIP_HIGH ||
                                                                                              FILTER_FLAG_STRIP_AMP))
                                                                           ? $cleanMapName . '-' . $imageOrderNum
                                                                           : filter_input(INPUT_POST,
                                                                                          'screenshotTitleThree',
                                                                                          FILTER_SANITIZE_STRING,
                                                                                          FILTER_FLAG_STRIP_BACKTICK ||
                                                                                          FILTER_FLAG_STRIP_LOW ||
                                                                                          FILTER_FLAG_STRIP_HIGH ||
                                                                                          FILTER_FLAG_STRIP_AMP));
                        $_FILES['screenshotFileThree']['imageOrderNum'] = $imageOrderNum;
                        $_FILES['screenshotFileThree']['imageType']     = $detectedType;
                        $screenshotFiles[]                              = $_FILES['screenshotFileThree'];
                        $imageOrderNum++;
                    };
                };

                if (!Empty($_FILES['screenshotFileFour']['tmp_name'])) {
                    $detectedType = exif_imagetype($_FILES['screenshotFileFour']['tmp_name']);
                    $validFile    = in_array($detectedType, $config['images']['allowedTypes']);

                    if ($validFile) {
                        $_FILES['screenshotFileFour']['imageTitle']    = (Empty(filter_input(INPUT_POST,
                                                                                             'screenshotTitleFour',
                                                                                             FILTER_SANITIZE_STRING,
                                                                                             FILTER_FLAG_STRIP_BACKTICK ||
                                                                                             FILTER_FLAG_STRIP_LOW ||
                                                                                             FILTER_FLAG_STRIP_HIGH ||
                                                                                             FILTER_FLAG_STRIP_AMP))
                                                                          ? $cleanMapName . '-' . $imageOrderNum
                                                                          : filter_input(INPUT_POST,
                                                                                         'screenshotTitleFour',
                                                                                         FILTER_SANITIZE_STRING,
                                                                                         FILTER_FLAG_STRIP_BACKTICK ||
                                                                                         FILTER_FLAG_STRIP_LOW ||
                                                                                         FILTER_FLAG_STRIP_HIGH ||
                                                                                         FILTER_FLAG_STRIP_AMP));
                        $_FILES['screenshotFileFour']['imageOrderNum'] = $imageOrderNum;
                        $_FILES['screenshotFileFour']['imageType']     = $detectedType;
                        $screenshotFiles[]                             = $_FILES['screenshotFileFour'];
                        $imageOrderNum++;
                    };
                };

                if (!Empty($_FILES['screenshotFileFive']['tmp_name'])) {
                    $detectedType = exif_imagetype($_FILES['screenshotFileFive']['tmp_name']);
                    $validFile    = in_array($detectedType, $config['images']['allowedTypes']);

                    if ($validFile) {
                        $_FILES['screenshotFileFive']['imageTitle']    = (Empty(filter_input(INPUT_POST,
                                                                                             'screenshotTitleFive',
                                                                                             FILTER_SANITIZE_STRING,
                                                                                             FILTER_FLAG_STRIP_BACKTICK ||
                                                                                             FILTER_FLAG_STRIP_LOW ||
                                                                                             FILTER_FLAG_STRIP_HIGH ||
                                                                                             FILTER_FLAG_STRIP_AMP))
                                                                          ? $cleanMapName . '-' . $imageOrderNum
                                                                          : filter_input(INPUT_POST,
                                                                                         'screenshotTitleFive',
                                                                                         FILTER_SANITIZE_STRING,
                                                                                         FILTER_FLAG_STRIP_BACKTICK ||
                                                                                         FILTER_FLAG_STRIP_LOW ||
                                                                                         FILTER_FLAG_STRIP_HIGH ||
                                                                                         FILTER_FLAG_STRIP_AMP));
                        $_FILES['screenshotFileFive']['imageOrderNum'] = $imageOrderNum;
                        $_FILES['screenshotFileFive']['imageType']     = $detectedType;
                        $screenshotFiles[]                             = $_FILES['screenshotFileFive'];
                    };
                };

                foreach ($screenshotFiles as $screenshotFile) {
                    $imageObject = new Imagick($screenshotFile['tmp_name']);
                    $this -> utils -> resizeImage($imageObject, $config['images']['maxWidth'], $config['images']['maxHeight']);

                    if ($screenshotFile['imageType'] == IMAGETYPE_GIF) {
                        $imageExtention = '.gif';
                    } else {
                        $imageExtention = '.png';
                        $imageObject -> setImageFormat('png');
                    };

                    $imageFileName = $cleanMapName . '-' . $screenshotFile['imageOrderNum'] . $imageExtention;

                    $imageObject -> writeImage(APP_DIR . $mapDir . $imageFileName);
                    $imageObject -> destroy();

                    $insertQuery = 'INSERT INTO ' . PHP_EOL .
                                   '    `Screenshots` (`rev_fk`, `screen_title`, `screen_alt`, `screen_file_name`, `screen_path`, `screen_order`) ' . PHP_EOL .
                                   'VALUES ' . PHP_EOL .
                                   '    (:revid, :screentitle, :screenalt, :screenfilename, :screenpath, :screenorder);';
                    $dbHandler -> PrepareAndBind($insertQuery, Array('revid'          => $revId,
                                                                     'screentitle'    => $screenshotFile['imageTitle'],
                                                                     'screenalt'      => $imageFileName,
                                                                     'screenfilename' => $imageFileName,
                                                                     'screenpath'     => $mapDir,
                                                                     'screenorder'    => $screenshotFile['imageOrderNum']));
                    $dbHandler -> Execute();
                };

                foreach ($oldScreenshotFiles as $oldScreenshotFile) { // Only append these to the revision but keep original location
                    $insertQuery = 'INSERT INTO ' . PHP_EOL .
                                   '    `Screenshots` (`rev_fk`, `screen_title`, `screen_alt`, `screen_file_name`, `screen_path`, `screen_order`) ' . PHP_EOL .
                                   'VALUES ' . PHP_EOL .
                                   '    (:revid, :screentitle, :screenalt, :screenfilename, :screenpath, :screenorder);';
                    $dbHandler -> PrepareAndBind($insertQuery, Array('revid'          => $revId,
                                                                     'screentitle'    => $oldScreenshotFile['screen_title'],
                                                                     'screenalt'      => $oldScreenshotFile['screen_alt'],
                                                                     'screenfilename' => $oldScreenshotFile['screen_file_name'],
                                                                     'screenpath'     => $oldScreenshotFile['screen_path'],
                                                                     'screenorder'    => $imageOrderNum));
                    $dbHandler -> Execute();

                    $imageOrderNum++;
                };

                return True;
            } catch (Exception $e) {
                return False;
            };
        }
    }
