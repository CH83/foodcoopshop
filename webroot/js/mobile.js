/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.Mobile = {

    getSlidebarMenu: function (side) {
        var responsiveMenu = $('<ul/>');
        responsiveMenu.addClass('sb-slidebar sb-' + side); // for css
        responsiveMenu.attr('off-canvas', 'sb-' + side + ' ' + side + ' push');
        return responsiveMenu;
    },

    getResponsiveMenuButton: function () {
        var showResponsiveMenuButton = $('<a/>');
        showResponsiveMenuButton.addClass('sb-toggle-left');
        showResponsiveMenuButton.html('<i class="fas fa-bars fa-2x"></i>');
        return showResponsiveMenuButton;
    },

    bindToggleLeft : function (controller) {
        $('.sb-toggle-left').on('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            controller.toggle('sb-left', function () {
                if ($('.sb-left').css('display') == 'block') {
                    $('body').addClass('slidebar-left-visible');
                } else {
                    $('body').removeClass('slidebar-left-visible');
                }
            });
        });
    },

    bindToggleRight: function (controller) {
        $('.sb-toggle-right').on('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            controller.toggle('sb-right');
        });
    },

    bindCloseSlidebarsOnCanvasClick : function (controller) {
        $(controller.events).on('opened', function (event, id) {
            $('html').on('click', function () {
                controller.close(id);
            });
            $('.sb-slidebar > *').on('click', function (event) {
                event.stopPropagation();
            });
        });
    },

    fixContentScrolling : function () {
        $('body').css('overflow-y', 'auto');
    },

    autoOpenSidebarLeft : function () {
        $('.sb-toggle-left').trigger('click');
    },

    initMenusAdmin: function () {

        $('#container').after(this.getSlidebarMenu('left')).attr('canvas', '');

        var menuItems = [];

        $('#menu > li').each(function () {
            var item = $(this);
            item.find('a').removeClass('btn');
            item.find('a').removeClass('btn-success');
            menuItems.push(item);
        });

        $('.sb-left').html(menuItems);

        var navBarContainer = $('.filter-container');
        navBarContainer.prepend(this.getResponsiveMenuButton());
        $('#container').prepend(navBarContainer);

        $(window).bind('resize', function () {
            foodcoopshop.Admin.adaptContentMargin();
        });

        var controller = new slidebars();
        controller.init();

        this.bindToggleLeft(controller);
        this.bindCloseSlidebarsOnCanvasClick(controller);
        this.fixContentScrolling(controller);

        foodcoopshop.Helper.showContent();

    },
    
    showSelfServiceCart : function() {
        $('.right-box').show();
        $('#products').hide();
    },
    
    hideSelfServiceCart : function() {
        $('.right-box').hide();
        $('#products').show();
    },

    initMenusSelfService: function() {
        
        $('.self-service').after(this.getSlidebarMenu('left')).attr('canvas', '');

        var menuItems = [];
        var ps = $('#product-search');
        menuItems.push(ps.wrap('<li>').parent());
 
        var pageItems = [];
        $('.footer a:not(.not-in-moblie-menu)').each(function () {
            $(this).removeClass('btn');
            $(this).removeClass('btn-success');
            $(this).find('i').remove();
            $(this).html($(this).html().trim());
            $(this).prepend($('<i/>').addClass('fa'));
            var newItem = $('<li/>').append($(this));
            pageItems.push(newItem);
        });
        pageItems = pageItems.reverse();
        menuItems = $.merge(menuItems, pageItems);

        $('.sb-left').html(menuItems);

        var headerId = 'responsive-header';
        var responsiveHeader = $('<div/>');
        responsiveHeader.attr('id', headerId);
        responsiveHeader.attr('canvas', '');
        $('body').prepend(responsiveHeader);

        $('#' + headerId).append(this.getResponsiveMenuButton());
        
        $('#' + headerId).append($('.footer .right-wrapper .btn-add-deposit'));
        $('#' + headerId).append($('.footer .left-wrapper'));
        
        var cartButtonHtml = '<a href="javascript:void(0);" class="responsive-cart"><span class="sum">' + foodcoopshop.Helper.formatFloatAsCurrency(0) + '</span><i class="fas fa-shopping-bag fa-2x"></i></a>';
        $('#' + headerId).append(cartButtonHtml);
        $('#' + headerId).find('.responsive-cart').on('click', function() {
            if ($('.right-box').css('display') == 'block') {
                foodcoopshop.Mobile.hideSelfServiceCart();
            } else {
                foodcoopshop.Mobile.showSelfServiceCart();
            }
        });

        var shoppingLimitReachedInfo = $('#cart .inner .credit-balance-wrapper .negative:not(.payment)').length;
        if (shoppingLimitReachedInfo > 0) {
            $('#' + headerId).append('<span class="negative shopping-limit-reached-info"><b>' + foodcoopshop.LocalizedJs.mobile.shoppingLimitReached + '</b></span>');
        }
        

        // button renaming
        $('.entity-wrapper .btn').html('<i class="fa fa-lg fa-shopping-bag"></i>');
        
        // move flash message into header
        $('#' + headerId).append($('#flashMessage'));
        
        var controller = new slidebars();
        controller.init();

        this.bindToggleLeft(controller);
        this.bindToggleRight(controller);
        this.bindCloseSlidebarsOnCanvasClick(controller);
        this.fixContentScrolling();

        foodcoopshop.Helper.showContent();
        
    },

    initMenusFrontend: function () {

        $('#container').after(this.getSlidebarMenu('left')).attr('canvas', '');

        var menuItems = [];

        var ps = $('#product-search');
        if (ps.length > 0) {
            menuItems.push(ps.wrap('<li>').parent());
        }

        menuItems.push('<li><a href="/"><i class="fas"></i>' + foodcoopshop.LocalizedJs.mobile.home + '</a></li>');

        $('#user-menu > li').each(function () {
            var item = $(this);
            item.find('a').removeClass('btn');
            item.find('a').removeClass('btn-success');
            menuItems.push(item);
        });

        // if all manufacturers are disabled / set to private - do not include menu item
        $('.sidebar li.header').each(function () {
            if ($(this).html() == foodcoopshop.LocalizedJs.mobile.manufacturers) {
                menuItems.push('<li><a href="/' + foodcoopshop.LocalizedJs.mobile.routeManufacturerList + '"><i class="fa"></i>' + foodcoopshop.LocalizedJs.mobile.manufacturers + '</a></li>');
            }
        });

        $('#main-menu > li > a').each(function () {
            if ($(this).html().replace(/\<i class="fas "\>\<\/i\>/, '') == foodcoopshop.LocalizedJs.mobile.news) {
                menuItems.push('<li><a href="/' + foodcoopshop.LocalizedJs.mobile.routeNewsList + '"><i class="fa"></i>' + foodcoopshop.LocalizedJs.mobile.news + '</a></li>');
            }
        });

        $('.sidebar ul#categories-menu > li').each(function () {
            menuItems.push($(this));
        });

        var pageItems = ['<li class="header">' + foodcoopshop.LocalizedJs.mobile.pages + '</li>'];
        $('#main-menu > li').each(function () {
            // take categories and manufacturers from sidebar and not from main menu
            var mainMenuHref = $(this).find('a').attr('href');
            if ($.inArray(mainMenuHref, ['/' + foodcoopshop.LocalizedJs.mobile.routeManufacturerList, foodcoopshop.LocalizedJs.mobile.routeAllCategories, '/' + foodcoopshop.LocalizedJs.mobile.routeNewsList]) == -1) {
                pageItems.push($(this));
            }
        });

        $('#footer-menu > li').each(function () {
            pageItems.push($(this));
        });

        if (pageItems.length > 1) {
            menuItems = $.merge(menuItems, pageItems);
        }

        $('.sb-left').html(menuItems);

        var headerId = 'responsive-header';
        var responsiveHeader = $('<div/>');
        responsiveHeader.attr('id', headerId);
        responsiveHeader.attr('canvas', '');
        $('body').prepend(responsiveHeader);

        $('#' + headerId).append(this.getResponsiveMenuButton());

        var infoButton = $('<a/>');
        infoButton.addClass('sb-toggle-right');
        infoButton.html('<i class="fas fa-info-circle fa-2x"></i>');
        $('#' + headerId).append(infoButton);

        var cartButton = $('#cart .inner .btn-success');
        cartButton.addClass('responsive-cart');
        cartButton.removeClass('btn btn-success');
        cartButton.html('<span class="sum">' + foodcoopshop.Helper.formatFloatAsCurrency(0) + '</span><i class="fas fa-shopping-cart fa-2x"></i>');
        $('#' + headerId).append(cartButton);

        var shoppingLimitReachedInfo = $('#cart .inner .credit-balance-wrapper .negative:not(.payment)').length;
        if (shoppingLimitReachedInfo > 0) {
            $('#' + headerId).append('<span class="negative shopping-limit-reached-info"><b>' + foodcoopshop.LocalizedJs.mobile.shoppingLimitReached + '</b></span>');
        }

        $('#' + headerId).append($('#header .logo-wrapper'));

        // button renamings
        var regexp = new RegExp(foodcoopshop.LocalizedJs.mobile.showAllProducts);
        $('.manufacturer-wrapper div.third-column a.btn').each(function (btn) {
            $(this).html($(this).html().replace(regexp, foodcoopshop.LocalizedJs.mobile.show));
        });
        $('.blog-post-wrapper div.third-column a.btn').html(foodcoopshop.LocalizedJs.mobile.show);
        $('.entity-wrapper .btn').html('<i class="fas fa-lg fa-cart-plus"></i>');
        $('#cart .btn-success').html('<i class="fas fa-shopping-cart"></i>');

        // add info box to right side bar
        $('#container').after(this.getSlidebarMenu('right')).attr('canvas', '');
        $('.sb-right').html('<div class="inner">' + $('#info-box').html() + '</div>');

        // add special infos to cart page
        var cartPage = $('body.carts.detail #inner-content h1:first');
        cartPage.after($('#cart p.instant-order-customer-info'));
        cartPage.after($('#cart div.credit-balance-wrapper'));
        cartPage.after($('#cart p.future-orders'));

        var loadLastOrderDetailsDropdown = $('#cart .inner #load-last-order-details');
        if (loadLastOrderDetailsDropdown.length > 0) {
            cartPage.after(loadLastOrderDetailsDropdown.closest('div.input'));
        }

        // move flash message into header
        $('#' + headerId).append($('#flashMessage'));

        var controller = new slidebars();
        controller.init();

        this.bindToggleLeft(controller);
        this.bindToggleRight(controller);
        this.bindCloseSlidebarsOnCanvasClick(controller);
        this.fixContentScrolling();

        foodcoopshop.Helper.showContent();

    }

};