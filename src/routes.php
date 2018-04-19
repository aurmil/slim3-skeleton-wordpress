<?php

$homePattern = '/';

// WordPress

if (isset($wp_rewrite)
    && class_exists('WP_Rewrite', false)
    && $wp_rewrite instanceof WP_Rewrite
) {
    $wpTrailingSlash = $wp_rewrite->use_trailing_slashes ? '/' : '';

    // home: posts list pagination
    $homePattern .= '[' . ('/' === substr($homePattern, -1) ? '' : '/')
        . 'page/{page:[1-9]\d*}' . $wpTrailingSlash . ']';

    $wpRoutes = $container->cache->get('wp-routes') ?: [];

    if (!count($wpRoutes)) {
        // posts

        if (function_exists('get_posts') && function_exists('get_permalink')) {
            $posts = get_posts([
                'numberposts' => -1,
            ]);
            $wpBaseUrl = $container->request->getUri()->getBaseUrl()
                . '/' . $config['wordpress']['folder_name'];

            foreach ($posts as $post) {
                $permalink = get_permalink($post);
                $pattern = str_replace($wpBaseUrl, '', $permalink);

                $wpRoutes[] = [
                    'pattern' => $pattern,
                    'callable' => 'App\Controllers\WordPressController:post',
                    'name' => 'wp-post-' . $post->ID,
                ];
            }

            unset($posts);
        }

        // pages

        if (function_exists('get_pages')) {
            $pages = get_pages([
                'child_of' => false,
                'hierarchical' => false,
            ]);

            if (count($pages)) {
                $slugs = array_column($pages, 'post_name');
                $structure = '/' . trim($wp_rewrite->get_page_permastruct(), '/');
                $pattern = str_replace(
                    '%pagename%',
                    '{slug:' . implode('|', $slugs) . '}',
                    $structure
                ) . $wpTrailingSlash;

                $wpRoutes[] = [
                    'pattern' => $pattern,
                    'callable' => 'App\Controllers\WordPressController:page',
                    'name' => 'wp-page',
                ];
            }

            unset($pages);
        }

        // categories

        // when using custom prefix, get_xyz_permastruct() does not include leading /
        $structure = '/' . trim($wp_rewrite->get_category_permastruct(), '/');
        $pattern = str_replace(
            '%category%',
            '{parents:(?:[\w-]+/)*}{slug:[\w-]+}',
            $structure
        ) . $wpTrailingSlash;
        $pattern .= '[' . ('/' === substr($pattern, -1) ? '' : '/')
            . 'page/{page:[1-9]\d*}' . $wpTrailingSlash . ']';

        $wpRoutes[] = [
            'pattern' => $pattern,
            'callable' => 'App\Controllers\WordPressController:category',
            'name' => 'wp-category',
        ];

        // tags

        $structure = '/' . trim($wp_rewrite->get_tag_permastruct(), '/');
        $pattern = str_replace('%post_tag%', '{slug:[\w-]+}', $structure)
            . $wpTrailingSlash;
        $pattern .= '[' . ('/' === substr($pattern, -1) ? '' : '/')
            . 'page/{page:[1-9]\d*}' . $wpTrailingSlash . ']';

        $wpRoutes[] = [
            'pattern' => $pattern,
            'callable' => 'App\Controllers\WordPressController:tag',
            'name' => 'wp-tag',
        ];

        // authors

        $structure = '/' . trim($wp_rewrite->get_author_permastruct(), '/');
        $pattern = str_replace('%author%', '{slug:[\w-]+}', $structure)
            . $wpTrailingSlash;
        $pattern .= '[' . ('/' === substr($pattern, -1) ? '' : '/')
            . 'page/{page:[1-9]\d*}' . $wpTrailingSlash . ']';

        $wpRoutes[] = [
            'pattern' => $pattern,
            'callable' => 'App\Controllers\WordPressController:author',
            'name' => 'wp-author',
        ];

        // date archives

        $dateSearch = ['%year%', '%monthnum%', '%day%'];
        $dateReplace = [
            '{year:(?:19|20)\d{2}}',
            '{month:(?:0[1-9]|1[0-2])}',
            '{day:(?:0[1-9]|[1-2]\d|3[0-1])}',
        ];

        $structure = '/' . trim($wp_rewrite->get_year_permastruct(), '/');
        $pattern = str_replace($dateSearch, $dateReplace, $structure)
            . $wpTrailingSlash;
        $pattern .= '[' . ('/' === substr($pattern, -1) ? '' : '/')
            . 'page/{page:[1-9]\d*}' . $wpTrailingSlash . ']';

        $wpRoutes[] = [
            'pattern' => $pattern,
            'callable' => 'App\Controllers\WordPressController:date',
            'name' => 'wp-archives-yearly',
        ];

        $structure = '/' . trim($wp_rewrite->get_month_permastruct(), '/');
        $pattern = str_replace($dateSearch, $dateReplace, $structure)
            . $wpTrailingSlash;
        $pattern .= '[' . ('/' === substr($pattern, -1) ? '' : '/')
            . 'page/{page:[1-9]\d*}' . $wpTrailingSlash . ']';

        $wpRoutes[] = [
            'pattern' => $pattern,
            'callable' => 'App\Controllers\WordPressController:date',
            'name' => 'wp-archives-monthly',
        ];

        $structure = '/' . trim($wp_rewrite->get_day_permastruct(), '/');
        $pattern = str_replace($dateSearch, $dateReplace, $structure)
            . $wpTrailingSlash;
        $pattern .= '[' . ('/' === substr($pattern, -1) ? '' : '/')
            . 'page/{page:[1-9]\d*}' . $wpTrailingSlash . ']';

        $wpRoutes[] = [
            'pattern' => $pattern,
            'callable' => 'App\Controllers\WordPressController:date',
            'name' => 'wp-archives-daily',
        ];

        $structure = '/' . trim($wp_rewrite->get_year_permastruct(), '/');
        $pattern = str_replace($dateSearch, $dateReplace, $structure)
            . '/w/{week:(?:[1-9]|[1-4]\d|5[0-3])}'
            . $wpTrailingSlash;
        $pattern .= '[' . ('/' === substr($pattern, -1) ? '' : '/')
            . 'page/{page:[1-9]\d*}' . $wpTrailingSlash . ']';

        $wpRoutes[] = [
            'pattern' => $pattern,
            'callable' => 'App\Controllers\WordPressController:date',
            'name' => 'wp-archives-weekly',
        ];

        // search

        $structure = '/' . trim($wp_rewrite->get_search_permastruct(), '/');
        $pattern = str_replace('%search%', '', $structure);
        $pattern = rtrim($pattern, '/') . $wpTrailingSlash;
        $pattern .= '[' . ('/' === substr($pattern, -1) ? '' : '/')
            . '{s}' . $wpTrailingSlash;
        $pattern .= '[' . ('/' === substr($pattern, -1) ? '' : '/')
            . 'page/{page:[1-9]\d*}' . $wpTrailingSlash . ']';
        $pattern .= ']'; // end of multiple nested optional parts

        $wpRoutes[] = [
            'pattern' => $pattern,
            'callable' => 'App\Controllers\WordPressController:search',
            'name' => 'wp-search',
        ];

        $container->cache->put(
            'wp-routes',
            $wpRoutes,
            $config['wordpress']['routes_cache_duration']
        );
    }

    foreach ($wpRoutes as $route) {
        $app->get($route['pattern'], $route['callable'])
            ->setName($route['name']);
    }
}

$app->get($homePattern, 'App\Controllers\FrontController:home')
    ->setName('home');

$app->get('/clear-cache', 'App\Controllers\FrontController:clearCache')
    ->add(new \Slim\Middleware\HttpBasicAuthentication([
        'secure' => $config['security']['restricted_access_https_only'],
        'users' => $config['security']['restricted_access_users'],
    ]));

// Page not found handler
$container['notFoundHandler'] = function ($container) {
    return function (
        Psr\Http\Message\RequestInterface $request,
        Psr\Http\Message\ResponseInterface $response,
        Slim\Exception\NotFoundException $exception = null
    ) use ($container) {
        return $container->view->render(
            $response->withStatus(404),
            'errors/not-found.twig'
        );
    };
};
