<?php

$NNTP_HOST = 'localhost';
if (getenv('NNTP_HOST')) {
    $NNTP_HOST = getenv('NNTP_HOST');
}
