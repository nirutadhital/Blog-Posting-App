<?php
// session_start();
require 'config/database.php';

//getting signup form data if signup button was clicked
if(isset($_POST['submit'])){
    $firstname=filter_var($_POST['firstname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $lastname=filter_var($_POST['lastname'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $username=filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email=filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $createpassword=filter_var($_POST['createpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmpassword=filter_var($_POST['confirmpassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $avatar=$_FILES['avatar'];
    // echo $firstname, $lastname, $username, $email, $createpassword, $confirmpassword;
    // var_dump($avatar);


    //validate input values
    if (!$firstname){
        $_SESSION['signup']="please enter your first name";
    } elseif (!$lastname){
        $_SESSION['signup']="please enter your last name";
    } elseif (!$username){
        $_SESSION['signup']="please enter your Username";
    } elseif (!$email){
        $_SESSION['signup']="please enter valid email";
    } elseif (strlen($createpassword)<8 || strlen($confirmpassword)<8){
        $_SESSION['signup']="password should be 8+ characters";
    } elseif(!$avatar['name']){
        $_SESSION['signup']="please add avatar";
    } else {
        //check if passwords do not match
        if ($createpassword !== $confirmpassword){
            $_SESSION['signup']="Passwords do not match";
        } else{
            //hash password
            $hashed_password = password_hash($createpassword, PASSWORD_DEFAULT);
            // echo $createpassword .'<br/>';
            // echo $hashed_password;

            //check if the username or email alerady exists in a database
            $user_check_query="SELECT * FROM users WHERE username='$username' OR email='$email'";
            $user_check_result=mysqli_query($connection,$user_check_query);
            if(mysqli_num_rows($user_check_result)>0){
                $_SESSION['signup'] = "username or email already exists";
            } else{
                 //work on avatar
                //rename avatar
                $time=time();//make each image name unique using current time stamp
                $avatar_name=$time . $avatar['name'];
                $avatar_tmp_name=$avatar['tmp_name'];
                $avatar_destination_path = "images/$avatar_name";

                //make sure file is an image
                $allowed_files=['png','jpg','jpeg'];
                $extension=explode ('.',$avatar_name);
                $extension=end($extension);
                if(in_array($extension, $allowed_files))
                {
                    //make sure image is not too large(1mb+)
                    if($avatar['size']<1000000){
                        //upload avtar
                        move_uploaded_file($avatar_tmp_name, $avatar_destination_path);
                    } else{
                        $_SESSION['signup'] ='file size too big.should be less than 1mb';
                    } 
                }else {
                        $_SESSION['signup']="file should be png,jpg or jpeg";
                    }
            }
        }
    }
    

    //rediret back to signup page if there was any problem
    if(isset($_SESSION['signup'])){
        //pass form data back to signup page
        $_SESSION['signup-data']=$_POST;
        header('location:' . ROOT_URL . 'signup.php');
        die();
    } else {
        //inset new user into user table
        $insert_user_query="INSERT INTO users SET firstname='$firstname', lastname='$lastname',
        username='$username', email='$email', password='$hashed_password', avatar='$avatar_name',is_admin=0";
        $insert_user_result= mysqli_query($connection, $insert_user_query);

        if(!mysqli_errno($connection)){
            //redirect to login page with success messgae
            $_SESSION['signup-success']="Registration successful .please log in";
            header('location:' . ROOT_URL . 'signin.php');
            die();
        }
    }
}else{
    //if button was  not clicked, bounce back to signup page
    header('location:' .ROOT_URL. 'signup.php');
    die();
}
?>