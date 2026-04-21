<?php
//to protect pages in session
require "../config/session.php";
if (empty($_SESSION["user_id"])) {
  http_response_code(401);
  exit;
}
?>
<h2>Dashboard</h2>
<p>Welcome to the admin dashboard.</p>
<div id="stats"></div>
