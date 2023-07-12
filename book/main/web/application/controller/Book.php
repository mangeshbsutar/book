<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * This controller class performs Books related operations
 *
 * @category Books.
 * @package  Controller
 * @author   Mangesh Sutar <sutarbmangesh@gmail.com>
 * @license  https://www.fancode.com/   Fancode
 * @link     https://www.fancode.com/Books
 * Developed Date : 11-07-2023.
 */
class Book extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        /* Loading model. */
        /* Common methods to communicate with DB. */
        $this->load->model('CommonMethods');

        /* Performing Cart operations i.e add/update/delete cart item. */
        $this->load->model('Cart', 'cart');

        /* Loading libraries. */
        $this->load->library('session');

        /* Loading helper. */
        $this->load->helper('url');
   }

    /**
     * Default index function to view the list of books
     *
     * @return the list of books
     */
    public function index()
    {
        $page = !empty(trim($this->input->post('page'))) ? $this->input->post('page') : 0;
        $getAllList = true;

        /* Retrive the book details using friendly url. */
        $friendlyUrl = !empty($this->uri->segment(2)) ? trim($this->uri->segment(2)) : '';
        $sortProducts = !empty(trim($this->input->post('sortProducts'))) ? $this->input->post('sortProducts') : '';
        $detailsFetchList = '';

        /* View detail information about the book. */
        if (!empty($friendlyUrl)) {
            $view = 'bookDetails';
            $whereClause = "b.FRIENDLYURL = '".$friendlyUrl."'";
            $getAllList = false;
            $detailsFetchList = ' , b.DESCRIPTION, b.SELLINGPOINTS';
        } else {
            $limit = 50; /* We can make this contant to use in whole application. */

            /* Display list of all books. */
            if (!empty($page)) {
                //TODO Pagination code here.
            } else {
                $view = 'allBooks';
            }
        }

        $this->load->view(
            'book/'.$view,
            array(
                'data' => $this->CommonMethods->getAll(
                    'b_mst_books b',
                    'b.ID, b.TITLE, b.AUTHORFIRSTNAME, b.AUTHORLASTNAME, b.PRICE, s.QTYAVAIL'.$detailsFetchList,
                    array(
                        'where' => array(
                            'b.ACTIVE' => 1
                        ),
                        'join' => array(
                            'b_dat_stock s' => array(
                                'condition' => 's.BID = b.ID'
                            )
                        ),
                        'whereString' => $whereClause,
                        'orderBy' => 'b.TITLE'
                    ),
                    $getAllList
                )
            )
        );
    }

    /**
     * Search book(s) as per provided keyword(s) by the user.
     *
     * @var string $keyword keyword to be search.
     *
     * @return array contains response data.
     */
    public function search()
    {
        $searchString = trim($this->input->post('searchString'));

        /* Redirects user to the home page if empty search string. */
        /* Empty condtion will check before submitting the search request i.e using jquery validations. */
        if (empty($searchString)) {

            redirect('book');
            die();
        }

        $this->load->view(
            'book/searchData',
            array(
                'data' => $this->CommonMethods->getAll(
                    'b_mst_books b',
                    'b.ID, b.TITLE, b.AUTHORFIRSTNAME, b.AUTHORLASTNAME, b.PRICE, s.QTYAVAIL',
                    array(
                        'join' => array(
                            'b_dat_stock s' => array(
                                'condition' => 's.BID = b.ID'
                            )
                        ),
                        'where' => array(
                            'b.ACTIVE' => 1
                        ),
                        'whereString' => 'LOWER(b.TITLE) LIKE "%'.trim($searchString).'%" OR LOWER(b.AUTHORFIRSTNAME) LIKE "%'.trim($searchString).'%" OR LOWER(b.AUTHORLASTNAME) LIKE "%'.trim($searchString).'%"',
                        'orderBy' => 'b.TITLE'
                    ),
                    false
                )
            )
        );
    }
}
