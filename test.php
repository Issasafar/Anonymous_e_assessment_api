<?php
$contentType = 'application/json';

if (strpos($contentType, 'application/json') !== false) {
    echo "the content is json";
} else {
    echo "not json";
}