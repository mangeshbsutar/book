<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Abstract classes for all the constants.
 *
 * Developed By : Mangesh Pegasus.
 * Developed Date : 11-07-2023.
 */

abstract class ErrorResponse
{
    const CREATED                   = 201;
    const OK                        = 200;
    const BAD_REQUEST               = 400;
    const NO_CONTENT                = 204;
    const NOT_FOUND                 = 404;
}