<?php

/**
 * 404 page gen
 */
class NotFound extends Controller
{
    /**
     * PAGE: index
     * This method handles the error page that will be shown when a page is not found
     */
    public function index()
    {
        $pageTitle = "404 - not founded page or request.";
        // load views
        require APP . 'view/header.php';
        require APP . 'view/404.php';
        require APP . 'view/footer.php';
    }
}
