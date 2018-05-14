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
        $page = (int) ($args['page'] ?? 1);
        $queryArgs = ['paged' => $page];
        $query = false;

        if (function_exists('get_posts')) {
            $query = new \WP_Query($queryArgs);
            $wpPosts = $query->get_posts();
            $posts = (new PostConverter($this->router))->processPosts($wpPosts);
        }

        return $this->render($response, 'front/home.twig', [
            'posts' => $posts,
            'pagin_current_page' => $page,
            'pagin_posts_count' => $query ? $query->found_posts : 0,
            'pagin_max_page' => $query ? $query->max_num_pages : 0,
            'pagin_route_name' => $request->getAttribute('route')->getName(),
            'pagin_route_args' => $args,
        ]);
    }

    public function clearCache(RequestInterface $request, ResponseInterface $response)
    {
        $this->cache->getStore()->flush();

        return $response->write('Cache cleared.');
    }
}
