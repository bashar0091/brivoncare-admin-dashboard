<?php

function logger($msg)
{
    echo "<script>console.log(" . json_encode($msg) . ")</script>";
}
