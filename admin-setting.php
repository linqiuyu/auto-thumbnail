<?php
/*
 * This file is part of the Creation Widgets
 * Copyright (c) Creation Studio Limited.
 */

class admin_setting {
    public function __construct() {
        add_action('carbon_fields_register_fields', array($this, 'attach_options'));
        add_action('after_setup_theme', array($this, 'crb_load'));
    }

    public function attach_options() {
        \Carbon_Fields\Container::make('theme_options', '文章特色图片')
            ->add_fields(array(
                \Carbon_Fields\Field::make('checkbox', 'auto_thumbnail_first', '是否选择第一张图片作为特色图片， 否为随机选取文章图片'),
                \Carbon_Fields\Field::make('image', 'auto_thumbnail_default_image', '设置默认图片，在文章没有图片时使用默认图片作为文章特色图片')
            ));
    }

    public function crb_load() {
        \Carbon_Fields\Carbon_Fields::boot();
    }
}