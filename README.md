# Slim 3 Skeleton with WordPress support

WordPress handles content management while Slim is used for front-office rendering, mixing content from WordPress and custom pages/templates.

**But... why?!** :hushed: I needed a very specific multilingual/multicountry routing system (not included here, too specific to the project) for a website having a user-friendly back office for managing content. And also... for fun :smiley:

## What's included?

* My own [Slim 3 app skeleton](https://github.com/aurmil/slim3-skeleton)
* WordPress support

## Installation and usage

I invite you to read first the short documentation I wrote for my [app skeleton](https://github.com/aurmil/slim3-skeleton).

Required: PHP 7 and [Composer](https://getcomposer.org/doc/00-intro.md)

Run the following command, replacing `[your-project-name]` with the name of the folder you want to create.

```sh
composer create-project aurmil/slim3-skeleton-wordpress [your-project-name]
```

`.htaccess` file, Web server choice, virtual host, `AllowOverride All`, `var` folder permissions... please refer to the main documentation.

**WordPress:**

* Optional: you can rename the `wp` folder to whatever you want, just put the correct folder name in `wordpress.yaml` configuration file.
* Unzip a WordPress archive in `public/wp` (or else if you changed it) folder and install it.

## WordPress integration

### Meta title

The config entry `app.title` from the Slim 3 app skeleton is not used here. Instead of it, title is read from WordPress configuration.

On the home page, meta title is composed of blog name + blog description, as in a normal WordPress home page.

### Posts lists

WordPress handles on its onw the number of posts to display in lists.

All pages displaying a list of posts (home, category, tag, ...) include an optional parameter at the end of their route: `/page/{page}` for pagination.

### Routes

I read all routes formats from WordPress to minimize errors, avoid future compatibility problems and support changes in WordPress configuration (eg: edit category or tag base in admin area).

WordPress contents routing in Slim app tries to stick as much as possible to the original WordPress routes. I also consider the optional use of a trailing slash in WordPress contents routes. It depends if permalink structure ends or not with a slash in WordPress permalinks settings.

You can add your own routes for classic Slim pages/contents but take care that they do not match a WordPress-content route.

#### Posts

I let WordPress build posts permalinks with `get_permalink()` then I use them to build posts routes in Slim app.

I decided to create one route per post because it allows you to use `pathFor('wp-post-{post_id}')` (`router->pathFor()` in Slim app code and `path_for()` in Twig templates) without having to know the post permalink structure. If you change permalink structure in WordPress admin area, you won't have to change your code, posts links will remain valid.

#### Pages

Route: `/{page_slug}`, read from `$wp_rewrite->get_page_permastruct()`

As it is directly on app root (`/`), we can't match everything (`[a-zA-Z0-9_-]+`) as it would prevent to use other root routes. So, the existing pages slugs are dynamically listed in this route to only match relevant URI.

#### Categories

Route: `/{category_base}/{category_slug}`, read from `$wp_rewrite->get_category_permastruct()`

#### Tags

Route: `/{tag_base}/{tag_slug}`, read from `$wp_rewrite->get_tag_permastruct()`

#### Authors

Route: `/author/{author_slug}`, read from `$wp_rewrite->get_author_permastruct()`

#### Archives

WordPress supports many types of archives list: yearly, monthly, daily, weekly and a simple list of posts. Each type has a route pattern I tried to reuse (except the last one which belongs to classic posts routes):

* Yearly: `/{yyyy}`, read from `$wp_rewrite->get_year_permastruct()`
* Monthly: `/{yyyy}/{mm}`, read from `$wp_rewrite->get_month_permastruct()`
* Daily: `/{yyyy}/{mm}/{dd}`, read from `$wp_rewrite->get_day_permastruct()`
* Weekly: WordPress uses GET parameters, eg: `/?m=2018&w=9` so I created a specific new route: `/{yyyy}/w/{w}`

Where `yyyy` = 1900 - 2099, `mm` = 01 - 12, `dd` = 01-31, `w` = 1 - 53.

#### Search

By default, WordPress uses GET parameters on main page, eg: `/?s=test`. I did the same but with a dedicated route: `/search`, read from `$wp_rewrite->get_search_permastruct()`.

For a prettier URI format when search results are paginated, I also support `/search/{search}`, to avoid this: `/search/page/5/?s={search}`.

### Specific classes

**App\Controllers\WordPressController:** contains all WordPress-related actions. Basically, every action reads received parameters, ask WordPress to fetch corresponding items, pass them to the model to be processed and then pass the result(s) to the view, along with pagination data when necessary.

**App\Models\WordPress\PostConverter:** transforms WP_Post object(s) into standard PHP object(s), using WordPress functions, for easier further use / access to posts data.

**App\TwigExtensions\WordPress:** declares Twig functions allowing to access WordPress functions in templates files (post thumbnail, post meta, nav menu, ...). Functions names and parameters are mostly the same as in WordPress:

* get_the_post_thumbnail
* get_post_meta => `$single` is forced to true
* get_bloginfo
* get_the_category_list
* get_the_tag_list
* wp_nav_menu => `echo` is forced to false

This class also includes a custom function `replaceWordpressLinks($html)`, available through Twig function `replace_wp_links(html)`. When fetching data from WordPress, if there are some links, they all point to the WordPress pages as expected. But we need them to point on Slim pages. This function does this job, replacing links `href` values.

This can only work if WordPress and Slim app routing patterns are the same, which is the way I made this skeleton. If you changed the WordPress contents routes in Slim app, you would have to handle this.

### Layout

In `templates/layouts/main.twig`, set the name of the menu you want to display in `wp_nav_menu` call.

Layout also contains a search form next to the nav menu. You will have to uncomment this form after successfully installing WordPress in app `public` dir.

## To do

* Polylang support + language selector

## License

The MIT License (MIT). Please see [License File](https://github.com/aurmil/slim3-skeleton-wordpress/blob/master/LICENSE.md) for more information.
