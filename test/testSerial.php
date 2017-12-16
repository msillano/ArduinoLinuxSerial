<html>
<head>

<?php
$d = dirname(__FILE__);
include("$d/ArduinoLinuxSerial.php");

// new object
$ArduinoSerial = new ArduinoLinuxSerial('/dev/ttyACM0');  // Linux device

$led = 'LED ??';

if (isset($_GET['LED'])){
   if ($_GET['LED'] == 'ON'){
   $led = $ArduinoSerial->sendMessage('1 test');  // command 1: led 13 ON, ' test' is dummy 
   if (isset($_GET['AUTO']))              // blinking 3 sec.
        header('refresh: 3; url=index.php?AUTO=BLINK&LED=OFF');  
   }
   if ($_GET['LED'] == 'OFF'){
   $led = $ArduinoSerial->sendMessage('2 test'); // command 2: led 13 OFF, ' test' is dummy 
   if (isset($_GET['AUTO']))             // blinking 3 sec.
        header('refresh: 3; url=index.php?AUTO=BLINK&LED=ON');  
   }
}
?>

</head>
<body
 style="font-family: -moz-fixed; white-space: -moz-pre-wrap; width: 72ch;">
<pre></pre>
<h1> TEST ARDUINO Linux Serial</h1>
&nbsp;&nbsp;&nbsp;&nbsp;<b><i><?php echo $led; ?></i></b><br><br>
<form action='index.php' method='GET'>
<select id='led' name='LED'>
<option id='0'>OFF</option>
<option id='1'>ON</option>
</select>&nbsp;&nbsp;&nbsp;&nbsp;
<input type='submit' value='SET'>&nbsp;&nbsp;&nbsp;&nbsp;
<input type='submit' name='AUTO' value='BLINK'>
</form>

<br>
</body>
</html>

