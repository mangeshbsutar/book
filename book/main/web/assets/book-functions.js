$(document).ready(function() {

    /* Ajax call on add to cart button. */
    $(document.body).on('click', '.add-to-cart', function(event) {
        event.stopPropagation();
        event.stopImmediatePropagation();

        var selectedElement = $(this);
        var bookId = selectedElement.attr('data-id');
        var bookName = selectedElement.attr("data-name");

        var quantity = $.trim($('#spinner-' + productId).val());

        if (quantity <= 0) {

            swal('Sorry', 'Please add atleast 1 quantity', 'warning');
            return false;
        }

        $.ajax({
            url : "/BookShoppingCart/addItem",
            type    :   "POST",
            data    :   {"bookId":productId, "quantity":quantity},
            success : function(e) {
                if (e.code == 200 && e.success) {
                    /* We can update cart related information like total, cart menu etc. */
                    $(".cart-menu").empty();
                    $(".cart-menu").append(e.cartMenu);
                    $(".cart-total").html(e.total);

                    swal('Done', e.message, 'success');
                } else {
                    swal('Sorry', e.message, 'error');
                }
            }
        });
    });

    /* Ajax call to update a cart item quantity on checkout page */
    $(document.body).on('change', '.update-cart-item', function(e) {
        event.stopPropagation();
        var selectedElement = $(this);
        var quantity = selectedElement.val();
        var bookId = selectedElement.attr('id');
        var itemId = selectedElement.attr('data-item-id');

        if (quantity <= 0) {

            swal('Sorry', 'Please add atleast 1 quantity or remove item from the cart.', 'warning');
            return false;
        }

        $.ajax({
            url : "/BookShoppingCart/updateItem",
            type    :   "POST",
            data    :   {"itemId":itemId, "quantity":quantity},
            success : function(e) {
                if (e.code == 200 && e.success) {
                    /* We can update cart related information like total, cart menu etc. */
                    $(".cart-menu").empty();
                    $(".cart-menu").append(e.cartMenu);
                    $(".cart-total").html(e.total);

                    swal('Done', e.message, 'success');
                } else {
                    swal('Sorry', e.message, 'error');
                }
            }
        });
    });

    function validateCart() {
        var success = true;

        if ($('#del-add-id').val().length == 0) {

            swal('Sorry', 'Please select delivery address.', 'error');
            return false;
        }

        $.ajax({
            url : "/ShoppingCart/validateCart",
            type    :   "POST",
            async: false,
            data : {'total': $('.cart-grand-total-display').text(), 'pType': $("input[name='payment-type']:checked").val()},
            success : function(e) {
                switch (e.type) {
                    case 'checkDeliveryAvailable':
                        /* We can restrict user if delivery/collection addres is missing. */
                        break;
                    case 'outOfStockItems':
                        /* We can restrict user to remove out of stock items and the procedd with the checkout . */
                        break;
                    case 'misMatchSalesTotal':
                        /* If sales total is mis-match then we can ask user to reload the page. */
                        break;
                    case 'checkMaxDiscountLimit':
                        /* We can restrict user if exceeds the maximum dicsount limit. */
                    break;
                    case 'expiredCoupons':
                        /* We can restrict users if any applied coupon or voucher is expired. */
                    break;
                }
            }
        });

        return success;
    }

    /* Proceed to purchase as manual EFT. */
    $('.purchase-manual-eft').on('click', function (event) {
        if (validateCart()) {
            swal({
                title: 'Placing Manual Eft Order',
                text: "Please wait...",
                imageUrl: "loader.gif",
                imageHeight: 70,
                showConfirmButton: false,
                allowOutsideClick: false
            });

            $.ajax({
                url : "/BookShoppingCart/purchaseManualEft",
                type    :   "POST",
                success : function(e) {
                    if (e.code == 200 && e.success) {
                        window.location.href = e.url;
                    } else {
                        swal('Whoops!', e.message, 'error');
                    }
                }
            });
        }

        return false;
    });
});
