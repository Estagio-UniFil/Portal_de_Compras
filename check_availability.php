<?php 
namespace Models;
include("includes/config.php");


class Users {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function checkEmailExists($email) {
        $query = $this->con->prepare("SELECT email FROM users WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $query->store_result();

        return $query->num_rows > 0;
    }
}

if (!empty($_POST["email"])) {
    $email = $_POST["email"];
    $user = new Users($con);

    if ($user->checkEmailExists($email)) {
        echo "<span style='color:red'> O e-mail já existe.</span>";
        echo "<script>$('#submit').prop('disabled',true);</script>";
    } else {
        echo "<span style='color:green'> E-mail disponível para inscrição.</span>";
        echo "<script>$('#submit').prop('disabled',false);</script>";
    }
}

if(!empty($_POST["contact"])) {
    $result = mysqli_query($con,"SELECT contactno FROM users WHERE contactno='".$_POST["contact"]."'");
    if(mysqli_num_rows($result) > 0) {
        echo "<span style='color:red'> Número já registrado.</span>";
    } else {
        echo "<span style='color:green'> Número disponível.</span>";
    }
}
?>
