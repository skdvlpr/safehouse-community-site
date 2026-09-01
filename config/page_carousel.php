<?php

return [

    /*
    | Optional hero carousel stored in pages.meta.carousel (any template).
    | Pattern: WordPress "featured gallery" / Strapi repeatable media component.
    */
    'disk' => 'public',
    'directory' => 'page-carousels',
    'article_directory' => 'article-carousels',
    'max_slides' => 12,
    'max_upload_kb' => 25600,
    'max_stored_kb' => 8192,
    'max_dimension' => 2560,

];
