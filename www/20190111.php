<?php

require_once 'XML/RPC2/Client.php';
require_once 'XML/RPC2/Backend/Php/Client.php';

try {
    $url = 'https://' . getenv('WORDPRESS_USERNAME') . '.wordpress.com/xmlrpc.php';
    error_log('url : ' . $url);
    $client = XML_RPC2_Client::create($url, ['prefix' => 'wp.']);

    error_log('xmlrpc : getUsersBlogs');
    $result = $client->getUsersBlogs(getenv('WORDPRESS_USERNAME'), getenv('WORDPRESS_PASSWORD'));
    error_log($log_prefix . 'RESULT : ' . print_r($result, true));

    $blogid = $result[0]['blogid'];

    $client = XML_RPC2_Backend_Php_Client::create($url, ['prefix' => 'wp.', 'connectionTimeout' => 1000]);
    
    error_log('xmlrpc : newPost');
    $post_data = ['post_title' => date('Y/m/d H:i:s', strtotime('+9 hours')) . " TEST RPCXML TEST",
                  'post_content' => '.',
                  'post_status' => 'publish'];        
    $result = $client->newPost($blogid, getenv('WORDPRESS_USERNAME'), getenv('WORDPRESS_PASSWORD'), $post_data);
    error_log('RESULT : ' . print_r($result, true));
} catch (Exception $e) {
    error_log('Exception : ' . $e->getMessage());
}
