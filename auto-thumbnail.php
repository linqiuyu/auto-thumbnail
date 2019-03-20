<?php
/*
Plugin Name: Auto Thumbnail
Plugin URI: http://www.fishgo.top
Description: Bunyad Shortcodes adds multiple shortcode functionality for ThemeSphere themes.
Version: 1.0.9
Author: linqiuyu
Author URI: http://www.fishgo.top
License: GPL2
*/

function auto_thumbnail($html, $post_ID, $post_thumbnail_id, $size, $attr ) {
    // 如果有特色图片直接返回
    if (!empty($html)) {
        return $html;
    }

    global $post;

    preg_match('/<img.*?src=\"(.*?[\.=](jpg|gif|bmp|bnp|png|jpeg))\".*?\/?>/i', $post->post_content,$match);

    // 如果文章没有图片，返回一张默认图片
    if (empty($match)) {
        return $html;
    }

    $src = $match[1];
    $state = @file_get_contents($src,0,null,0,1);
    $wp_upload_dir = wp_upload_dir();
    if ($state) {
        $filename = $wp_upload_dir['path'] . '/' . date("dMYHis"). '.' . $match[2];//文件名称生成
        $fp2 = @fopen($filename, "a");
        ob_start();//打开输出
        readfile($src);//输出图片文件
        $img = ob_get_contents();//得到浏览器输出
        ob_end_clean();//清除输出并关闭
        fwrite($fp2, $img);//向当前目录写入图片文件，并重新命名
        fclose($fp2);
        $filetype = wp_check_filetype($filename);
        $attachment = array(
            'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
            'post_mime_type' => $filetype['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        $attach_id = wp_insert_attachment( $attachment, $filename, $post->ID );

        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        set_post_thumbnail( $post->ID, $attach_id );

        return get_the_post_thumbnail($post, $size);
    }


}

add_filter('post_thumbnail_html', 'auto_thumbnail', 10, 5);