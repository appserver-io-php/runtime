<?php

// header('Set-Cookie: track=978268624934537');

foreach (getallheaders() as $name => $value) {
    echo $name . ': ' . $value . '<br/>';
}