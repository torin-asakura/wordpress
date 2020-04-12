<?php

namespace YOOtheme\Builder\Wordpress;

use YOOtheme\Http\Request;
use YOOtheme\Http\Response;
use YOOtheme\Http\Uri;

class BuilderController
{
    public static function loadImage(Request $request, Response $response)
    {
        $src = $request('src');
        $md5 = $request('md5');

        $uri = new Uri($src);
        $file = basename($uri->getPath());

        if ($uri->getHost() === 'images.unsplash.com') {
            $file .= ".{$uri->getQueryParam('fm', 'jpg')}";
        }

        $site = get_site_url(null, '/');
        $upload = wp_upload_dir();

        // file exists already?
        while ($iterate = @md5_file("{$upload['basedir']}/{$file}")) {

            if ($iterate === $md5 || is_null($md5)) {
                return $response->withJson(str_replace($site, '', "{$upload['baseurl']}/{$file}"));
            }

            $file = preg_replace_callback('/-?(\d*)(\.[^.]+)?$/', function ($match) {
                return sprintf('-%02d%s', intval($match[1]) + 1, isset($match[2]) ? $match[2] : '');
            }, $file, 1);
        }

        // set upload dir to base
        add_filter('upload_dir', function ($upload) {

            if ($upload['subdir']) {
                $upload['url'] = $upload['baseurl'];
                $upload['path'] = $upload['basedir'];
            }

            return $upload;
        });

        // download file
        $tmp = download_url($src);

        if (is_wp_error($tmp)) {
            $request->abort(500, "{$file}: {$tmp->get_error_message()}");
        }

        // import file to uploads dir
        $id = media_handle_sideload([
            'name' => $file,
            'tmp_name' => $tmp,
        ], 0);

        if (is_wp_error($id)) {
            $request->abort(500, "{$file}: {$id->get_error_message()}");
        }

        return $response->withJson(str_replace($site, '', set_url_scheme(wp_get_attachment_url($id))));
    }
}
