<?php

/**
 * Class ORM
 * @package Model
 */
class Model
{

    /**
     * init $db connection to all methods
     * @param object $db A PDO database connection
     */
    function __construct($db)
    {
        try {
            $this->db = $db;
        } catch (PDOException $e) {
            exit('Database connection could not be established.');
        }
    }

    /**
     * Get all songs from database, only for admin.
     */
    public function getAllTasks()
    {
        $sql = "SELECT * FROM tasks";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    /**
     * Safely Get all songs from database
     */
    public function getAllAllowedTasks()
    {
        $sql = "SELECT `id`,`image`,`name`,`description`,`created_at` 
                      FROM tasks WHERE `status`!=0";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    /**
     * Gets amount of tasks, just for stat.
     */
    public function getAmountOfTasks()
    {
        $sql = "SELECT COUNT(id) AS amounts FROM tasks";
        $query = $this->db->prepare($sql);
        $query->execute();

        // get exactly one result
        return $query->fetch()->amounts;
    }

    /**
     * Method to add a new task
     * @param $imageName
     * @param $userName
     * @param $userEmail
     * @param $description
     * @return string
     */
    public function addTask($imageName, $userName, $userEmail, $description)
    {
        $createQuery = "INSERT INTO tasks(image, name, email, description, created_at, status)
                    VALUES(:image,:name, :email, :desc, now(), 0)";

        $statement = $this->db->prepare($createQuery);

        $statement->execute(array(
            ":image" => $imageName,
            ":name" => $userName,
            ":email" => $userEmail,
            ":desc" => $description
        ));

        if($statement->rowCount() === 1){
            $result = "Success, new record has been inserted";
        }else{
            $result = "Task not added";
        }

        return $result;

    }

    /**
     * Method to update existing task
     * @param $id
     * @param $key
     * @param $change
     * @return string
     */
    public function updateTask($id, $key, $change)
    {
        if($key == 'status'){//just invert if updated status
            $updateQuery = "UPDATE tasks SET `status` = 
             CASE WHEN `status` = 0 THEN '1' ELSE '0' END
             WHERE id = :id";
            $statement = $this->db->prepare($updateQuery);
            $statement->execute(array(":id" => $id));

        }else {
            $updateQuery = "UPDATE tasks SET {$key} = :{$key} WHERE id = :id";
            $statement = $this->db->prepare($updateQuery);
            $statement->execute(array(":{$key}" => $change, ":id" => $id));
        }

        if ($statement->rowCount() === 1) {
            $result = "Success, task's {$key} updated!";
        } else {
            $result = "No changes was made";
        }
        return $result;

    }

    /**
     * Method to delete existing task
     * @param $id
     * @return string
     */
    public function deleteTask($id)
    {
        $deleteQuery = "DELETE FROM tasks WHERE id = :id";

        $statement = $this->db->prepare($deleteQuery);
        $statement->execute(array(":id" => $id));

        if ($statement) {
            $result = "Task deleted successfully";
        } else {
            $result = "No changes was made";
        }
        return $result;

    }


    /**
     * Method to check user-pass
     * if admin finded - generated new admintoken and return it
     * else returned false
     */
    public function findAdmin($login, $cryptedPassword)
    {

        $searchQuery = "SELECT * FROM users 
                  WHERE `login`= :login AND `password`= :password";

        $statement = $this->db->prepare($searchQuery);

        $statement->execute(array(
            ":login" => $login,
            ":password" => $cryptedPassword
        ));

        if($statement->rowCount() === 1){

            $result = "user - finded, recieve new cookie";

            $tokenVal = md5(time().$cryptedPassword);
            $updateQuery = "UPDATE users SET `admintoken` = :admintoken , `last_logged` = CURRENT_TIMESTAMP
                              WHERE `login` = :login";

            $statement = $this->db->prepare($updateQuery);
            $statement->execute(array(":admintoken" => $tokenVal, ":login" => $login));

            $result = $tokenVal;

        }else{

            $result = false;
        }

        return $result;

    }

    /**
     * Method to find if admintoken exist(returnd user or ajax calls)
     * if admintoken finded - generated new admintoken and return it
     * else returned false
     */
    public function findAdminToken($admintoken)
    {

        $searchQuery = "SELECT `login` FROM users 
                  WHERE `admintoken`= :admintoken";

        $statement = $this->db->prepare($searchQuery);

        $statement->execute(array(
            ":admintoken" => $admintoken
        ));

        if($statement->rowCount() === 1){
            $result = "Finded";

        }else{
            $result = false;
        }

        return $result;

    }

}
