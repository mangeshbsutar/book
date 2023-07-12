<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * This controller class performs Shopping related operations
 *
 * @category Books.
 * @package  Controller
 * @author   Mangesh Sutar <sutarbmangesh@gmail.com>
 * @license  https://www.fancode.com/   Fancode
 * @link     https://www.fancode.com/Books
 * Developed Date : 12-07-2023.
 */
class BookShoppingCart extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        /* Loading model. */
        /* Common methods to communicate with DB. */
        $this->load->model('CommonMethods');

        /* Performing Cart operations i.e add/update/delete cart item. */
        $this->load->model('BookCart', 'cart');

        /* Loading libraries. */
        $this->load->library('session');

        /* Loading helper. */
        $this->load->helper('url');
   }

    /**
     *  Add a Book into the Cart.
     *
     * @param int $bookId book id to be added/updated.
     * @param int $quantity quantity to be added/updated.
     * @Method({"POST"})
     *
     * @return json array contains the cart details.
     */
    public function addItem()
    {
        $bookId = trim($this->input->post('bookId'));
        $quantity = trim($this->input->post('quantity'));

        if (empty($bookId) && empty($quantity)) {

            return $this->api_output->sendResult(
                array(
                    'success' => false,
                    'code' => ErrorResponse::BAD_REQUEST
                ),
                'json'
            );
        }

        /* To check if book is available or not. */
        $book = $this->CommonMethods->getAll(
            'b_mst_books b',
            'b.ID, b.PRICE, s.QTYAVAIL',
            array(
                'where' => array(
                    'b.ID' => $bookId,
                    'b.ACTIVE' => 1 /* We can add more clauses like internal or public. */
                ),
                'join' => array(
                    'b_dat_stock s' => array(
                        'condition' => 's.BID = b.ID'
                    )
                )
            )
        );

        /* Check book entry is present or not. */
        if (empty($book)) {

            return $this->api_output->sendResult(
                array(
                    'success' => false,
                    'code' => ErrorResponse::NO_CONTENT
                ),
                'json'
            );
        }

        /* Check out of stock item if any.*/
        if (!empty($book) && empty($book->QTYAVAIL)) {

            return $this->api_output->sendResult(
                array(
                    'success' => false,
                    'code' => ErrorResponse::NO_CONTENT,
                    'code' => 'Out of stock',
                ),
                'json'
            );
        }

        $success = $this->cart->addItem($book, $quantity);
        $responseData['code'] = ErrorResponse::BAD_REQUEST;
        $responseData['sucess'] = $success;

        if ($success) {
            $cartItems = $this->cart->getCartItems();
            $total = 0;

            foreach ($cartItems as $item) {
                $total += ($item->QTY * $item->PRICE);
            }

            $responseData['total'] = $total;

            /* Load the cart menu view to prepare the cart menu. */
            $responseData['cartMenu'] = $this->load->view(
                'book/cartMenu',
                array(
                    'cartIemts' => $cartItems
                ),
                TRUE
            );
            $responseData['code'] = ErrorResponse::OK;

            /* Here we can add audit event for order history. */
        }

        return $this->api_output->sendResult(
            $responseData,
            'json'
        );
    }

    /**
     * Update the cart item details.
     *
     * @param int $cartItemId id to be updated.
     * @param int $quantity quantity to be updated.
     * @Method({"POST"})
     *
     *  @return json array contains the cart details.
     */
    public function updateItem()
    {
        $quantity = trim($this->input->post('quantity'));
        $cartItemId = trim($this->input->post('itemId'));

        if (empty($cartItemId)) {

            return $this->api_output->sendResult(
                array(
                    'success' => false,
                    'code' => ErrorResponse::BAD_REQUEST
                ),
                'json'
            );
        }

        /* To check if cart item is exist or not. */
        $cartItem = $this->CommonMethods->getAll(
            'b_sales_history',
            'ID, QTY',
            array(
                'where' => array(
                    'ID' => $cartItemId
                )
            )
        );

        /* Check cart item entry is exist or not. */
        if (empty($cartItem)) {

            return $this->api_output->sendResult(
                array(
                    'success' => false,
                    'code' => ErrorResponse::NO_CONTENT
                ),
                'json'
            );
        }

        $success = $this->cart->updateCart($cartItemId, $quantity);
        $responseData['code'] = ErrorResponse::INTERNAL_SERVER_ERROR;
        $responseData['success'] = $success;

        if ($success) {
            $cartItems = $this->cart->getCartItems();
            $total = 0;

            foreach ($cartItems as $item) {
                $total += ($item->QTY * $item->PRICE);
            }

            $responseData['total'] = $total;

            /* Load the cart menu view to prepare the cart menu. */
            $responseData['cartMenu'] = $this->load->view(
                'book/cartMenu',
                array(
                    'cartIemts' => $cartItems
                ),
                TRUE
            );
            $responseData['code'] = ErrorResponse::OK;

            /* Here we can add audit event for order history. */
        }

        return $this->api_output->sendResult(
            $responseData,
            'json'
        );
    }

    /**
     * Delete the cart item details.
     * Clear cart if $clearCart = 1
     *
     * @param int $cartItemId id to be updated.
     * @param int $clearCart clear the cart if flag = 1.
     * @Method({"POST"})
     *
     *  @return json array contains the cart details.
     */
    public function deleteItem()
    {
        $clearCart = !empty($this->input->post('clearCart')) ? trim($this->input->post('clearCart')) : 0;
        $cartItemId = !empty($this->input->post('cartItemId')) ? trim($this->input->post('cartItemId')) : '';

        if (empty($cartItemId) && empty($clearCart)) {

            return $this->api_output->sendResult(
                array(
                    'success' => false,
                    'code' => ErrorResponse::BAD_REQUEST
                ),
                'json'
            );
        }

        /* Delete single cart item.
         * To check if cart item is exist or not. */
        if (!empty($cartItemId)) {
            $cartItem = $this->CommonMethods->getAll(
                'b_sales_history',
                'ID, QTY',
                array(
                    'where' => array(
                        'ID' => $cartItemId
                    )
                )
            );

            /* Check cart item entry is exist or not. */
            if (empty($cartItem)) {

                return $this->api_output->sendResult(
                    array(
                        'success' => false,
                        'code' => ErrorResponse::NO_CONTENT
                    ),
                    'json'
                );
            }
        }

        /* Check clear cart request if any. */
        $reloadPage = 0;

        if (!empty($clearCart) && $clearCart == 1) {
            $cartItemId = '';
            $reloadPage = 1;
        }

        $success = $this->cart->deleteCartItem($cartItemId, $clearCart);
        $responseData['code'] = ErrorResponse::INTERNAL_SERVER_ERROR;
        $responseData['success'] = $success;

        if ($success) {
            if (empty($reloadPage)) {
                $cartItems = $this->cart->getCartItems();
                $total = 0;

                foreach ($cartItems as $item) {
                    $total += ($item->QTY * $item->PRICE);
                }

                $responseData['total'] = $total;

                /* Load the cart menu view to prepare the cart menu. */
                $responseData['cartMenu'] = $this->load->view(
                    'book/cartMenu',
                    array(
                        'cartIemts' => $cartItems
                    ),
                    TRUE
                );
            }
            $responseData['reloadPage'] = $reloadPage;
            $responseData['message'] = 'Successfully item removed.';
            $responseData['code'] = ErrorResponse::OK;

            /* Here we can add audit event for order history. */
        }

        return $this->api_output->sendResult(
            $responseData,
            'json'
        );
    }

    /**
     * Performs the manual EFT payment order.
     *
     * @param Request $request
     * @Method({"POST"})
     *
     * @return array
     */
    public function purchaseManualEft()
    {
        $salesHistoryData = $discountData = [];
        $outStandingAmount = 0;

        /* Get the logged in user details. */
        /* Fetch the user details from session using access-token. */
        $loggedInUserDetails = isLoggedIn();

        /* Ask user to login and proceed checkout .*/
        if (empty($loggedInUserDetails)) {

            return $this->api_output->sendResult(
                array(
                    'success' => false,
                    'code' => ErrorResponse::BAD_REQUEST,
                    'message' => 'Please login and proceed with the checkout.'
                ),
                'json'
            );
        }

        /* Check if cart is empty or not. */
        if ($this->cart->isEmpty()) {

            return $this->api_output->sendResult(
                array(
                    'success' => false,
                    'code' => ErrorResponse::NO_CONTENT,
                    'message' => 'Cart is empty, please reload the page.'
                ),
                'json'
            );
        }

        $salesDetails = $this->cart->getSalesDetails($loggedInUserDetails->GUID);

        if (empty($salesDetails)) {

            return $this->api_output->sendResult(
                array(
                    'success' => false,
                    'code' => ErrorResponse::NO_CONTENT,
                    'message' => 'Order details are not found, please refresh the page.'
                ),
                'json'
            );
        }

        $success = $this->cart->placeEFTOrder($salesDetails->ID);
        $responseData['code'] = ErrorResponse::INTERNAL_SERVER_ERROR;
        $responseData['success'] = $success;

        if ($success) {
            $responseData['message'] = 'Successfully placed the manual EFT order.';
            $responseData['code'] = ErrorResponse::OK;

            /* Send order details notifications Email and SMS. */

            /* Here we can add audit event for order history. */
        }

        return $this->api_output->sendResult(
            $responseData,
            'json'
        );
    }

    /**
     * Validate the cart
     *
     * @return boolean true /false.
     */
    public function validateCart()
    {
        $success = false;
        /* Redirect to checkout page. */
        if ($this->cart->isEmpty()) {

             return $this->api_output->sendResult(
                array(
                    'success' => false,
                    'code'=>ErrorResponse::NOT_FOUND,
                    'message' => 'Cart is empty'
                ),
                'json'
            );
        }

        /* Here we can check remaining validations like
         * out of stock items if any.
         * Sales total mis-match if any.
         * invalid/expired coupon applied if any. */

         return $success;
    }
}
