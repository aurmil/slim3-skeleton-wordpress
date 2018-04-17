<?php

namespace App\Controllers;

use App\Models\WordPress\PostConverter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WordPressController extends Controller
{
    public function post(RequestInterface $request, ResponseInterface $response)
    {
        $routeName = $request->getAttribute('route')->getName();
        $postID = (int) substr($routeName, strrpos($routeName, '-') + 1);
        $wpPosts = get_posts(['p' => $postID]);

        if (!count($wpPosts)) {
            $notFoundHandler = $this->container->get('notFoundHandler');

            return $notFoundHandler($request, $response);
        }

        $wpPost = reset($wpPosts);
        $post = (new PostConverter($this->router))->processPost($wpPost);

        return $this->render($response, 'wordpress/post.twig', [
            'post' => $post
        ]);
    }

    public function page(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $wpPosts = get_posts([
            'post_type' => 'page',
            'name' => $args['slug'],
        ]);

        if (!count($wpPosts)) {
            $notFoundHandler = $this->container->get('notFoundHandler');

            return $notFoundHandler($request, $response);
        }

        $wpPost = reset($wpPosts);
        $post = (new PostConverter($this->router))->processPost($wpPost);

        return $this->render($response, 'wordpress/page.twig', [
            'post' => $post
        ]);
    }

    public function category(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $category = get_category_by_slug($args['slug']);

        if (!$category) {
            $notFoundHandler = $this->container->get('notFoundHandler');

            return $notFoundHandler($request, $response);
        }

        $page = (int) ($args['page'] ?? 1);
        $queryArgs = [
            'paged' => $page,
            'cat' => $category->cat_ID,
        ];
        $query = new \WP_Query($queryArgs);
        $wpPosts = $query->get_posts();
        $posts = (new PostConverter($this->router))->processPosts($wpPosts);

        return $this->render($response, 'wordpress/archive.twig', [
            'posts' => $posts,
            'pagin_current_page' => $page,
            'pagin_posts_count' => $query->found_posts,
            'pagin_max_page' => $query->max_num_pages,
            'pagin_route_name' => $request->getAttribute('route')->getName(),
            'pagin_route_args' => $args,
            'meta_title' => $category->name,
            'list_title' => sprintf(__('Category: %s'), $category->name),
        ]);
    }

    public function tag(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $tag = get_term_by('slug', $args['slug'], 'post_tag');

        if (!$tag) {
            $notFoundHandler = $this->container->get('notFoundHandler');

            return $notFoundHandler($request, $response);
        }

        $page = (int) ($args['page'] ?? 1);
        $queryArgs = [
            'paged' => $page,
            'tag_id' => $tag->term_taxonomy_id,
        ];
        $query = new \WP_Query($queryArgs);
        $wpPosts = $query->get_posts();
        $posts = (new PostConverter($this->router))->processPosts($wpPosts);

        return $this->render($response, 'wordpress/archive.twig', [
            'posts' => $posts,
            'pagin_current_page' => $page,
            'pagin_posts_count' => $query->found_posts,
            'pagin_max_page' => $query->max_num_pages,
            'pagin_route_name' => $request->getAttribute('route')->getName(),
            'pagin_route_args' => $args,
            'meta_title' => $tag->name,
            'list_title' => sprintf(__('Tag: %s'), $tag->name),
        ]);
    }

    public function author(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $author = get_user_by('slug', $args['slug']);

        if (!$author) {
            $notFoundHandler = $this->container->get('notFoundHandler');

            return $notFoundHandler($request, $response);
        }

        $page = (int) ($args['page'] ?? 1);
        $queryArgs = [
            'paged' => $page,
            'author' => $author->ID,
        ];
        $query = new \WP_Query($queryArgs);
        $wpPosts = $query->get_posts();
        $posts = (new PostConverter($this->router))->processPosts($wpPosts);

        return $this->render($response, 'wordpress/archive.twig', [
            'posts' => $posts,
            'pagin_current_page' => $page,
            'pagin_posts_count' => $query->found_posts,
            'pagin_max_page' => $query->max_num_pages,
            'pagin_route_name' => $request->getAttribute('route')->getName(),
            'pagin_route_args' => $args,
            'meta_title' => $author->get('display_name'),
            'list_title' => sprintf(__('Author: %s'), $author->get('display_name')),
        ]);
    }

    public function date(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $page = (int) ($args['page'] ?? 1);
        $queryArgs = [
            'paged' => $page,
            'year' => $args['year'],
        ];

        $title = sprintf( __( 'Year: %s' ), get_the_date( _x( 'Y', 'yearly archives date format' ) ) );

        if (isset($args['month'])) {
            $queryArgs['monthnum'] = $args['month'];
        }

        if (isset($args['day'])) {
            $queryArgs['day'] = $args['day'];
        }

        if (isset($args['week'])) {
            $queryArgs['w'] = $args['week'];
        }

        $query = new \WP_Query($queryArgs);
        $wpPosts = $query->get_posts();
        $posts = (new PostConverter($this->router))->processPosts($wpPosts);

        $metaTitle = get_the_date(_x('Y', 'yearly archives date format'), reset($wpPosts));
        $listTitle = sprintf(__('Year: %s'), $metaTitle);

        if (isset($args['day'])) {
            $metaTitle = get_the_date(_x('F j, Y', 'daily archives date format'), reset($wpPosts));
            $listTitle = sprintf(__('Day: %s'), $metaTitle);
        } elseif (isset($args['month'])) {
            $metaTitle = get_the_date(_x('F Y', 'monthly archives date format'), reset($wpPosts));
            $listTitle = sprintf(__('Month: %s'), $metaTitle);
        }

        return $this->render($response, 'wordpress/archive.twig', [
            'posts' => $posts,
            'pagin_current_page' => $page,
            'pagin_posts_count' => $query->found_posts,
            'pagin_max_page' => $query->max_num_pages,
            'pagin_route_name' => $request->getAttribute('route')->getName(),
            'pagin_route_args' => $args,
            'meta_title' => $metaTitle,
            'list_title' => $listTitle,
        ]);
    }

    public function search(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $search = $request->getQueryParam('s', $args['s'] ?? null);

        if (!$search) {
            $notFoundHandler = $this->container->get('notFoundHandler');

            return $notFoundHandler($request, $response);
        }

        if (!isset($args['s'])) {
            $args['s'] = $search;
        }

        $page = (int) ($args['page'] ?? 1);
        $queryArgs = [
            'paged' => $page,
            'post_type' => get_post_types(['public' => true]),
            's' => $search,
        ];
        $query = new \WP_Query($queryArgs);
        $wpPosts = $query->get_posts();
        $posts = (new PostConverter($this->router))->processPosts($wpPosts);

        return $this->render($response, 'wordpress/archive.twig', [
            'posts' => $posts,
            'pagin_current_page' => $page,
            'pagin_posts_count' => $query->found_posts,
            'pagin_max_page' => $query->max_num_pages,
            'pagin_route_name' => $request->getAttribute('route')->getName(),
            'pagin_route_args' => $args,
            'meta_title' => sprintf(__("Search results for &#8220;%s&#8221;"), $search),
            'list_title' => sprintf(__('Search Results for: %s'), $search),
            'search_query' => $search,
        ]);
    }
}
