<?php

require_once('../../libraries/config.php');
require_once(LIB_DIR.'session.lib.php');
session_destroy(); 
header('Location: /auth/'); // back to the login screen
