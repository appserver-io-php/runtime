<?php

if (mail('info@appserver.io', 'Test-Subject', 'Test-Message') === false) {
    die('Can\'t send mail!');
}