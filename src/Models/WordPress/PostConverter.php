<?php
declare(strict_types = 1);

namespace App\Models\WordPress;

class PostConverter
{
    /**
     *
     * @var \Slim\Router
     */
    protected $router;

    public function __construct(\Slim\Router $router)
    {
        $this->router = $router;
    }

    /**
     *
     * @param \WP_Post $wpPost
     * @return \stdClass
     */
    public function processPost(\WP_Post $wpPost)
    {
        setup_postdata($wpPost);

        $url = '';
        $date = '';

        switch (get_post_type($wpPost)) {
            case 'post':
                $url = $this->router->pathFor('wp-post-' . $wpPost->ID);
                $date = get_the_date('', $wpPost);
                break;

            case 'page':
                $url = $this->router->pathFor('wp-page', [
                    'slug' => $wpPost->post_name
                ]);
                break;

            default:
                break;
        }

        return (object) [
            'id' => $wpPost->ID,
            'content' => apply_filters('the_content', get_the_content()),
            'title' => get_the_title($wpPost),
            'permalink' => $url,
            'date' => $date,
            'author' => [
                'name' => get_the_author(),
                'slug' => get_the_author_meta('user_nicename'),
            ],
        ];
    }

    /**
     *
     * @param \WP_Post[] $wpPosts
     * @return \stdClass[]
     */
    public function processPosts(array $wpPosts)
    {
        $posts = [];

        foreach ($wpPosts as $k => $wpPost) {
            $posts[$k] = $this->processPost($wpPost);
        }

        return $posts;
    }
}
