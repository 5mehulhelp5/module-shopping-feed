var config = {
    map: {
        '*': {
            "mageosShoppingFeedCategoryTaxonomy": 'MageOS_ShoppingFeed/js/category-taxonomy',
            "mageosShoppingFeedForm": 'MageOS_ShoppingFeed/js/feed-form'
        }
    },
    shim: {
        'domReady': {
            deps: ['jquery']
        }
    },
    deps: [
        'MageOS_ShoppingFeed/js/category-taxonomy',
        'MageOS_ShoppingFeed/js/feed-form'
    ]
};
