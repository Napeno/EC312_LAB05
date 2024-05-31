jQuery(document).ready(function($) {
    $('.odoo-product').on('click', function() {
        var name = $(this).data('name');
        var price = $(this).data('price');
        var category = $(this).data('category');
        var imageUrl = $(this).data('image-url');
        
        $('#modal-product-name').text(name);
        $('#modal-product-price').text('Price: ' + price);
        $('#modal-product-category').text('Category: ' + category);
        $('#modal-product-image').attr('src', imageUrl);
        
        $('#odoo-product-modal').show();
    });

    $('.odoo-close').on('click', function() {
        $('#odoo-product-modal').hide();
    });

    $(window).on('click', function(event) {
        if ($(event.target).is('#odoo-product-modal')) {
            $('#odoo-product-modal').hide();
        }
    });
});
