<?php
header('Content-type: application/json');
header('X-JSON: ' . $js->object($response));
if($response['success'] == null)
    header('HTTP/1.1 500 Server Error', true, 500);

echo json_encode($data);
