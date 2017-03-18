<?php
    $config = Array();

    $config['tpl']['main']     = APP_DIR . '/include/main.tpl';
    $config['tpl']['nav']      = APP_DIR . '/include/nav.tpl';
    $config['tpl']['adminNav'] = APP_DIR . '/include/admin-nav.tpl';

    $config['db']['server']   = 'localhost';
    $config['db']['database'] = 'aDatabase';
    $config['db']['username'] = 'aUser';
    $config['db']['password'] = 'aPassword';

    $config['files']['queueDir']          = APP_DIR . '/uploads/Queue';
    $config['files']['defaultDir']        = APP_DIR . '/uploads/Default';
    $config['files']['allowedExtensions'] = Array('.zip', '.rar', '.7z', '.kpmap');

    $config['reCaptcha']['siteKey']   = 'google site key';
    $config['reCaptcha']['secretKey'] = 'google secret key';

    return $config;
