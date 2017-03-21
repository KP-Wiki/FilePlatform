<?php
    namespace Functions;
    use \ZipArchive;

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
                    throw new \Exception('Invalid request, inputs missing');

                if (isset($_POST['map_pk']) && !Empty($_POST['map_pk'])) {
                    $content = '';
                } else {
                    $mapName         = $_POST['mapName'];
                    $mapType         = IntVal($_POST['mapType']);
                    $mapVersion      = $_POST['mapVersion'];
                    $mapDescShort    = $_POST['mapDescShort'];
                    $mapDescFull     = $_POST['mapDescFull'];
                    $mapArchive      = new ZipArchive();
                    $mapDirInArchive = $mapName . '/';
                    $mapDirOnDisk    = $config['files']['uploadDir'] . '/' . $mapName . '/' . $mapVersion . '/';

                    // Create the directory that will hold our newly created ZIP archive
                    $this -> utils -> mkdirRecursive($mapDirOnDisk);

                    // Because PHP uses an odd manner of stacking multiple files into an array we will re-array them here
                    if (count($_FILES['libxFiles']['name']) > 0) {
                        $libxFiles = $this -> utils -> reArrayFiles($_FILES['libxFiles']);
                    } else {
                        $libxFiles = Array();
                    };

                    // Because PHP uses an odd manner of stacking multiple files into an array we will re-array them here
                    if (count($_FILES['screenshotFiles']['name']) > 0) {
                        $screenshotFiles = $this -> utils -> reArrayFiles($_FILES['screenshotFiles']);
                    } else {
                        $screenshotFiles = Array();
                    };

                    // Try to create the new archive
                    if ($mapArchive -> open($mapDirOnDisk . $mapName . '.zip', ZIPARCHIVE::CREATE) !== True)
                        throw new \Exception('Unable to create the archive');

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

                    $content = '';
                };
            } catch (Exception $e) {
                $content = 'Something went wrong, please try again later';
            };

            return $content;
        }
    }
