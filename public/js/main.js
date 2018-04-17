// when no menu exists in WordPress
if ($('.navbar div.navbar-nav').length
    && $('.navbar div.navbar-nav ul').length
    && !$('.navbar div.navbar-nav ul').hasClass('navbar-nav')
) {
    var div = $('.navbar div.navbar-nav');
    var ul = $('.navbar div.navbar-nav ul');
    ul.addClass(div.attr('class'));
    div.parent().prepend(ul);
    div.remove();
}

// add missing Bootstrap classes on menu items
$('.navbar .navbar-nav li').addClass('nav-item');
$('.navbar .navbar-nav li a').addClass('nav-link');
