<?php
/**
* Class for creating and rendering an incident
*/
class User
{
  private $id;
  private $name;
  private $surname;
  private $username;
  private $email;
  private $rank;
  private $active;

  function __construct($id)
  {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE id=?");
    $stmt->bind_param("d", $id);
    $stmt->execute();
    $query = $stmt->get_result();

    if (!$query->num_rows)
    {
      throw new Exception("User doesn't exist.");
      return;
    }

    $result = $query->fetch_array();
    $this->id = $id;
    $this->active = $result['active'];
    $this->name = $result['name'];
    $this->email = $result['email'];
    $this->surname = $result['surname'];
    $this->username = $result['username'];
    $this->rank = $result['permission'];
  }

  public function get_username()
  {
    return $this->username;
  }

  public function get_rank()
  {
    return $this->rank;
  }

  public function get_name()
  {
    return $this->name . " " . $this->surname;
  }

  public function toggle()
  {
    global $mysqli, $message, $user;
    $id = $_SESSION['user'];
    $stmt = $mysqli->prepare("SELECT permission FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $query = $stmt->get_result();
    $permission = $result['permission'];
    $id = $_GET['id'];
    if ($this->id!=$_SESSION['user'] && $user->get_rank()<=1 && ($user->get_rank()<$this->rank))
    {
      $stmt = $mysqli->prepare("UPDATE users SET active = !active WHERE id=?");
      $stmt->bind_param("i", $this->id);
      $stmt->execute();
      $stmt->close();
      header("Location: /admin/?do=user&id=".$id);
    }else{
      $message = _("You don't have the permission to do that!");
    }
  }

  public static function add()
  {
    global $user, $message, $mysqli;
    if (INSTALL_OVERRIDE || $user->get_rank()==0)
    {
      if (strlen(trim($_POST['name']))==0 || strlen(trim($_POST['surname']))==0 || strlen(trim($_POST['email']))==0 || strlen(trim($_POST['password']))==0 || !isset($_POST['permission']))
      {
        $message = _("Please enter all data!");
      }else{
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $pass = $_POST['password'];

        $variables = array();
        if (strlen($name)>50){
          $variables[] = 'name: 50';
        }
        if (strlen($surname)>50){
          $variables[] = 'surname: 50';
        }
        if (strlen($username)>50){
          $variables[] = 'username: 50';
        }
        if (strlen($email)>60){
          $variables[] = 'email: 60';
        }

        if (!empty($variables))
        {
          $message = _("Please mind the following character limits: ");
          $message .= implode(", ", $variables);
          return;
        }

        $salt = uniqid(mt_rand(), true);
        $hash = hash('sha256', $pass.$salt);
        $permission = $_POST['permission'];
        

        $stmt = $mysqli->prepare("INSERT INTO users values (NULL, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("ssssssi", $email, $username, $name, $surname, $hash, $salt, $permission);
        $stmt->execute();

        if ($stmt->affected_rows>0)
        {
          $to      = $email;
          $subject = _('User account created').' - '.NAME;
          $message = sprintf(_("Hi %s!<br>"."Your account has been created. You can login with your email address at <a href=\"%s\">%s</a> with password %s - please change it as soon as possible."), .$name." ".$surname,WEB_URL."/admin", WEB_URL."/admin", $pass);
          $headers = "Content-Type: text/html; charset=utf-8 ".PHP_EOL;
          $headers .= "MIME-Version: 1.0 ".PHP_EOL;
          $headers .= "From: ".MAILER_NAME.' <'.MAILER_ADDRESS.'>'.PHP_EOL;
          $headers .= "Reply-To: ".MAILER_NAME.' <'.MAILER_ADDRESS.'>'.PHP_EOL; 

          mail($to, $subject, $message, $headers);
          header("Location: /admin/?do=settings");
        }
        else{
          $message = _("Username or email already used");
        }
      }
    }
    else {
      $message = _("You don't have the permission to do that!");
    }
  }

  public static function login()
  {
    global $message, $mysqli;
    if (isset($_POST['email']))
    {
      $email = $_POST['email'];
      $pass = $_POST['pass'];

      $stmt = $mysqli->prepare("SELECT id,password_salt as salt,active FROM users WHERE email=?");
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $query = $stmt->get_result();
      if ($query->num_rows)
      {
        $result = $query->fetch_assoc();

        $salt = $result["salt"];
        $id =  $result["id"];
        $active =  $result["active"];
        if (!$active)
        {
          $message = _("Your account has been disabled. Please contact administrator.");
        }
        else
        {
          $hash = hash('sha256', $pass.$salt);

          $stmt = $mysqli->prepare("SELECT count(*) as count FROM users WHERE id=? AND password_hash=?");
          $stmt->bind_param("is", $id, $hash);
          $stmt->execute();
          $query = $stmt->get_result();
          if (!$query->fetch_assoc()['count'])
          {
            $message = _("Wrong email or password");
          }else
          {
            if (isset($_POST['remember'])&&$_POST['remember'])
            {
              $year = strtotime('+356 days', time());
              $salt = uniqid(mt_rand(), true);
              $token = hash('sha256', $id.$salt);
              setcookie('token', $token, $year, "/");
              setcookie('user', $id, $year, "/");
              Token::new($id, 'remember', $year);
            }
            $_SESSION['user'] = $id;
            header("Location: /admin");
          }
        }
      }
      else{
        $message = _("Wrong email or password");
      }
    }
  }

  public static function restore_session()
  {
    global $mysqli, $message;
    $id = $_COOKIE['user'];
    $token = $_COOKIE['token'];
    $time = time();
    if (Token::validate_token($token, $id, "remember"))
    {
      $year = strtotime('+356 days', $timestamp);
      unset($_COOKIE['token']);
      $_SESSION['user'] = $id;
      $salt = uniqid(mt_rand(), true);
      $token = hash('sha256', $id.$salt);
      setcookie('token', $token, $year);
      Token::new($id, 'remember', $year);
    }
    else
    {
      unset($_COOKIE['user']);
      unset($_COOKIE['token']);
      setcookie('user', null, -1, '/');
      setcookie('token', null, -1, '/');
      $message = _("Invalid token detected, please login again!");
    }
    
    Token::delete($token);
  }

  public function render_user_settings()
  {
    global $permissions, $user;
    ?>
    <div class="row">
      <div class="col-md-2 col-md-offset-2"><img src="https://www.gravatar.com/avatar/<?php echo md5( strtolower( trim( $this->email ) ) );?>" alt="<?php echo _("Profile picture");?>"></div>
      <div class="col-md-6"><h3><?php echo $this->name." ".$this->surname;?></h3></div>
    </div>
    <div class="row">
      <div class="col-md-2 col-md-offset-2"><strong><?php echo _("ID");?></strong></div>
      <div class="col-md-6"><?php echo $this->id; ?></div>
    </div>
    <div class="row">
      <div class="col-md-2 col-md-offset-2"><strong><?php echo _("Username");?></strong></div>
      <div class="col-md-6"><?php echo $this->username."&nbsp;"; if ($this->id!=$_SESSION['user'] && $user->get_rank()<=1 && ($user->get_rank()<$this->rank))
      {
        echo "<a href='/admin/?do=user&id=".$this->id."&what=toggle'>";
        echo "<i class='fa fa-".($this->active?"check success":"times danger")."'></i></a>";
      }else{
        echo "<i class='fa fa-".($this->active?"check success":"times danger")."'></i>";
      }?></div>
    </div>

    <form action="/admin/?do=user&id=<?php echo $this->id; ?>" method="POST">
      <div class="row">
        <div class="col-md-2 col-md-offset-2"><strong><?php echo _("Role");?></strong></div>
        <div class="col-md-6"><?php if ($user->get_rank() == 0 && $this->id != $_SESSION['user']){?> <div class="input-group"><select class="form-control" name="permission"><?php foreach ($permissions as $key => $value) {
          echo "<option value='$key' ".($key==$this->rank?"selected":"").">$value</option>";
        } ?>
        </select><span class="input-group-btn">
          <button type="submit" class="btn btn-primary pull-right"><?php echo _("Change role");?></button>
        </span>
      </div><?}else{ echo $permissions[$this->rank];}?></div>
    </div>
  </form>

  <?php if($this->id==$_SESSION['user'])
  {?>
    <form action="/admin/?do=user" method="POST">
      <div class="row">
        <div class="col-md-2 col-md-offset-2"><strong>Email</strong></div>
        <div class="col-md-6">
          <div class="input-group">
            <input type="email" class="form-control" name="email" value="<?php echo $this->email; ?>">
            <span class="input-group-btn">
              <button type="submit" class="btn btn-primary pull-right"><?php echo _("Change email");?></button>
            </span>
          </div>
        </div>
      </div>
    </form>
    <form action="/admin/?do=user" method="POST">
      <div class="row">
        <div class="col-md-2 col-md-offset-2"><strong><?php echo _("Password");?></strong></div>
        <div class="col-md-6">
          <label for="password"><?php echo _("Old password");?></label>
          <input id="password" placeholder="<?php echo _("Old password");?>" type="password" class="form-control" name="old_password">
          <label for="new_password"><?php echo _("New password");?></label>
          <input id="new_password" placeholder="<?php echo _("New password");?>" type="password" class="form-control" name="password">
          <label for="new_password_check"><?php echo _("Repeat password");?></label>
          <input id="new_password_check" placeholder="<?php echo _("Repeat password");?>" type="password" class="form-control" name="password_repeat">
          <button type="submit" class="btn btn-primary pull-right margin-top"><?php echo _("Change password");?></button>
        </div>
      </div>
    </form>
    <?php
  }
  else
  {
    ?>
    <div class="row">
      <div class="col-md-2 col-md-offset-2"><strong><?php echo _("Email");?></strong></div>
      <div class="col-md-6">
        <a href="mailto:<?php echo $this->email; ?>"><?php echo $this->email; ?></a>
      </div>
    </div>
    <?php
  }

  }


  public function change_password($token = false)
  {
    global $mysqli, $user, $message;
    $time = time();
    $id = $this->id;
    if ($_POST['password']!=$_POST['password_repeat'])
    {
      $message = _("Passwords do not match!");
    }else{
      if (!$token)
      {
        if ($_SESSION['user']!=$id)
        {
          $message = _("Cannot change password of other users!");
        }else{
          $stmt = $mysqli->prepare("SELECT password_salt as salt FROM users WHERE id=?");
          $stmt->bind_param("i", $id);
          $stmt->execute();
          $query = $stmt->get_result();

          $result = $query->fetch_assoc();
          $salt = $result['salt'];
          $pass = $_POST['old_password'];
          $hash = hash('sha256', $pass.$salt);

          $stmt = $mysqli->prepare("SELECT count(*) as count FROM users WHERE id=? AND password_hash = ?");
          $stmt->bind_param("is", $id, $hash);
          $stmt->execute();
          if ($stmt->get_result()->fetch_assoc()['count'])
          {
            $pass = $_POST['password'];
            $hash = hash('sha256', $pass.$salt);
            $stmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE id=?");
            $stmt->bind_param("si", $hash, $id);
            $stmt->execute();
            $stmt->close();
            User::logout();
          }
          else{
            $message = _("Wrong password!");
          }
        }
      }else{
        if (Token::validate_token($token, $id, "passwd"))
        {
          $stmt = $mysqli->prepare("SELECT password_salt as salt FROM users WHERE id=?");
          $stmt->bind_param("i", $id);
          $stmt->execute();
          $query = $stmt->get_result();
          $result = $query->fetch_assoc();
          $salt = $result['salt'];
          $pass = $_POST['password'];
          $hash = hash('sha256', $pass.$salt);

          $stmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE id=?");
          $stmt->bind_param("si", $hash,$id);
          $stmt->execute();
          $stmt->close();
        }
        else
        {
          $message = _("Invalid token detected, please retry your request from start!");
        }

        Token::delete($token);
      }
    }
  }

  public static function password_link()
  {
    global $mysqli;
    $email = $_POST['email'];

    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $query = $stmt->get_result();

    $id = $query->fetch_assoc()['id'];    
    $time = strtotime('+1 day', time());

    $token = Token::new($id, 'passwd', $time);

    $link = WEB_URL."/admin/?do=lost-password&id=$id&token=$token";
    $to      = $email;
    $user = new User($id);
    $subject = _('Reset password')' - '.NAME;
    $msg = sprintf(_( "Hi %s!<br>Below you will find link to change your password. The link is valid for 24hrs. If you didn't request this, feel free to ignore it. <br><br><a href=\"%s\">RESET PASSWORD</a><br><br>If the link doesn't work, copy & paste it into your browser: <br>%s"), $user->get_name(), $link, $link);
    $headers = "Content-Type: text/html; charset=utf-8 ".PHP_EOL;
    $headers .= "MIME-Version: 1.0 ".PHP_EOL;
    $headers .= "From: ".MAILER_NAME.' <'.MAILER_ADDRESS.'>'.PHP_EOL;
    $headers .= "Reply-To: ".MAILER_NAME.' <'.MAILER_ADDRESS.'>'.PHP_EOL; 

    mail($to, $subject, $msg, $headers);
  } 

  public function email_link(){
    global $mysqli;
    $email = $_POST['email'];
    $time = strtotime('+1 day', time());
    $id = $this->id;

    $token = Token::new($id, 'email;$email', $time);


    $link = WEB_URL."/admin/?do=change-email&id=$id&token=$token";
    $to      = $email;
    $subject = _('Email change').' - '.NAME;
    $msg = sprintf(_( "Hi %s!<br>Below you will find link to change your email. The link is valid for 24hrs. If you didn't request this, feel free to ignore it. <br><br><a href=\"%s\">RESET PASSWORD</a><br><br>If the link doesn't work, copy & paste it into your browser: <br>%s"), $user->get_name(), $link, $link);
    $headers = "Content-Type: text/html; charset=utf-8 ".PHP_EOL;
    $headers .= "MIME-Version: 1.0 ".PHP_EOL;
    $headers .= "From: ".MAILER_NAME.' <'.MAILER_ADDRESS.'>'.PHP_EOL;
    $headers .= "Reply-To: ".MAILER_NAME.' <'.MAILER_ADDRESS.'>'.PHP_EOL; 

    mail($to, $subject, $msg, $headers);
  }

  public function change_email()
  {
      //TODO: Get message from this somehow
    global $mysqli, $message;
    $time = time();
    $token = $_GET['token'];
    $id = $_GET['id'];

    if (Token::validate_token($token, $id, "email;%"))
    {
      $data = explode(";", $result['data']);

      $email = $data[1];

      $stmt = $mysqli->prepare("UPDATE users SET email = ? WHERE id=?");
      $stmt->bind_param("sd", $email, $id);
      $stmt->execute();
      $query = $stmt->get_result();
      Token::delete($token);
      header("Location: /admin/");
    }
    else
    {
      $message = _("Invalid token detected, please retry your request from start!");
    }

    Token::delete($token);

  }

  public static function logout(){
    global $mysqli;
    session_unset();
    $token = $_COOKIE['token'];
    $time = time();
    Token::delete($token);
    unset($_COOKIE['user']);
    unset($_COOKIE['token']);
    setcookie('user', null, -1, '/');
    setcookie('token', null, -1, '/');
    header("Location: /admin");
  }

  public function change_permission(){
    global $mysqli, $message, $user;
    if ($user->get_rank()==0)
    {
      $permission = $_POST['permission'];
      $id = $_GET['id'];
      $stmt = $mysqli->prepare("UPDATE users SET permission=? WHERE id=?");
      $stmt->bind_param("si", $permission, $id);
      $stmt->execute();  
      header("Location: /admin/?do=user&id=".$id);
    }
    else{
      $message = _("You don't have permission to do that!");
    }
  }
}          
