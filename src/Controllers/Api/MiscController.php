<?php
    /**
     * The central controller for all misc features
     *
     * PHP version 7
     *
     * @package    MapPlatform
     * @subpackage Controllers\Api
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    namespace MapPlatform\Controllers\Api;
    
    use InvalidArgumentException;
    use Slim\Http\Request;
    use Slim\Http\Response;
    use MapPlatform\Core;
    use MapPlatform\AbstractClasses\ApiController;

    /**
     * Misc feature controller
     *
     * @package    MapPlatform
     * @subpackage Controllers\Api
     * @author     Thimo Braker <thibmorozier@gmail.com>
     * @version    1.0.0
     * @since      First available since Release 1.0.0
     */
    class MiscController extends ApiController
    {
        /**
         * MiscController invoker.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function __invoke(Request $request, Response $response, $args) {
            return $response;
        }

        /**
         * MiscController default image resizer.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function resizeDefault(Request $request, Response $response, $args) {
            $this->container->logger->info("MapPlatform '/api/v1/resizedefault" . (Empty($args['catchall']) ? "" : "/" . $args['catchall']) . "' route");
            $config = $this->container->get('settings')['images'];
            $images = [
                $config['defaultImageDir'] . 'kp_2016-08-30_21-29-44.png',
                $config['defaultImageDir'] . 'kp_2016-09-03_18-34-31.png'
            ];

            foreach ($images as $image) {
                $imageObject = new Imagick($image);
                $this->container->fileUtils->resizeImage($imageObject, $config['maxWidth'], $config['maxHeight']);
                $imageObject->writeImage($image);
                $imageObject->destroy(); 
            };

            return $response->withJson(['result' => 'Success'], 200, JSON_PRETTY_PRINT);
        }

        /**
         * MiscController script tester.
         *
         * @param \Slim\Http\Request $request
         * @param \Slim\Http\Response $response
         * @param array $args
         *
         * @return \Slim\Http\Response
         */
        public function testScript(Request $request, Response $response, $args) {
            $data = $request->getParsedBody();

            $this->container->logger->info("MapPlatform '/api/v1/testscript" . (Empty($args['catchall']) ? "" : "/" . $args['catchall']) . "' route");
            $this->container->logger->debug("MapPlatform '/api/v1/testscript' data: " . print_r($data, True));
            $config = $this->container->get('settings');

            try {
                $ScriptText = filter_var($data['ScriptText'], FILTER_SANITIZE_STRING);
                $guid       = $this->container->miscUtils->guidv4();

                $scriptFilePath = $config['appTempDir'] . $guid . '.script';
                $scriptFile     = fopen($scriptFilePath, 'w') or die('Unable to open file!');

                fwrite($scriptFile, $ScriptText);
                fclose($scriptFile);

                $descriptorSpec = array(
                    0 => array('pipe', 'r'), // stdin is a pipe that the child will read from
                    1 => array('pipe', 'w'), // stdout is a pipe that the child will write standard output to
                    2 => array('pipe', 'w')  // stderr is a pipe that the child will write error output to
                );
                $pipes   = array();
                $cwd     = $config['appRootDir'];
                $env     = array('SHELL'    => '/bin/bash',
                                 'WINEARCH' => 'win64',
                                 'HOME'     => $cwd,
                                 'LANGUAGE' => 'en_US:en');
                $command = 'wine ' . $config['appRootDir'] . '/ScriptValidatorCLI.exe -v -V ' . $scriptFilePath;
                $process = proc_open($env['SHELL'], $descriptorSpec, $pipes, $cwd, $env);

                if (is_resource($process)) {
                    /**
                     * $pipes now looks like this:
                     *   0 => Writable handle connected to child stdin
                     *   1 => Readable handle connected to child stdout
                     *   2 => Readable handle connected to child stderr
                     */
                    // Write the command to the stdin pipe and close it to avoid a deadlock
                    fwrite($pipes[0], $command);
                    fclose($pipes[0]);

                    // Retrieve the output and error output
                    $shellResponse    = stream_get_contents($pipes[1]);
                    $shellErrorOutput = stream_get_contents($pipes[2]);

                    // It is important that you close any pipes before calling proc_close in order to avoid a deadlock
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    proc_close($process);
                    unlink($scriptFilePath);

                    return $response->withJson([
                        'status' => 'Success',
                        'data' => $shellResponse,
                        'errData' => $shellErrorOutput
                    ], 200, JSON_PRETTY_PRINT);
                } else {
                    unlink($scriptFilePath);

                    return $response->withJson([
                        'status' => 'Error',
                        'message' => 'PHP goofed us. :('
                    ], 500, JSON_PRETTY_PRINT);
                };
            } catch (Exception $ex) {
                $result = [
                    'result' => 'Fail',
                    'trace' => print_r($ex, True)
                ];

                return $response->withJson($result, 500, JSON_PRETTY_PRINT);
            };
        }
    }
