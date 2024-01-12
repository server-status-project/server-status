<?php
/**
* Class that encapsulates everything that can be done with a user
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

  /**
   * Gets user data from database and creates the class
   * @param int $id user ID
   */
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

  /**
   * Returns username of this user
   * @return String username
   */
  public function get_username()
  {
    return $this->username;
  }
  
  /**
   * Returns whether this user is active
   * @return Boolean user active status
   */
  public function is_active()
  {
    return $this->active;
  }
  
  /**
   * Returns rank of this user
   * @return int rank
   */
  public function get_rank()
  {
    return $this->rank;
  }
  
  /**
   * Returns full name of this user
   * @return String name in "Name Surname" format
   */
  public function get_name()
  {
    return $this->name . " " . $this->surname;
  }

  /**
   * Toggles active status of this user. First checks if the user
   * making the change has permission to do that.
   * @return void
   */
  public function toggle()
  {
    global $mysqli, $message, $user;

    $id = $_GET['id'];
    if ($this->id!=$_SESSION['user'] && $user->get_rank()<=1 && ($user->get_rank()<$this->rank))
    {
      $stmt = $mysqli->prepare("UPDATE users SET active = !active WHERE id=?");
      $stmt->bind_param("i", $this->id);
      $stmt->execute();
      $stmt->close();
      header("Location: ".WEB_URL."/admin/?do=user&id=".$id);
    }else{
      $message = _("You don't have the permission to do that!");
    }
  }

  /**
   * Processes submitted form and adds user unless problem is encountered, 
   * calling this is possible only for Superadmin (other ranks cannot add users)
   * or when the installation script is being run. Also checks requirements
   * for username and email being unique and char limits.
   * @return void
   */
  public static function add()
  {
    global $user, $message, $mysqli;
    if (INSTALL_OVERRIDE || $user->get_rank()==0)
    {
      if (strlen(trim($_POST['name']))==0) {
        $messages[] = _("Name");
      }
      if(strlen(trim($_POST['surname']))==0) {
        $messages[] = _("Surname");
      }
      if(strlen(trim($_POST['email']))==0) {
        $messages[] = _("Email");
      }
      if(strlen(trim($_POST['password']))==0) {
        $messages[] = _("Password");
      }
      if(!isset($_POST['permission']))
      {
        $messages[] = _("Rank");
      }

      if (isset($messages)){
        $message = "Please enter ".implode(", ", $messages);
        return;
      }
      
      $name = $_POST['name'];
      $surname = $_POST['surname'];
      $username = $_POST['username'];
      $email = $_POST['email'];
      $pass = $_POST['password'];
        
      if (!filter_var($email, FILTER_VALIDATE_EMAIL))
      {
        $message = "Invalid email!";
        return;
      }

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

      if ($stmt->affected_rows==0)
      {
        $message = _("Username or email already used");
        return;
      }

      $to      = $email;
      $subject = _('User account created').' - '.NAME;
      $msg = sprintf(_("Hi %s!<br>"."Your account has been created. You can login with your email address at <a href=\"%s\">%s</a> with password %s - please change it as soon as possible."), $name." ".$surname,WEB_URL."/admin", WEB_URL."/admin", $pass);
      $headers = "Content-Type: text/html; charset=utf-8 ".PHP_EOL;
      $headers .= "MIME-Version: 1.0 ".PHP_EOL;
      $headers .= "From: ".MAILER_NAME.' <'.MAILER_ADDRESS.'>'.PHP_EOL;
      $headers .= "Reply-To: ".MAILER_NAME.' <'.MAILER_ADDRESS.'>'.PHP_EOL; 

      mail($to, $subject, $msg, $headers);
      if (!INSTALL_OVERRIDE) 
      {
        header("Location: ".WEB_URL."/admin/?do=settings");
      }
    }
    else {
      $message = _("You don't have the permission to do that!");
    }
  }

  /**
   * Processes submitted form and logs user in, unless the user is deactivated or wrong
   * password or email has been submitted. The script doesn't let anyone know which
   * field was wrong as it is not possible to verify email address from outside admin panel,
   * so this actually helps with security :)
   * @return void
   */
  public static function login()
  {
    global $message, $mysqli;
    if (!isset($_POST['email']) && !isset($_POST['pass']))
    {
      return;
    }

    if ((!isset($_POST['email']) || !isset($_POST['pass'])))
    {
      $message = _("Please fill in your email and password!");
      return;
    }
    $email = $_POST['email'];
    $pass = $_POST['pass'];

    $stmt = $mysqli->prepare("SELECT id,password_salt as salt,active FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $query = $stmt->get_result();

    if ($query->num_rows<1)
    {
      $message = _("Wrong email or password");
      return;
    }
      
    $result = $query->fetch_assoc();
    $salt = $result["salt"];
    $id =  $result["id"];
    $active =  $result["active"];

    if (!$active)
    {
      $message = _("Your account has been disabled. Please contact administrator.");
      return;
    }

    $hash = hash('sha256', $pass.$salt);
    $stmt = $mysqli->prepare("SELECT count(*) as count FROM users WHERE id=? AND password_hash=?");
    $stmt->bind_param("is", $id, $hash);
    $stmt->execute();
    $query = $stmt->get_result();

    if (!$query->fetch_assoc()['count'])
    {
      $message = _("Wrong email or password");
      return;
    }

    if (isset($_POST['remember'])&&$_POST['remember'])
    {
      $year = strtotime('+356 days', time());
      $token = Token::add($id, 'remember', $year);
      setcookie('token', $token, $year, "/");
      setcookie('user', $id, $year, "/");
    }

    $_SESSION['user'] = $id;
    header("Location: ".WEB_URL."/admin");
  }

  /**
   * Checks whether token is valid (this means is in database and associated
   * with the user) and sets session data if it is, so user remains logged in.
   * The script deletes the token either way.
   * @return void
   */
  public static function restore_session()
  {
    global $message;
    $id = $_COOKIE['user'];
    $token = $_COOKIE['token'];

    if (Token::validate($token, $id, "remember"))
    {
      $year = strtotime('+356 days', time());
      unset($_COOKIE['token']);
      $_SESSION['user'] = $id;
      $new_token = Token::add($id, 'remember', $year);
      setcookie('token', $new_token, $year, "/");
      setcookie('user', $id, $year, "/");
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
  /**
   * Renders settings for this user so it can be displayed in admin panel.
   * @return void
   */
  public function render_user_settings()
  {
    global $permissions, $user;
    ?>
    <div class="row user">
      <div class="col-md-2 col-md-offset-2"><img src="https://www.gravatar.com/avatar/<?php echo md5( strtolower( trim( $this->email ) ) );?>?s=160" 
      alt="<?php echo _("Profile picture");?>"></div>
      <div class="col-md-6">
      <?php if($this->id==$_SESSION['user']||$user->get_rank()<1){
      ?>
        <form action="<?php echo WEB_URL;?>/admin/?do=user&amp;id=<?php echo $this->id; ?>" method="POST">
          <div class="input-group">
            <div class="col-md-12">
              <div class="row">
                <label class="form-name" for="name"><?php echo _("Name"); ?></label>
                <label class="form-name" for="surname"><?php echo _("Surname"); ?></label>
              </div>
              <div class="row">
                <input type="text" name="name" placeholder="<?php echo _("Name"); ?>" 
                  title="<?php echo _("Name"); ?>" class="form-control form-name" 
                  value=<?php echo htmlspecialchars($this->name, ENT_QUOTES);?>>
                <input type="text" name="surname" placeholder="<?php echo _("Surname"); ?>" 
                  title="<?php echo _("Surname"); ?>" class="form-control form-name" 
                  value=<?php echo htmlspecialchars($this->surname, ENT_QUOTES);?>>        
              </div>
            </div>
          </div>
          <div class="input-group">
            <button type="submit" class="btn btn-primary pull-right"><?php echo _("Change name");?></button>
          </div>
        </form>
        <?php
        }else{
        ?>
         <h3><?php echo $this->name." ".$this->surname;?></h3>
        <?php
        }?>
      </div>
    </div>
    <form action="<?php echo WEB_URL;?>/admin/?do=user&amp;id=<?php echo $this->id; ?>" method="POST">
      <div class="row user">
        <div class="col-md-2 col-md-offset-2"><strong><?php echo _("Username");?></strong></div>
        <div class="col-md-6">
          <?php
          if ($this->id==$_SESSION['user'] || $user->get_rank()<1){?>
          <div class="input-group">
              <input type="text" class="form-control" name="username" required value="<?php echo htmlspecialchars($this->username, ENT_QUOTES);?>">
            <span class="input-group-btn">
              <button type="submit" class="btn btn-primary pull-right"><?php echo _("Change username");?></button>
            </span>
          </div>
        <?php
          }else{?><?php echo $this->username." ";
            if ($user->get_rank()>=1){
              echo "<i class='fa fa-".($this->active?"check success":"times danger")."'></i>";
            }
          }
        ?>
        </div>
      </div>
    </form>

    <form action="<?php echo WEB_URL;?>/admin/?do=user&id=<?php echo $this->id; ?>" method="POST">
      <div class="row user">
        <div class="col-md-2 col-md-offset-2"><strong><?php echo _("Role");?></strong></div>
        <div class="col-md-6"><?php if ($user->get_rank() == 0 && $this->id != $_SESSION['user']){?> 
        <div class="input-group"><select class="form-control" name="permission">
        <?php foreach ($permissions as $key => $value) {
          echo "<option value='$key' ".($key==$this->rank?"selected":"").">$value</option>";
        } ?>
        </select><span class="input-group-btn">
          <button type="submit" class="btn btn-primary pull-right"><?php echo _("Change role");?></button>
        </span>
      </div><?php }else{ echo $permissions[$this->rank];}?></div>
    </div>
  </form>

  <?php if($this->id==$_SESSION['user']||$user->get_rank()<1)
  {?>
    <form action="<?php echo WEB_URL;?>/admin/?do=user&amp;id=<?php echo $this->id; ?>" method="POST">
      <div class="row user">
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
  <?php }else
  {
    ?>
    <div class="row user">
      <div class="col-md-2 col-md-offset-2"><strong><?php echo _("Email");?></strong></div>
      <div class="col-md-6">
        <a href="mailto:<?php echo $this->email; ?>"><?php echo $this->email; ?></a>
      </div>
    </div>
    <?php
  }

  if($this->id==$_SESSION['user']){
  ?>

    <form action="<?php echo WEB_URL;?>/admin/?do=user" method="POST">
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
  
  if ($this->id!=$_SESSION['user'] && $user->get_rank()<=1 && ($user->get_rank()<$this->rank))
      {?>
  <div class="row">
      <div class="col-md-2 col-md-offset-2"></div>
      <div class="col-md-6">
        <?php
        if ($this->active){
          echo '<a href="'.WEB_URL.'/admin/?do=user&id='.$this->id.'&what=toggle" class="btn btn-danger">'._("Deactivate user")."</a>";
        }else{
          echo '<a href="'.WEB_URL.'/admin/?do=user&id='.$this->id.'&what=toggle" class="btn btn-success">'._("Activate user")."</a>";
        }
        ?>
      </div>
    </div>
    <?php }
  }

  /**
   * Changes username of user by POST[ID].
   * @return void
   */
  public function change_username()
  {
    global $mysqli, $message, $user;
    $id = $this->id;

    $stmt = $mysqli->prepare("SELECT count(*) FROM users WHERE username LIKE ?");
    $stmt->bind_param("s",$_POST["username"]);
    $stmt->execute();
    if ($stmt->num_rows > 0)
    {
      $message = _("This username is already taken.");
      return;
    }
    $stmt->close();

    if ($_SESSION['user'] != $id && $user->get_rank()>0)
    {
      $message = _("Cannot change username of other users!");
    }else{
      $stmt = $mysqli->prepare("UPDATE users SET username = ? WHERE id=?");
      $stmt->bind_param("si",$_POST["username"],$id);
      $stmt->execute();
      $stmt->close();
      header("Location:  ".WEB_URL."/admin/?do=user&id=".$id);
    }
  }

  /**
   * Changes name and surname of user by POST[ID].
   * @return void
   */
  public function change_name()
  {
    global $mysqli, $message, $user;
    if (strlen(trim($_POST['name']))==0) {
      $messages[] = _("Name");
    }
    if(strlen(trim($_POST['surname']))==0) {
      $messages[] = _("Surname");
    }

    if (!empty($messages))
    {
      $message = "Please enter ".implode(", ", $messages);
      return;
    }

    $id = $this->id;

    if ($_SESSION['user'] != $id && $user->get_rank()>0)
    {
      $message = _("Cannot change names of other users!");
    }else{
      $stmt = $mysqli->prepare("UPDATE users SET `name` = ?, `surname` = ?  WHERE id=?");
      $stmt->bind_param("ssi",$_POST["name"],$_POST["surname"],$id);
      $stmt->execute();
      $stmt->close();
      header("Location:  ".WEB_URL."/admin/?do=user&id=".$id);
    }
  }

  /**
   * Changes user password and deletes all remember tokens so all other sessions 
   * won't stay logged in without knowing new pass. Uses token when reseting password.
   * @param String $token
   * @return void
   */
  public function change_password($token = false)
  {
    global $mysqli, $message;
    $id = $this->id;
    if ($_POST['password']!=$_POST['password_repeat'])
    {
      $message = _("Passwords do not match!");
      return;
    }

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

          $stmt = $mysqli->prepare("DELETE FROM tokens WHERE user = ? AND data = 'remember'");
  		    $stmt->bind_param("d", $id);
  		    $stmt->execute();
          $stmt->get_result();
          
          User::logout();
        }
        else{
          $message = _("Wrong password!");
        }
      }
    }else{
      if (Token::validate($token, $id, "passwd"))
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

        $stmt = $mysqli->prepare("DELETE FROM tokens WHERE user = ? AND data = 'remember'");
  		  $stmt->bind_param("d", $id);
  		  $stmt->execute();
  		  $stmt->get_result();
      }
      else
      {
        $message = _("Invalid token detected, please retry your request from start!");
      }
      Token::delete($token);
    }
  }

  /**
   * Sends email with link for password reset, link is token protected and valid only once.
   * @return void
   */
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

    $token = Token::add($id, 'passwd', $time);

    $link = WEB_URL."/admin/?do=lost-password&id=$id&token=$token";
    $to      = $email;
    $user = new User($id);
    $subject = _('Reset password') . ' - '.NAME;
    $msg = sprintf(_( "Hi %s!<br>Below you will find link to change your password. The link is valid for 24hrs. If you didn't request this, feel free to ignore it. <br><br><a href=\"%s\">RESET PASSWORD</a><br><br>If the link doesn't work, copy &amp; paste it into your browser: <br>%s"), $user->get_name(), $link, $link);
    $headers = "Content-Type: text/html; charset=utf-8 ".PHP_EOL;
    $headers .= "MIME-Version: 1.0 ".PHP_EOL;
    $headers .= "From: ".MAILER_NAME.' <'.MAILER_ADDRESS.'>'.PHP_EOL;
    $headers .= "Reply-To: ".MAILER_NAME.' <'.MAILER_ADDRESS.'>'.PHP_EOL; 

    mail($to, $subject, $msg, $headers);
  } 

  /**
   * Sends email with link for email change confirmation (security reasons), link is token protected and valid only once.
   * @return void
   */
  public function email_link(){
    global $user, $mysqli;
    
    $email = $_POST['email'];
    $id = $this->id;

    if ($user->get_rank()<1 && $id!=$_SESSION['user'])
    {
      $stmt = $mysqli->prepare("UPDATE users SET email = ? WHERE id=?");
      $stmt->bind_param("sd", $email, $id);
      $stmt->execute();
      $stmt->get_result();
      header("Location: ".WEB_URL."/admin/?do=user&id=".$id);
      return;
    }

    $time = strtotime('+1 day', time());

    $token = Token::add($id, 'email;$email', $time);

    $link = WEB_URL."/admin/?do=change-email&id=$id&token=$token";
    $to      = $email;
    $subject = _('Email change').' - '.NAME;
    $msg = sprintf(_( "Hi %s!<br>Below you will find link to change your email. The link is valid for 24hrs. If you didn't request this, feel free to ignore it. <br><br><a href=\"%s\">CHANGE EMAIL</a><br><br>If the link doesn't work, copy &amp; paste it into your browser: <br>%s"), $user->get_name(), $link, $link);
    $headers = "Content-Type: text/html; charset=utf-8 ".PHP_EOL;
    $headers .= "MIME-Version: 1.0 ".PHP_EOL;
    $headers .= "From: ".MAILER_NAME.' <'.MAILER_ADDRESS.'>'.PHP_EOL;
    $headers .= "Reply-To: ".MAILER_NAME.' <'.MAILER_ADDRESS.'>'.PHP_EOL; 

    mail($to, $subject, $msg, $headers);
    return _('Confirmation email sent!');
  }

  /**
   * Changes email.
   * @return void
   */
  public function change_email()
  {
    global $mysqli, $message;
    $token = $_GET['token'];
    $id = $_GET['id'];

    if (Token::validate($token, $id, "email;%"))
    {
      $data = explode(";", Token::get_data($token, $id));

      $email = $data[1];

      $stmt = $mysqli->prepare("UPDATE users SET email = ? WHERE id=?");
      $stmt->bind_param("sd", $email, $id);
      $stmt->execute();
      $stmt->get_result();
      Token::delete($token);
      header("Location: ".WEB_URL."/admin/");
    }
    else
    {
      $message = _("Invalid token detected, please retry your request from start!");
    }

    Token::delete($token);

  }

  /**
   * Logs current user out.
   * @return void
   */
  public static function logout(){
    session_unset();
    if (isset($_COOKIE['token']))
    {
      $token = $_COOKIE['token'];
      Token::delete($token);
      unset($_COOKIE['user']);
      unset($_COOKIE['token']);
      setcookie('user', null, -1, '/');
      setcookie('token', null, -1, '/');
    }
    header("Location: ".WEB_URL."/admin");
  }

  /**
   * Changes permissions of current user - only super admin can do this, so it checks permission first.
   * @return void
   */
  public function change_permission(){
    global $mysqli, $message, $user;
    if ($user->get_rank()==0)
    {
      $permission = $_POST['permission'];
      $id = $_GET['id'];
      $stmt = $mysqli->prepare("UPDATE users SET permission=? WHERE id=?");
      $stmt->bind_param("si", $permission, $id);
      $stmt->execute();  
      header("Location: ".WEB_URL."/admin/?do=user&id=".$id);
    }
    else{
      $message = _("You don't have permission to do that!");
    }
  }
}          
