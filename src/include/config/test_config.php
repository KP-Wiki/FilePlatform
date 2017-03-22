<?php
    $config = Array();

    $config['tpl']['main']       = APP_DIR . '/include/main.tpl';
    $config['tpl']['nav']        = APP_DIR . '/include/nav.tpl';
    $config['tpl']['userNav']    = APP_DIR . '/include/user-nav.tpl';
    $config['tpl']['contribNav'] = APP_DIR . '/include/contributor-nav.tpl';
    $config['tpl']['adminNav']   = APP_DIR . '/include/admin-nav.tpl';

    $config['db']['server']   = 'localhost';
    $config['db']['database'] = 'aDatabase';
    $config['db']['username'] = 'aUser';
    $config['db']['password'] = 'aPassword';

    $config['files']['uploadDir']  = '/uploads';
    $config['files']['queueDir']   = '/uploads/Queue';
    $config['files']['defaultDir'] = '/uploads/Default';

    $config['images']['maxWidth']     = 1280;
    $config['images']['maxHeight']    = 720;
    $config['images']['allowedTypes'] = Array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP);

    $config['reCaptcha']['siteKey']   = 'google site key';
    $config['reCaptcha']['secretKey'] = 'google secret key';

    $config['security']['cookieLifetime'] = 15778463; // ~6 months in seconds

    return $config;
