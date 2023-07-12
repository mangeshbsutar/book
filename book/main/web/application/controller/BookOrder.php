<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * This controller class performs Shopping related operations
 *
 * @category Books.
 * @package  Order Controller
 * @author   Mangesh Sutar <sutarbmangesh@gmail.com>
 * @license  https://www.fancode.com/   Fancode
 * @link     https://www.fancode.com/Books
 * Developed Date : 12-07-2023.
 */
class BookOrder extends CI_Controller
{
	/**
     * Default index function to view all orders
     * 
     * $param string orderId used to display order details 
     * {{$GET}}
     *
     * @return the product home page
     */
    public function index()
    {
		/* List of orders or detail view of order */
	}
	
	/**
     * Process CC payment 
     * 
     * $param array Payment gateway response
     * 
     * @return boolen true / false
     */
    public function processCCOrder()
    {
		/* Process CC payments using pyment gateway response. */
		
		/* Send notifications to the user. */
	}
	
	/**
     * Process instant EFT payment 
     * 
     * $param array Payment gateway response
     * 
     * @return boolen true / false
     */
    public function processIntantEFTOrder()
    {
		/* Process Instant EFT payments using pyment gateway response. */
		
		/* Send notifications to the user. */
	}
}
