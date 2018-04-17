<?php

namespace App\Controllers;

use App\Models\WordPress\PostConverter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FrontController extends Controller
{
    public function home(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $posts = [];

        if (function_exists('get_posts')) {
            $page = (int) ($args['page'] ?? 1);
            $queryArgs = ['paged' => $page];
            $query = new \WP_Query($queryArgs);
            $wpPosts = $query->get_posts();
            $posts = (new PostConverter($this->router))->processPosts($wpPosts);
        }

        return $this->render($response, 'front/home.twig', [
            'posts' => $posts,
            'pagin_current_page' => $page,
            'pagin_posts_count' => $query->found_posts,
            'pagin_max_page' => $query->max_num_pages,
            'pagin_route_name' => $request->getAttribute('route')->getName(),
            'pagin_route_args' => $args,
            // remove this var when is_current_path('home') is fixed,
            // see https://github.com/slimphp/Twig-View/issues/69
            'is_home' => true,
        ]);
    }
}
