<?php
	// Login system for a single php file:
	// ----------------- Begin Login Section (add protected content after this section) -----------------
	@session_start();
    // --- start modifiable variables: ---
    // $credentials contains unsalted hash for the login "admin" and "password" (replace with your hashed credentials):
	$credentials = array('8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918' => '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8');
	$salt        = '';    // add random string here to salt your password hashes (vaguely more secure)
  $max_logins  = 5;     // maximum number of failed login attempts before ban
	$ban_time    = 24;    // ban hours
  $timeout     = 180;   // number of seconds before session timeout (since last active)
  $max         = 86400; // maximum length of login (even with activity)
    // --- end modifiable variables ---
	$message     = ''; // placeholder for login error/status messages
	if(@$_SESSION[$_COOKIE['PHPSESSID']]['auth']['ban_time'] && $_SESSION[$_COOKIE['PHPSESSID']]['auth']['ban_time'] > time()) exit; // this is not very secure
	if(isset($_GET["logout"])) LOGGED_IN(0); // log out
	if(isset($_REQUEST["login"])) LOG_IN(@$_POST['login'],@$_POST['password']); // log in
	if(!LOGGED_IN($timeout, $max)) SHOW_LOGIN();
	function LOG_IN($u,$p) {
		global $credentials,$max_logins,$ban_time;
		unset($_SESSION[$_COOKIE['PHPSESSID']]['auth']['fail_count']);
		if(isset($credentials[hash('SHA256',$salt.trim($u))]) && @$credentials[hash('SHA256',$salt.trim($u))] == hash('SHA256',$salt.trim($p))) { // good login
			$_SESSION[$_COOKIE['PHPSESSID']]['auth']['last_seen'] = time();
			$_SESSION[$_COOKIE['PHPSESSID']]['auth']['login_time'] = time();
			$_SESSION[$_COOKIE['PHPSESSID']]['auth']['login'] = trim($u);
			unset($_SESSION[$_COOKIE['PHPSESSID']]['auth']['fail_count'], $_SESSION[$_COOKIE['PHPSESSID']]['auth']['message']);
			return true;
		} // otherwise invalid login, check # attempts/ban:
		if(!isset($_SESSION[$_COOKIE['PHPSESSID']]['auth']['fail_count'])) $_SESSION[$_COOKIE['PHPSESSID']]['auth']['fail_count'] = 0;
		if(++$_SESSION[$_COOKIE['PHPSESSID']]['auth']['fail_count'] < $max_logins) $_SESSION[$_COOKIE['PHPSESSID']]['auth']['message'] = "Invalid login! Remaining opportunities: ".($max_logins - $_SESSION[$_COOKIE['PHPSESSID']]['auth']['fail_count']).'/'.$max_logins;
		else $_SESSION[$_COOKIE['PHPSESSID']]['auth']['ban_time'] = time() + $ban_time * 3600;
		SHOW_LOGIN();
	}
	function SHOW_LOGIN() {
		exit ('<html><head><style class="text/css">
				 body { margin: 0; display: flex; background: linear-gradient(to right, rgba(117,189,209,0.5) 0%, rgba(193,234,191,0.7) 100%), linear-gradient(to bottom, rgba(147,206,222,0) 0%, rgba(117,189,209,1) 41%, rgba(73,165,191,0.6) 100%); }
		         form { display: flexbox; margin: auto auto; vertical-align: middle; padding: 20px 30px; border-radius:10px; background: rgba(255,255,255,0.5); text-align: right; filter: drop-shadow(15px 10px 6px rgba(0,40,40,0.2)); } 
				 p,input { display: block; font-family: sans-serif; margin: 0 auto; }
				 input { margin: 5px 0px; padding: 5px 8px; }
		         input[type=text],input[type=password],input[type=submit] { border: 1px solid rgba(0,0,0,0.4); width: 100%; }
				 p,input[type=submit] { color: rgba(0,0,0,0.7); width: auto; } 
				 input[type=submit] { margin-left: auto; margin-right: 0; padding: 5px 25px; } 
		        </style></head>
		        <body><form method="POST" name="login">
				 <p>'.$_SESSION[$_COOKIE['PHPSESSID']]['auth']['message'].'</p>
				 <input type="hidden" name="login" value="login" /><input type="text" name="login" />
			     <input type="password" name="password" /><input type="submit" value="Log in..." />
			    </form></body></html>');
	}
	function LOGGED_IN($timeout=180, $max=86400) {
		if(@$_SESSION[$_COOKIE['PHPSESSID']]['auth']['login_time'] > 9999999 && 
		   time() - @$_SESSION[$_COOKIE['PHPSESSID']]['auth']['last_seen'] < $timeout && 
		   time() - @$_SESSION[$_COOKIE['PHPSESSID']]['auth']['login_time'] < $max ) {
			$_SESSION[$_COOKIE['PHPSESSID']]['auth']['last_seen'] = time();
			return true;
		} else {
			if(!isset($_SESSION[$_COOKIE['PHPSESSID']]['auth']['login_time'])) $_SESSION[$_COOKIE['PHPSESSID']]['auth']['message'] = 'You need to log in to access this resource.'; // new login
			else if($timeout == 0) $_SESSION[$_COOKIE['PHPSESSID']]['auth']['message'] = 'You have been logged out successfully.'; // log out
			else $_SESSION[$_COOKIE['PHPSESSID']]['auth']['message'] = 'Session expired. Please log in again.'; // time out
			$_SESSION[$_COOKIE['PHPSESSID']]['auth']['url'] = $_SERVER['REQUEST_URI'];
		}
		unset($_SESSION[$_COOKIE['PHPSESSID']]['auth']['last_seen'], $_SESSION[$_COOKIE['PHPSESSID']]['auth']['login_time']);
		if($timeout == 0) { header('location: '.$_SERVER['PHP_SELF']); exit; }
		return false;
	}
	// ----------------- End Login Section (add protected content after this line) -----------------
	echo '<h4>This content is protected.</h4><a href="?logout">log out...</a>'
?>
