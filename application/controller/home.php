<?php

/**
 * Class Home
 * Default starts
 */
class Home extends Controller
{
    /**
     * PAGE: index
     * site.com/home/index (also as the default page)
     */
    public function index()
    {
        //sets The title
        $pageTitle = "main page";
        // load views
        require APP . 'view/header.php';//head + part of body
        require APP . 'view/alert.php';//alert block
        require APP . 'view/home/index.php';//contents: create and admin manage tasks
        require APP . 'view/ajax/taskframe.php';//frame for tasklist
        require APP . 'view/footer.php';//end of body + footer
    }

}