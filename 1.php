<?php
session_start();
//$_SESSION['test'] = 1;
echo $_SESSION['test'];
// $sessid = session_id();
setcookie("user", "Alex Porter", time()+3600,"/we");
setcookie("user", "Alex Porter", time()+3600,"/te");
