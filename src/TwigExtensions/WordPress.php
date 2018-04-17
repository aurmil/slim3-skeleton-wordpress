<?php
declare(strict_types = 1);

namespace App\TwigExtensions;

class WordPress extends \Twig_Extension
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $wpBaseUrl;

    public function __construct(string $baseUrl, string $wpBaseUrl)
    {
        $this->baseUrl = $baseUrl;
        $this->wpBaseUrl = $wpBaseUrl;
    }

    /**
     * @return Twig_SimpleFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction(
                'replace_wp_links',
                [$this, 'replaceWordpressLinks'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'get_the_post_thumbnail',
                [$this, 'getThePostThumbnail']
            ),
            new \Twig_SimpleFunction(
                'get_post_meta',
                [$this, 'getPostMeta']
            ),
            new \Twig_SimpleFunction(
                'get_bloginfo',
                [$this, 'getBloginfo']
            ),
            new \Twig_SimpleFunction(
                'get_the_category_list',
                [$this, 'getTheCategoryList']
            ),
            new \Twig_SimpleFunction(
                'get_the_tag_list',
                [$this, 'getTheTagList']
            ),
            new \Twig_SimpleFunction(
                'wp_nav_menu',
                [$this, 'wpNavMenu']
            ),
        ];
    }

    public function replaceWordpressLinks(string $html): string
    {
        $replace = [];
        $linkPattern = '/<a\s[^>]*href\s*=\s*([\"\'])('
            . preg_quote($this->wpBaseUrl, '/')
            . '[^\\1]*)\1[^>]*>.*<\/a>/siU';

        preg_match_all($linkPattern, $html, $matches);

        foreach ($matches[2] as $k => $url) {
            $replace[$k] = str_replace($this->wpBaseUrl, $this->baseUrl, $url);
        }

        return str_replace($matches[2], $replace, $html);
    }

    public function getThePostThumbnail(
        int $postId,
        string $size = 'post-thumbnail',
        string $attr = ''
    ): string {
        if (function_exists('get_the_post_thumbnail')) {
            $thumb = get_the_post_thumbnail($postId, $size, $attr);

            if ($thumb) {
                return $thumb;
            }
        }

        return '';
    }

    public function getPostMeta(int $postId, string $key): string
    {
        if (function_exists('get_post_meta')) {
            $meta = get_post_meta($postId, $key, true);

            if ($meta) {
                return $meta;
            }
        }

        return '';
    }

    public function getBloginfo(
        string $show = '',
        string $filter = 'raw'
    ): string {
        if (function_exists('get_bloginfo')) {
            $info = get_bloginfo($show, $filter);

            if ($info) {
                return $info;
            }
        }

        return '';
    }

    public function getTheCategoryList(
        string $separator = '',
        string $parents = '',
        int $post_id = null
    ): string {
        if (function_exists('get_the_category_list')) {
            $categories = get_the_category_list($separator, $parents, $post_id);

            if ($categories) {
                return $this->replaceWordpressLinks($categories);
            }
        }

        return '';
    }

    public function getTheTagList(
        string $before = '',
        string $sep = '',
        string $after = '',
        int $id
    ): string {
        if (function_exists('get_the_tag_list')) {
            $tags = get_the_tag_list($before, $sep, $after, $id);

            if ($tags) {
                return $this->replaceWordpressLinks($tags);
            }
        }

        return '';
    }

    public function wpNavMenu(array $args = [])
    {
        if (function_exists('wp_nav_menu')) {
            $args['echo'] = false;
            $menu = wp_nav_menu($args);

            if ($menu) {
                return $this->replaceWordpressLinks($menu);
            }
        }

        return '';
    }
}
