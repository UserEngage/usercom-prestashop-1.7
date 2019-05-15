/* global combinations, attributesCombinations, prestashop, id_product, userengage */

/**
 * File from https://prestashow.pl
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *  @authors     PrestaShow.pl <contact@prestashow.pl>
 *  @copyright   2018 PrestaShow.pl
 *  @license     https://prestashow.pl/license
 */

/**
 * UserEngage
 */
UserEngage = {debug: false, data: []};

/**
 * Log text
 *
 * @param txt
 */
UserEngage.log = function (txt) {
    if (UserEngage.debug) {
        console.log("UserEngage: " + txt);
    }
};

/**
 * Show error
 *
 * @param string txt
 */
UserEngage.error = function (txt) {
    if (UserEngage.debug) {
        console.error("UserEngage Error: " + txt);
    }
};

/**
 * Log object
 *
 * @param object obj
 */
UserEngage.logObj = function (obj) {
    if (UserEngage.debug) {
        console.log("UserEngage:");
        console.log(obj);
    }
};

/**
 * Create product event
 *
 * @param object data
 */
UserEngage.pushProductEvent = function (data) {
    UserEngage.log("sending product event with data:");
    UserEngage.logObj(data);

    if (typeof userengage !== "function") {
        UserEngage.error("base script from userengage.com not loaded");
        return;
    }

    userengage('product_event', data);
};

/**
 * Create event
 *
 * @param object data
 */
UserEngage.pushEvent = function (event, data) {
    UserEngage.log("sending event." + event + " with data:");
    UserEngage.logObj(data);

    if (typeof userengage !== "function") {
        UserEngage.error("base script from userengage.com not loaded");
        return;
    }

    userengage("event." + event, data);
};

/**
 * UserEngage events
 */
UserEngage.event = {

    /**
     * event: checkout_option
     *
     * @param object data
     */
    checkoutOption: function (data) {
        UserEngage.pushEvent("checkout_option", data);
    },

    /**
     * event: purchase
     *
     * @param object orderDetails
     */
    purchase: function (orderDetails) {
        UserEngage.pushEvent("purchase", orderDetails);
    },

    /**
     * event: registration
     *
     * @param object customerDetails
     */
    registration: function (customerDetails) {
        UserEngage.pushEvent("registration", customerDetails);
    },

    /**
     * event: order
     *
     * @param checkoutStep
     */
    order: function (checkoutStep) {
        UserEngage.pushEvent("order", {step: parseInt(checkoutStep)});
    },

    /**
     * event: newsletter_signup
     *
     * @param email
     * @param url
     */
    newsletter: function (email, url) {
        let org_civchat = null;

        if (typeof window.civchat !== 'undefined' &&
            (typeof window.civchat.email === 'undefined' ||
            window.civchat.email !== email)) {
            org_civchat = window.civchat;
            window.civchat = {'apiKey':  window.civchat.apiKey};

            UserEngage.log("temporary changed window.civchat for event.newsletter_signup");
            UserEngage.logObj(window.civchat);
        }

        UserEngage.pushEvent("newsletter_signup", {'email': email, 'place': url});

        // restore original window.civchat
        if (org_civchat !== null) {
            setTimeout(function(){
                window.civchat = org_civchat;

                UserEngage.log("restored original window.civchat");
                UserEngage.logObj(org_civchat);
            }, 300);
        }
    }

};

/**
 * UserEngage product events
 */
UserEngage.event.product = {

    /**
     * event: add to cart
     *
     * @param object product
     */
    addToCart: function (product) {
        this._cartUpdate('add to cart', product);
    },

    /**
     * event: remove
     *
     * @param object product
     */
    remove: function (product) {
        this._cartUpdate('remove', product);
    },

    /**
     * event: view
     */
    view: function () {
        this._productView('view');
    },

    /**
     * event: view
     */
    liking: function () {
        this._productView('liking');
    },

    /**
     * event: detail
     */
    detail: function () {
        this._productView('detail');
    },

    /**
     * event: click
     *
     * @param object eventDetails
     */
    click: function (eventDetails) {
        eventDetails.event_type = 'click';
        UserEngage.pushProductEvent(eventDetails);
    },

    /**
     * event: promo click
     *
     * @param object eventDetails
     */
    promoClick: function (eventDetails) {
        eventDetails.event_type = 'promo click';
        UserEngage.pushProductEvent(eventDetails);
    },

    /**
     * event: checkout
     *
     * @param object eventDetails
     */
    checkout: function (eventDetails) {
        eventDetails.event_type = 'checkout';
        UserEngage.pushProductEvent(eventDetails);
    },

    /**
     * event: refund
     *
     * @param object eventDetails
     */
    refund: function (eventDetails) {
        eventDetails.event_type = 'refund';
        UserEngage.pushProductEvent(eventDetails);
        eventDetails.event_type = 'return';
        UserEngage.pushProductEvent(eventDetails);
    },

    /**
     * event: purchase
     *
     * @param array products
     */
    purchase: function (products) {
        for (let i in products) {
            let eventDetails = products[i];
            eventDetails.event_type = 'purchase';
            UserEngage.pushProductEvent(eventDetails);
        }
    },

    /**
     * On cart update
     *
     * @param string event_type
     * @param object product
     */
    _cartUpdate: function (event_type, product) {
        if (product === null || event_type === null) {
            return;
        }

        // prepare product details
        let eventDetails = {
            'event_type': event_type,
            'product_id': product.id_product + '-' + product.id_product_attribute,
            'name': product.name,
            'price': product.price_wt,
            'quantity': product.cart_quantity,
            'category_name': product.category,
            'product_url': product.url
        };

        if (product.cover !== null && product.cover.medium !== null) {
            eventDetails.image_url = product.cover.medium.url;
        }

        // append product attributes
        for (let i in product.attributes) {
            eventDetails[i] = product.attributes[i];
        }

        // update shopping cart
        if (typeof prestashop.cart === 'undefined') {
            UserEngage.shopping_cart = [];
        } else {
            UserEngage.shopping_cart = prestashop.cart.products;
        }

        // push event
        UserEngage.pushProductEvent(eventDetails);
    },

    _productView: function (eventType) {
        if (typeof eventType === 'undefined' || !eventType) {
            eventType = 'view';
        }

        let productContainer = $('#main[itemtype="https://schema.org/Product"]');

        // prepare product details
        let eventDetails = {
            'event_type': eventType
        };

        // append product id
        let productId = parseInt($('body').attr('class')
            .replace(/.*product\-id\-([0-9]+).*/, '$1'));
        if (!isNaN(productId)) {
            eventDetails.product_id = productId;

            // get product attribute id
            $.ajax({
                dataType: 'json',
                url: document.location.href,
                data: 'ajax=1&action=refresh&' + $('#add-to-cart-or-refresh').serialize()
            }).success(function (resp) {

                eventDetails.product_id += '-' + resp.id_product_attribute;

                // append product name
                if (productContainer.find('[itemprop="name"]').length) {
                    eventDetails.name = productContainer.find('[itemprop="name"]').text().trim();
                }

                // append product price
                if (productContainer.find('[itemprop="price"]').length) {
                    eventDetails.price = productContainer.find('[itemprop="price"]')
                        .attr('content').trim().replace(/([^0-9\.\,]+)/, '').replace(',', '.');
                }

                // append product category
                if ($('.breadcrumb a').length) {
                    eventDetails.category_name = $('.breadcrumb a').last()
                        .closest('li').prev('li').find('a').text().trim()
                        .toLowerCase().replace(' ', '-');
                }

                // append image url
                if (productContainer.find('[itemprop="image"]').length) {
                    eventDetails.image_url = productContainer
                        .find('[itemprop="image"]').attr('src').trim();
                }

                // append product url
                if (productContainer.find('[itemprop="url"]').length) {
                    eventDetails.product_url = productContainer
                        .find('[itemprop="url"]').attr('content').trim();
                }

                productContainer.find('.product-variants-item').each(function () {
                    if ($(this).find('select').length) {
                        let key = $(this).find('.control-label').text().trim();
                        let value = $(this).find('select option:selected').text().trim();
                        eventDetails[key] = value;
                    } else if ($(this).find('input[type=radio]').length) {
                        let key = $(this).find('.control-label').text().trim();
                        let value = $(this).find('input[type=radio]:checked')
                            .next('span').text().trim();
                        eventDetails[key] = value;
                    }
                });

                // push event
                UserEngage.pushProductEvent(eventDetails);
            });
        }
    }
};

/**
 * Measuring Refunds
 */

UserEngage.watchForRefundSubmit = function () {
    if ($('body').attr('id') === 'order-detail' &&
        typeof UserEngage.data.orderProducts !== 'undefined') {

        UserEngage.data.catchedSubmit = false;

        $(document).on('submit', '#order-return-form', function (e) {

            if (UserEngage.data.catchedSubmit) {
                return;
            }

            e.preventDefault();

            $('#order-products tbody input[type="checkbox"]:checked').each(function () {
                let idProductDetails = parseInt($(this).val());
                if (typeof UserEngage.data.orderProducts[idProductDetails] === 'undefined') {
                    return;
                }

                let productDetails = UserEngage.data.orderProducts[idProductDetails];
                productDetails.quantity = parseInt(
                    $(this).closest('tr').find('select').eq(0).val()
                );

                UserEngage.event.product.refund(productDetails);
            });

            UserEngage.data.catchedSubmit = true;

            setTimeout(function () {
                $('#order-return-form [name="submitReturnMerchandise"]').trigger('click');
            }, 200);

            return false;

        });
    }
};

/**
 * End of Measuring Refunds
 */

$(function () {

    // save current shopping cart details
    if (typeof prestashop.cart !== 'undefined' &&
        typeof prestashop.cart.products !== 'undefined') {
        UserEngage.shopping_cart = prestashop.cart.products;
    }

    /**
     * Measuring Additions to a Shopping Cart
     */

    prestashop.addListener('updateCart', function (data) {
        console.log(data);

        let product = null;

        if (typeof data.resp === 'undefined' ||
            typeof data.resp.success === 'undefined' ||
            !data.resp.success) {

            // delete product from cart
            if (typeof data.reason !== 'undefined' &&
                typeof data.reason.idProduct !== 'undefined' &&
                typeof data.reason.linkAction !== 'undefined' &&
                data.reason.linkAction === 'delete-from-cart') {

                let productId = data.reason.idProduct;
                let productAttributeId = data.reason.idProductAttribute;

                let product = null;
                for (let y in UserEngage.shopping_cart) {
                    let cartProduct = $.extend(true, {}, UserEngage.shopping_cart[y]);

                    if (parseInt(productId) === parseInt(cartProduct.id_product) &&
                        parseInt(productAttributeId) === parseInt(cartProduct.id_product_attribute)) {
                        product = cartProduct;
                        break;
                    }
                }

                if (product !== null) {
                    UserEngage.event.product.remove(product);
                }

            } else
            // change quantity of the product
            if (typeof data.reason !== 'undefined' &&
                ((typeof data.reason.productId !== 'undefined' &&
                    data.reason.productId) ||
                    (typeof data.reason.id_product !== 'undefined' &&
                        data.reason.id_product))) {

                let productId = 0;
                if (typeof data.reason.productId !== 'undefined') {
                    productId = data.reason.productId;
                } else {
                    productId = data.reason.id_product;
                }

                $.ajax({
                    dataType: 'json',
                    url: $('.js-cart').data('refresh-url').replace('refresh', 'update')
                }).success(function (resp) {
                    prestashop.cart = resp.cart;

                    // find product with changed quantity
                    for (let x in UserEngage.shopping_cart) {
                        let tempProduct1 = $.extend(true, {}, UserEngage.shopping_cart[x]);

                        if (parseInt(productId) !== parseInt(tempProduct1.id_product)) {
                            continue;
                        }

                        let product = null;

                        for (let y in prestashop.cart.products) {
                            let tempProduct2 = $.extend(true, {}, prestashop.cart.products[y]);

                            if (parseInt(productId) === parseInt(tempProduct2.id_product) &&
                                parseInt(tempProduct1.id_product_attribute) === parseInt(tempProduct2.id_product_attribute) &&
                                parseInt(tempProduct1.cart_quantity) !== parseInt(tempProduct2.cart_quantity)) {
                                product = tempProduct2;
                                break;
                            }
                        }

                        if (product !== null) {
                            if (tempProduct1.cart_quantity < product.cart_quantity) {
                                product.cart_quantity = Math.abs(
                                    parseInt(tempProduct1.cart_quantity) - parseInt(product.cart_quantity)
                                );
                                UserEngage.event.product.addToCart(product);
                            } else {
                                product.cart_quantity = Math.abs(
                                    parseInt(tempProduct1.cart_quantity) - parseInt(product.cart_quantity)
                                );
                                UserEngage.event.product.remove(product);
                            }
                        } else {
                            // product fully removed from shopping cart
                            UserEngage.event.product.remove(tempProduct1);
                        }
                    }
                });
            }

            return;
        }

        // find product in the shopping cart
        for (let i in data.resp.cart.products) {
            if (parseInt(data.resp.cart.products[i].id_product) === parseInt(data.resp.id_product) &&
                parseInt(data.resp.cart.products[i].id_product_attribute) === parseInt(data.resp.id_product_attribute)) {
                product = data.resp.cart.products[i];
                break;
            }
        }

        // find product in the shopping cart cache
        let productCache = null;
        for (let i in UserEngage.shopping_cart) {
            if (parseInt(UserEngage.shopping_cart.id_product) === parseInt(data.resp.id_product) &&
                parseInt(UserEngage.shopping_cart.id_product_attribute) === parseInt(data.resp.id_product_attribute)) {
                productCache = data.resp.cart.products[i];
                break;
            }
        }

        if (productCache === null || productCache.cart_quantity < product.cart_quantity) {
            UserEngage.event.product.addToCart(product);
        } else {
            UserEngage.event.product.remove(product);
        }

    });

    /**
     * End of Measuring Additions to a Shopping Cart
     */

    /**
     * Measuring Product Clicks
     */

    $(document).on('click', '.product-miniature a', function (e) {

        e.preventDefault();

        let productContainer = $(this).closest('[itemtype="http://schema.org/Product"]');

        // give one second for script to push event and redirect
        let productUrl = $(this).attr('href').trim();
        setTimeout(function () {
            document.location = productUrl;
        }, 500);

        // prepare product details
        let eventDetails = {
            'name': productContainer.find('[itemprop="name"]').text().trim(),
            'product_id': productContainer.attr('data-id-product') + '-'
                + productContainer.attr('data-id-product-attribute'),
            'product_url': productUrl,
            'price': productContainer.find('[itemprop="price"]').text().trim()
                .replace(/([^0-9\.\,]+)/g, '').replace(',', '.')
        };

        // append image url
        if (productContainer.find('img').length) {
            eventDetails.image_url = productContainer.find('img').eq(0).attr('src');
        }

        // check if it's promo
        if (productContainer.find('.discount-product').length) {
            UserEngage.event.product.promoClick(eventDetails);
        } else {
            UserEngage.event.product.click(eventDetails);
        }

        return false;
    });

    /**
     * End of Measuring Product Clicks
     */

    /**
     * Measuring Product Views and Product Details
     */

    if ($('body').attr('id') === 'product') {
        UserEngage.event.product.view();

        let pushedEventOnProductDetails = false;
        $(document).on('scroll', function () {

            let productDetailsContainer = $('#product-details').closest('.tabs');

            if (pushedEventOnProductDetails || !productDetailsContainer.is(':visible')) {
                return;
            }

            let docViewTop = $(window).scrollTop();
            let docViewBottom = docViewTop + $(window).height();
            let elemTop = productDetailsContainer.offset().top;
            if (((elemTop <= docViewBottom) && (elemTop >= docViewTop))) {
                UserEngage.event.product.detail();
                pushedEventOnProductDetails = true;
            }
        });

        prestashop.on('updatedProduct', function (param) {
            UserEngage.event.product.view();
        });
    }

    /**
     * End of Measuring Product Views and Product Details
     */

    /**
     * Measuring Checkout
     */

    if ($('body').attr('id') === 'cart') {
        for (let i in prestashop.cart.products) {
            let p = prestashop.cart.products[i];

            // prepare product details
            let eventDetails = {
                'name': p.name,
                'product_id': p.id_product + '-' + p.id_product_attribute,
                'price': p.price_wt,
                'product_url': p.url,
                'category_name': p.category,
                'quantity': p.cart_quantity
            };

            if (p.cover !== null && p.cover.medium !== null) {
                eventDetails.image_url = p.cover.medium.url;
            }

            // append product attributes
            for (let i in p.attributes) {
                eventDetails[i] = p.attributes[i];
            }

            UserEngage.event.product.checkout(eventDetails);

        }
    }

    /**
     * End of Measuring Checkout
     */

    /**
     * Measuring Order steps
     */

    if ($('body').attr('id') === 'checkout') {

        let checkoutStep = 0;

        switch ($('.js-current-step').attr('id')) {
            case 'checkout-personal-information-step':
                checkoutStep = 1;
                break;
            case 'checkout-addresses-step':
                checkoutStep = 2;
                break;
            case 'checkout-delivery-step':
                checkoutStep = 3;
                break;
            case 'checkout-payment-step':
                checkoutStep = 4;
                break;
        }

        UserEngage.event.order(checkoutStep);
    }

    /**
     * End of Measuring Order steps
     */

    /**
     * Measuring Newsletter subscriptions
     */

    UserEngage.watchForNewsletterSubmit = function (newsletterForm) {
        let newsletterFormSent = false;

        newsletterForm.on('submit', function (e) {
            if (newsletterFormSent) return;
            newsletterFormSent = true;

            e.preventDefault();

            setTimeout(function () {
                newsletterForm.trigger('submit');
            }, 500);

            if (!newsletterForm.find('[name="newsletter"][type="checkbox"]').length ||
                newsletterForm.find('[name="newsletter"][type="checkbox"]').eq(0).is(':checked')) {
                let email = newsletterForm.find('[name="email"]').eq(0).val();
                UserEngage.event.newsletter(email, document.location.href);
            }

            return false;
        });
    };

    // footer
    if ($('[name="submitNewsletter"]').length) {
        UserEngage.watchForNewsletterSubmit(
            $('[name="submitNewsletter"]').closest('form')
        );
    }
    // registration
    // if ($('[name="newsletter"][type="checkbox"]').length) {
    //     UserEngage.watchForNewsletterSubmit(
    //         $('[name="newsletter"][type="checkbox"]').closest('form')
    //     );
    // }

    /**
     * End of Measuring Newsletter subscriptions
     */

    /**
     * Measuring Checkout options
     */

    UserEngage.watchForDeliveryMessageSubmit = function (form) {
        let formSent = false;

        form.on('submit', function (e) {
            if (formSent) return;
            formSent = true;

            e.preventDefault();

            setTimeout(function () {
                form.find('[type="submit"]').trigger('click');
            }, 500);

            let msg = form.find('#delivery_message').eq(0).val().trim();
            if (msg.length) {
                UserEngage.event.checkoutOption({
                    'additional_information': msg
                });
            }

            return false;
        });
    };

    // delivery message
    if ($('#delivery_message').length) {
        UserEngage.watchForDeliveryMessageSubmit($('#delivery_message').closest('form'));
    }

    /**
     * End of Measuring Checkout options
     */

    /**
     * Measuring Liking
     */

    if ($('body').attr('id') === 'product') {
        $(document).on('click', '.social-sharing a', function () {
            UserEngage.event.product.liking();
        });
    }

    /**
     * End of Measuring Liking
     */

});