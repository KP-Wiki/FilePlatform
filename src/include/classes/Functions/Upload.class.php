<?php
    namespace Functions;
    use \ZipArchive;
    use \Exception;
    use \Imagick;

    class Upload
    {
        private $utils = null;

        public function __construct(&$utilsClass) {
            $this -> utils = $utilsClass;
        }

        public function getContent(&$dbHandler) {
            global $config, $request;

            try {
                if (!isset($_FILES['mapFile']['name']) &&
                    Empty($_FILES['mapFile']['name']) &&
                    !isset($_FILES['mapFile']['datFile']) &&
                    Empty($_FILES['mapFile']['datFile']) &&
                    !isset($_POST['mapName']) &&
                    Empty($_POST['mapName']) &&
                    !isset($_POST['mapType']) &&
                    !isset($_POST['mapVersion']) &&
                    Empty($_POST['mapVersion']) &&
                    !isset($_POST['mapDescShort']) &&
                    Empty($_POST['mapDescShort']) &&
                    !isset($_POST['mapDescFull']) &&
                    Empty($_POST['mapDescFull']))
                    throw new Exception('Invalid request, inputs missing');

                if (isset($_POST['map_pk']) && !Empty($_POST['map_pk'])) {
                    $content = '<div class="alert alert-warning" role="alert">' . PHP_EOL .
                               '    There is nothing here, yet.<br />' . PHP_EOL .
                               '    Redirecting you now.' . PHP_EOL .
                               '</div>' . PHP_EOL;
                } else {
                    $mapName         = $_POST['mapName'];
                    $mapType         = IntVal($_POST['mapType']);
                    $mapVersion      = $_POST['mapVersion'];
                    $mapDescShort    = $_POST['mapDescShort'];
                    $mapDescFull     = $_POST['mapDescFull'];
                    $mapArchive      = new ZipArchive();
                    $mapDirInArchive = $mapName . '/';
                    $mapDirOnDisk    = $config['files']['uploadDir'] . '/' . $mapName . '/' . $mapVersion . '/';

                    $selectQuery = 'SELECT ' .
                                   '    COUNT(*) AS map_count ' .
                                   'FROM ' .
                                   '    `Maps` ' .
                                   'WHERE ' .
                                   '    `map_name` = :mapname;';
                    $dbHandler -> PrepareAndBind($selectQuery, Array('mapname' => $mapName));
                    $mapCount = $dbHandler -> ExecuteAndFetch();

                    if ($mapCount['map_count'] > 0)
                        throw new Exception('Map already exists!');

                    // Create the directory that will hold our newly created ZIP archive
                    $this -> utils -> mkdirRecursive(APP_DIR . $mapDirOnDisk);

                    // Because PHP uses an odd manner of stacking multiple files into an array we will re-array them here
                    if (count($_FILES['libxFiles']['name']) > 0) {
                        $libxFiles = $this -> utils -> reArrayFiles($_FILES['libxFiles']);
                    } else {
                        $libxFiles = Array();
                    };

                    // Try to create the new archive
                    if ($mapArchive -> open(APP_DIR . $mapDirOnDisk . $mapName . '.zip', ZIPARCHIVE::CREATE) !== True)
                        throw new Exception('Unable to create the archive');

                    // Create a new directory
                    $mapArchive -> addEmptyDir($mapDirInArchive);
                    // Add the required files
                    $mapArchive -> addFile($_FILES['mapFile']['tmp_name'], $mapDirInArchive . $mapName . '.map');
                    $mapArchive -> addFile($_FILES['datFile']['tmp_name'], $mapDirInArchive . $mapName . '.dat');

                    if (isset($_FILES['scriptFile']['name']) && !Empty($_FILES['scriptFile']['name']))
                        $mapArchive -> addFile($_FILES['scriptFile']['tmp_name'], $mapDirInArchive . $mapName . '.script');

                    //add the files
                    foreach ($libxFiles as $libxFile) {
                        $fileBitsArr   = Explode('.', $libxFile['name']);
                        $fileBitsCount = count($fileBitsArr);
                        $fileExtention = '.' . $fileBitsArr[$fileBitsCount - 2] . '.libx'; // Get the language part as well
                        $mapArchive -> addFile($libxFile['tmp_name'], $mapDirInArchive . $mapName . $fileExtention);
                    };

                    $mapArchive -> close();

                    $insertMapQuery = 'INSERT INTO ' .
                                      '    `Maps` (`map_name`, `user_fk`, `map_Type_fk`) '.
                                      'VALUES ' .
                                      '    (:mapname, :userid, :maptypeid);';
                    $dbHandler -> PrepareAndBind($insertMapQuery, Array('mapname'   => $mapName,
                                                                        'userid'    => $_SESSION['user'] -> id,
                                                                        'maptypeid' => $mapType));
                    $dbHandler -> Execute();
                    $mapId = $dbHandler -> GetLastInsertId();
                    $dbHandler -> Clean();

                    if ($mapId == null)
                        throw new Exception('Could not add the map to the database');

                    $insertRevQuery = 'INSERT INTO ' .
                                      '    `Revisions` (`map_fk`, `rev_map_file_name`, `rev_map_file_path`, `rev_map_version`, `rev_map_description_short`, `rev_map_description`, `rev_status_fk`) '.
                                      'VALUES ' .
                                      '    (:mapid, :filename, :filepath, :mapversion, :mapdescshort, :mapdescfull, :revstatusid);';
                    $dbHandler -> PrepareAndBind($insertRevQuery, Array('mapid' => $mapId,
                                                                        'filename' => $mapName . '.zip',
                                                                        'filepath' => $mapDirOnDisk,
                                                                        'mapversion' => $mapVersion,
                                                                        'mapdescshort' => $mapDescShort,
                                                                        'mapdescfull' => $mapDescFull,
                                                                        'revstatusid' => 1));
                    $dbHandler -> Execute();
                    $revId = $dbHandler -> GetLastInsertId();
                    $dbHandler -> Clean();

                    if ($revId == null)
                        throw new Exception('Could not add the map to the database');

                    if (!$this -> uploadImages($dbHandler, $mapName, $mapDirOnDisk, $revId))
                        throw new Exception('Could not add the screenshots to the map');

                    $content = '<div class="alert alert-success" role="alert">' . PHP_EOL .
                               '    Map has been added successfully!<br />' . PHP_EOL .
                               '    Redirecting you now.' . PHP_EOL .
                               '</div>' . PHP_EOL;
                };
            } catch (Exception $e) {
                $content = '<div class="alert alert-danger" role="alert">' . PHP_EOL .
                           '    Something went wrong, please try again later' . PHP_EOL .
                           PHP_EOL .
                           '    Message : ' . $e -> getMessage();
                           '</div>' . PHP_EOL;
            };

            return $content;
        }

        private function uploadImages(&$dbHandler, $mapName, $mapDir, $revId, $oldRevId = null) {
            global $config, $request;

            try {
                $cleanMapName  = $this -> utils -> cleanInput($mapName, True);
                $imageDir      = $config['files']['uploadDir'] . '/images/' . $cleanMapName;
                $imageOrderNum = 0;

                if ($oldRevId !== null) {
                    $selectQuery = 'SELECT ' .
                                   '    * ' .
                                   'FROM ' .
                                   '    `Screenshots` ' .
                                   'WHERE ' .
                                   '    `rev_fk` = :revid ' .
                                   'ORDER BY ' .
                                   '    `screen_order` ASC;';
                    $dbHandler -> PrepareAndBind($selectQuery, Array('revid' => $oldRevId));
                    $oldScreenshotFiles = $dbHandler -> ExecuteAndFetchAll();
                    $dbHandler -> Clean();
                } else {
                    $oldScreenshotFiles = Array();
                };

                // Because PHP uses an odd manner of stacking multiple files into an array we will re-array them here
                if (count($_FILES['screenshotFiles']['name']) > 0) {
                    $screenshotFiles = $this -> utils -> reArrayFiles($_FILES['screenshotFiles']);
                } else {
                    $screenshotFiles = Array();
                };

                foreach ($screenshotFiles as $screenshotFile) {
                    $detectedType = exif_imagetype($screenshotFile['tmp_name']);
                    $validFile    = in_array($detectedType, $config['images']['allowedTypes']);

                    if ($validFile) {
                        $imageObject = new Imagick($screenshotFile['tmp_name']);
                        $this -> utils -> resizeImage($imageObject, $config['images']['maxWidth'], $config['images']['maxHeight']);

                        if ($detectedType == IMAGETYPE_GIF) {
                            $imageExtention = '.gif';
                        } else {
                            $imageExtention = '.png';
                            $imageObject -> setImageFormat('png');
                        };

                        $imageObject -> writeImage(APP_DIR . $mapDir . $cleanMapName . '-' . $imageOrderNum . $imageExtention);
                        $imageObject -> destroy();

                        $insertQuery = 'INSERT INTO ' .
                                       '    `Screenshots` (`rev_fk`, `screen_title`, `screen_alt`, `screen_file_name`, `screen_path`, `screen_order`) ' .
                                       'VALUES ' .
                                       '    (:revid, :screentitle, :screenalt, :screenfilename, :screenpath, :screenorder);';
                        $dbHandler -> PrepareAndBind($insertQuery, Array('revid'          => $revId,
                                                                         'screentitle'    => $cleanMapName . '-' . $imageOrderNum,
                                                                         'screenalt'      => $cleanMapName . '-' . $imageOrderNum . $imageExtention,
                                                                         'screenfilename' => $cleanMapName . '-' . $imageOrderNum . $imageExtention,
                                                                         'screenpath'     => $mapDir,
                                                                         'screenorder'    => $imageOrderNum));
                        $dbHandler -> Execute();

                        $imageOrderNum++;
                    };
                };

                foreach ($oldScreenshotFiles as $oldScreenshotFile) { // Only append these to the revision but keep original location
                    $insertQuery = 'INSERT INTO ' .
                                   '    `Screenshots` (`rev_fk`, `screen_title`, `screen_alt`, `screen_file_name`, `screen_path`, `screen_order`) ' .
                                   'VALUES ' .
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