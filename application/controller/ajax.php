<?php

/**
 * All ajax calls from clients to server
 */
class Ajax extends Controller
{
    /**
     * Ajax constructor.
     */
    function __construct() {
        parent::__construct();
        //stops if we work not in ajax request
        if( !isset($_SERVER['HTTP_X_REQUESTED_WITH']) or
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest' )
        {
            header('location: ' . URL);//redirect to home
            die;// exit; echo "all system stop";
        }


    }

    /**
     * for all users
     * site.com/ajax/index (also as the default page)
     */
    public function index()//safely reads all task
    {

        try {
            //reads all + getting amount of tasks
            $tasks = $this->model->getAllAllowedTasks();

            //$amounts = $this->model->getAmountOfTasks();
            foreach($tasks as $task) {
                    echo "
                <tr>
                    <td class=\"shortename\"><div data-toggle=\"tooltip\" data-placement=\"top\" data-html=\"true\" 
                    title=\" <img class='intooltip' src='" . URL_UPLOAD . "/" . $task->image . "'/> \" >
                            {$task->image}<sup>*</sup>
                        </div>
                    </td>
                    <td>
                        <div>{$task->name}</div>
                    </td>
                    <td >
                        <div>{$task->description}</div>
                    </td>
                    <td>
                        <div>".strftime("%d %b, %Y", strtotime($task->created_at)) ."</div>
                    </td>
                </tr>";
            }
        }
        catch (PDOException $ex) {
            echo "An error occured " . $ex->getMessage();
        }
    }

    /**
     * for admin
     * site.com/ajax/indexadmin (also as the default page)
     */
    public function indexadmin()
    {
        // if POSTED and not logged earlier
        if( isset($_POST['login']) && isset($_POST['password']) ) {

            // if correct POSTED
            if (!$_POST['login'] == NULL && !$_POST['password'] == NULL) {

                $token = $this->model->findAdmin( htmlentities(trim($_POST['login'])), md5($_POST['password']) );

                if($token){
                    setcookie("admintoken", $token, time()+3600, '/', null, null, false );
                    // settting cookie without httponly(7val=false) for allow manage from js!

                    //return a successful  text and form
                    echo "<!-- successful process login and password -->";
                    require APP . 'view/ajax/taskframeadmin.php';//frame for tasklist
                    exit;

                }else{ echo "Ckeck both user AND password !!!"; exit;}

                //not found - die
            }else{exit;}

            // if user has 'admintoken' 
        }elseif( isset($_COOKIE['admintoken']) && strlen($_COOKIE['admintoken']) > 1){

            $result = $this->model->findAdminToken(htmlentities(trim($_COOKIE['admintoken'])) );
            if($result === false){
                echo "smth notgood. token nod founded<br />\n";
                echo "<a href='#' onclick='logout();'>Click here to AUTH again!!!</a>";
                exit;
            }

        }else{
            //still nothing good detected, exit with alers
            echo "Nothin good in your request, please AUTHENTICATE !";
            exit;
        }

        try {
            //reads all + getting amount of tasks
            $tasks = $this->model->getAllTasks();
            //$amounts = $this->model->getAmountOfTasks();
            echo "\n<!-- successful -->";//access granted, display in #admin

            foreach($tasks as $task) {

                $creation_date = strftime("%d %b, %Y", strtotime($task->created_at));
                $id = $task->id;
                $checked = ($task->status == 1 ) ? ' checked="checked" ' : '';

                echo "
                <tr>
                     <td title='Click to edit' class='shortename'>
                       <div class='editable' onclick='makeElementEditable(this)'
                       onblur=\"updateTask(this, 'image', '{$id}')\">
                        {$task->image}
                       </div>
                        <div data-toggle=\"tooltip\" data-placement=\"top\" data-html=\"true\" 
                            title=\" <img class='intooltip' src='" . URL_UPLOAD . "/" . $task->image . "'/> \">
                            <tt>hover over to see image<sup>*</sup></tt>
                        </div>
                     </td>
                     
                     <td title='Click to edit'>
                       <div class='editable' onclick='makeElementEditable(this)'
                       onblur=\"updateTask(this, 'name', '{$id}')\">{$task->name}</div>
                     </td>
            
                     <td title='Click to edit'>
                       <div class='editable' onclick='makeElementEditable(this)'
                       onblur=\"updateTask(this, 'email', '{$id}')\">{$task->email}</div>
                     </td>
            
                     <td title='Click to edit'>
                       <div class='editable' onclick='makeElementEditable(this)'
                       onblur=\"updateTask(this, 'description', '{$id}')\">{$task->description}</div>
                     </td>
            
                     <td>{$creation_date}</td>
            
                     <td title='If changed it will be updated automatically'>
                       <input type=\"checkbox\" 
                       onChange=\"updateTask(this, 'status', '{$id}')\" {$checked} />
                     </td>
            
                     <td style=\"width: 5%;\">
                        <button class='btn-danger' onclick=\"deleteTask('{$id}')\">
                          <i class='fa fa-times'></i>
                        </button>
                     </td>
                </tr>";

            }

        }
        catch (PDOException $ex) {
            echo "An error occured " . $ex->getMessage();
        }

    }

    /**
     * site.com/ajax/create
     */
    public function create()
    {
        // if POSTED
        if( isset($_POST['name']) &&
            isset($_POST['email']) &&
            isset($_POST['description']) &&
            isset($_FILES['image']) ){

            // if correct POSTED
            if(!$_POST['name'] == NULL &&
                !$_POST['email'] == NULL &&
                !$_POST['description'] == NULL &&
                count($_FILES['image']) > 0 ){

                //POST
                $name = str_replace(">", "", str_replace("<", "", $_POST['name']));
                $email = str_replace(">", "", str_replace("<", "", $_POST['email']));
                $description = str_replace(">", "", str_replace("<", "", $_POST['description']));
                
                //FILE
                $uploadDir = ROOT.UPLOAD_FOLDER . DIRECTORY_SEPARATOR;//from config
                $fileName = $_FILES['image']['name'];
                $uploadFile = $uploadDir . basename($fileName);
                // convert file, and rename to "md5(now)_{$fileName}'
                //if same user will add a new task with same image
                $newFileName = md5(time()) . "_" . $fileName;
                $newFile = $uploadDir . $newFileName;

                try{

                    move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile);

                    require APP . 'lib/simpleImage.php';//load class for work with image
                    $image = new SimpleImage();
                    $image
                        ->fromFile($uploadFile)//process with old image
                        ->bestFit(320, 240)
                        ->toFile($newFile); // output to the file
                    unlink($uploadFile);//remove old big file

                    //insert row in db
                    $res = $this->model->addTask($newFileName, $name, $email, $description);
                    
                    echo $res;
                    
                } catch (PDOException $ex){
                    echo "An error occured " .$ex->getMessage();
                }
            } else {
                echo "Please fill in the whole form!";
            }
        }else{
            echo "Please fill in the form!";
        }

    }

    /**
     * site.com/ajax/update
     */
    public function update()
    {
        //allowed for admin. no token, no data
        if(!isset($_COOKIE['admintoken'])){
            echo "<a href='#' onclick='logout();'>Click here to AUTH again!!!</a>";
            exit;
        }
        //safely check if user has 'admintoken' an its==tokenDB
        if( false == $this->model->findAdminToken(htmlentities(trim($_COOKIE['admintoken'])) )
        ){
            echo "smth not good. Please refresh page and authenticate!";
            exit;//or  $this->indexadmin();
        }

        //check for legal char in POSTed data
        // fields - only words
        // ids - only digits
        // update - new content, or empty when field=='status'
        if( isset($_POST['field']) && preg_match("/\w/i", $_POST['field']) &&
            isset($_POST['id']) && preg_match("/\d/i", $_POST['id'])
                //allowed only for status
        ){

            echo "Updating: <br />\n";

            $key = $_POST['field'];
            $id = $_POST['id'];
            $change = str_replace(">", "", str_replace("<", "", $_POST['update']));

            //use case on $_POST['field']
            switch ($key) {
                case 'image':
                case 'name':
                case 'email':
                case 'description':
                case 'status':

                    //update and return rez =)
                    echo $this->model->updateTask($id, $key, $change);

                    break;
                default:
                    echo "Field - error.";
            }

        }else{
            echo "go away hacker!";
        }

    }

    /**
     * site.com/ajax/delete
     */
    public function delete()
    {

        //allowed for admin. no token, no data
        if(!isset($_COOKIE['admintoken'])){
            echo "<a href='#' onclick='logout();'>Click here to AUTH again!!!</a>";
            exit;
        }
        //safely check if user has 'admintoken' an its==tokenDB
        if( false == $this->model->findAdminToken(htmlentities(trim($_COOKIE['admintoken'])) )
        ){
            echo "smth not good. Please refresh page and authenticate!";
            exit;//or  $this->indexadmin();
        }

        if( isset($_POST['id']) && preg_match("/\d/i", $_POST['id']) ) {

            $id = $_POST['id'];
            //deleting one =)
            $this->model->deleteTask($id);
            //+unlink image
        }
    }

    /**
     * PAGE: site.com/ajax/adminform
     */
    public function adminform()
    {
        // load views
        require APP . 'view/ajax/adminform.php';//frame for managin tasks
    }

}
