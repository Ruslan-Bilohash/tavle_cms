<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/functions.php';
logoutUser();
redirect('/admin/login.php');