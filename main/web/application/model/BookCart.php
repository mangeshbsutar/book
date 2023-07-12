<?php
/**
 * This model class performs shopping cart operations.
 *
 * @category Book.
 * @package  Model.
 * @author   Mangesh Sutar <sutarbmangesh@gmail.com>
 * @license  https://www.fancode.com/   Fancode
 * @link     https://www.fancode.com/Books
 * Developed Date : 12-07-2023.
 */
class Cart extends CI_Model {

    /**
     * @var Array
     */
    private $cartItems;

    /**
     * Cart constructor.
     *
     */
    public function __construct() {
        /* Loading model. */
        $this->load->model('CommonMethods');

        /* Loading helper. */
        $this->load->helper('cookie');

        /* Assigning logged in user details. */
        $this->session = isLoggedIn();
        $this->cartItems  = $this->getCartItems();
    }

    /**
     * Retrieve the sales details
     *
     * @return Array contains sales details for logged in / guest user
     */
    public function getSalesDetails($uid)
    {
        /* Fetch sales history details for logged in or guest user */
        return $this->CommonMethods->getAll(
            'b_sales_history',
            'ID',
            array(
                'where' => array(
                    'UID' => $uid,
                    'STATUS' => array('NEW', 'CHECKEDOUT')
                )
            )
        );
    }

    /**
     * @return Array contains all cart items
     */
    public function getCartItems($salesId='')
    {
        $items = [];

        /* Fetch sales history details for logged in or guest user */
        if (empty($salesId)) {
            $salesId = $this->getSalesDetails($this->session->UID);
        }

        /* Fetch cart items for logged in or guest user */
        if (!empty($salesId)) {
            $items = $this->CommonMethods->getAll(
                'b_sales_items i',
                'i.ID, i.SID, i.BID, i.QTY, b.TITLE',
                array(
                    'where' => array(
                        'i.SID' => $salesId->ID
                    ),
                    'join' => array(
                        'b_mst_books b' => array(
                            'condition' => 'b.ID = i.BID'
                        )
                    )
                ),
                false
            );
        }

        return $items;
    }

    /**
     * This function adds a cart item or updates qty if item exist.
     *
     * @param array $book book to be added/updated.
     * @param int $quantity qauntity to be added/updated.
     *
     * @return boolean True / False
     */
    public function addItem($book, $quantity)
    {
        /* Fetch sales history details for logged in or guest user */
        $salesId = $this->getSalesDetails($this->session->UID);
        $addNewItem = 1;
        $success = false;

        /* check for existing item if any */
        if (!empty($salesId)) {
            $existingItem = $this->CommonMethods->getAll(
                'b_sales_items',
                'ID, SID, BID',
                array(
                    'where' => array(
                        'SID' => $salesId->ID,
                        'BID' => $book->ID
                    )
                ),
                true
            );

            if (!empty($existingItem)) {
                $addNewItem = 0;

                /* Update only QTY for the exiting item. */
                $success = $this->CommonMethods->update(
                    'b_sales_items',
                    array(
                        'QTY' => $existingItem->QTY + $quantity
                    ),
                    array(
                        'ID' => $existingItem->ID
                    )
                );
            }
        }

        /* Add new item to the cart. */
        if (!empty($addNewItem) && $addNewItem == 1) {

            /* Create a new cart in sales history table. */
            if (empty($salesId)) {

                $salesId = $this->CommonMethods->insert(
                    'b_sales_history',
                    array(
                        'STATUS' => 'NEW'
                    )
                );

                if (!empty($salesId)) {
                    $success = $this->CommonMethods->insert(
                        'b_sales_items',
                        array(
                            'SID' => $salesId,
                            'BID' => $book->ID,
                            'PRICE' => $book->PRICE,
                            'QTY' => $quantity
                        )
                    );
                }
            }
        }

        if ($success) {

            /* Update sales total. */
            $this->updateSalesTotal($salesId);
        }

        return $success;
    }

    /**
     * This function updates a cart item.
     *
     * @param int $itemId item id to be updated.
     * @param int $quantity qauntity to be updated.
     *
     * @return boolean True / False
     */
    public function updateCart($itemId, $quantity)
    {
        $success = false;

        /* Retrieve existing item details. */
        $existingItem = $this->CommonMethods->getAll(
            'b_sales_items',
            'ID, SID, BID',
            array(
                'where' => array(
                    'ID' => $itemId
                )
            ),
            true
        );

        if (!empty($existingItem)) {

            /* Update only QTY for the exiting item. */
            $success = $this->CommonMethods->update(
                'b_sales_items',
                array(
                    'QTY' => $existingItem->QTY + $quantity
                ),
                array(
                    'ID' => $existingItem->ID
                )
            );
        }

        /* Update sales total. */
        if (!empty($success)) {
            $this->updateSalesTotal($salesId);
        }

        return $success;
    }

    /**
     *  Delete a cart item from the Cart.
     *
     * @param array $cartsIds cart multiple/single item ids to be deleted.
     * @Method({"POST"})
     *
     * @return boolean true/false.
     */
    public function deleteCartItem($itemIds=[], $clearCart='')
    {
        $success = false;

        if (!empty($itemIds)) {
            $whereClause['ID'] = $itemIds;
        }

        if (!empty($clearCart) && $clearCart == 1) {
            $salesId = $this->getSalesDetails($this->session->UID);

            if (!empty($salesId)) {
                $whereClause['SID'] = $salesId->ID;
            }
        }

        if (!empty($whereClause)) {
            if ($this->commonFunction->delete(
                    'b_sales_items',
                    !empty($whereClause) ? $whereClause : []
                )
            ) {
                $success = true;
            }
        }

        return $success;
    }

    /**
     * Update sales total
     *
     * @param int $salesId sales id to be updated.
     * */
    public function updateSalesTotal($salesId) {
        $success = false;

        if (!empty($salesId)) {
            $salesItems = getCartItems($salesId);

            if (!empty($salesItems)) {
                $total = 0;

                foreach ($salesItems as $salesItem) {
                    $total += ($salesItem->QTY * $salesItem->PRICE);
                }

                /* Here we can do some other steps like delivery fee, handling charges, tax etc. */

                $success = $this->CommonMethods->update(
                    'b_sales_history',
                    array(
                        'CARTTOTAL' => $total
                    ),
                    array(
                        'ID' => $salesId
                    )
                );
            }
        }

        return $success;
    }

    /**
     * This function checks the cart is emty or not
     *
     * @return bool True / False
     */
    public function isEmpty()
    {
        return count($this->cartItems) == 0;
    }

    /**
     * This function places the manual eft order
     *
     * @return bool True / False
     */
    public function placeEFTOrder($sid) {
        $success = false;

        if (!empty($sid)) {
            $success = $this->CommonMethods->update(
                'b_sales_history',
                array(
                    'STATUS' => 'REVIEW'
                ),
                array(
                    'ID' => $salesId
                )
            );
        }

        return $success;
    }
}
