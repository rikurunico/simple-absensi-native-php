<?php
// Destroy session and redirect to login page
session_destroy();
redirect('index.php?page=login');
