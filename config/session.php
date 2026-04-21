<?php
ini_set('session.cookie_httponly',1);
ini_set('session.use_strict_mode',1);
ini_set('session.cookie_samesite','Strict');
if(!empty($_SERVER['HTTPS'])) ini_set('session.cookie_secure',1);
session_start();
