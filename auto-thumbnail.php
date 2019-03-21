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

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . 'admin-setting.php';

/**
 * 从文章中提取一张图片
 * @param WP_Post $post
 * @return array|null
 */
function get_image(WP_Post $post){
    $result = array();
    $first = get_option('_auto_thumbnail_first');

    // 通过first判断是否选择第一张图片作为缩略图
    if ($first) {
        preg_match('/<img.*?src=\"(.*?[\.=](jpg|gif|bmp|bnp|png|jpeg))\".*?\/?>/i', $post->post_content,$match);
        if (!empty($match)) {
            $result['img'] = $match[0];
            $result['src'] = $match[1];
            $result['ext'] = $match[2];
        }
    } else {
        preg_match_all('/<img.*?src=\"(.*?[\.=](jpg|gif|bmp|bnp|png|jpeg))\".*?\/?>/i', $post->post_content,$matches);
        if (!empty($matches)) {
            $rand = rand(0, count($matches[0]) - 1);
            $result['img'] = $matches[0][$rand];
            $result['src'] = $matches[1][$rand];
            $result['ext'] = $matches[2][$rand];
        }
    }

    if (!empty($result)) {
        return $result;
    }
}

/**
 * 自动为没有特色图片的文章设置一张特色图片，
 * @param $html
 * @param $post_ID
 * @param $post_thumbnail_id
 * @param $size
 * @param $attr
 * @return string
 */
function auto_thumbnail($html, $post_ID, $post_thumbnail_id, $size, $attr ) {
    // 如果有特色图片直接返回
    if (!empty($html)) {
        return $html;
    }

    global $post;

    $img = get_image($post);

    // 如果文章没有图片，返回一张默认图片
    if (empty($img)) {
        $attach_id = get_option('_auto_thumbnail_default_image');
    } else {
        $src = $img['src'];
        $state = @file_get_contents($src,0,null,0,1);
        $wp_upload_dir = wp_upload_dir();
        if ($state) {
            $filename = $wp_upload_dir['path'] . '/' . date("dMYHis"). '.' . $img['ext'];//文件名称生成
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

        } else {
            $attach_id = get_option('_auto_thumbnail_default_image');
        }
    }

    if (!empty($attach_id)) {
        set_post_thumbnail( $post->ID, $attach_id );
        return get_the_post_thumbnail($post, $size);
    } else {
        return $html;
    }
}

$admin_setting = new admin_setting();

add_filter('post_thumbnail_html', 'auto_thumbnail', 10, 5);