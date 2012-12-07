<!-- PHP Wrapper - 500 Server Error -->
<html><head><title>500 Server Error</title></head>
<body bgcolor=white>
<h1>500 Server Error</h1>

SERVER IS UPDATING.STAND BY THIS COULD TAKE A WHILE
<hr>

<?php
echo "URL: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]<br>\n";
echo `checksuexec`;
?>

</body></html>
