
<?php
/*
  ArduinoLinuxSerial - This is an helper class for php-Arduino
                 communications on Linux.
  Copyright (c) 2017 Marco Sillano.  All right reserved.

  This library is free software; you can redistribute it and/or
  modify it under the terms of the GNU Lesser General Public
  License as published by the Free Software Foundation; either
  version 2.1 of the License, or (at your option) any later version.

  This library is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
  Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public
  License along with this library; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//  This Class allows communications from php running on Linux (master)
//  to an Arduino board via serial USB.
//  The master message max length is 60 char (serial Arduino rx buffer limit),
//  the response (from Arduino) as no limits.
//  The protocol uses CRC8 (transparently) to insure correctness.
//  In case of error the message is resended 3 times before exit in error state.
//  A Bash script (serialArduino.sh) is used to setup the USB device on Linux, after
//  startup or USB connection.
//  The serial communication is open for every message: in the Bash script 
//  the DTR pin is disabled to avoid the auto reset.
//  (see: https://playground.arduino.cc/Main/DisablingAutoResetOnSerialConnection)
//
// SETUP 
//    I used a TVbox MXQ: Android 4.4.2, Linux Ububuntu@server 3.10.33.
//    To easy work i use WinSCP on Windows (https://winscp.net/eng/download.php)
//    and 'Rooted SSH/SFTP Daemon' (https://www.apkmonk.com/app/web.oss.sshsftpDaemon/)
//    I installed also 'Palapa WEB server' 
//    (https://play.google.com/store/apps/details?id=com.alfanla.android.pws&hl=en) and
//     phpMyAdmin 4.1.14.1 (this version is required by Palapa).
//  
// 1) You must found the linux serial device for your Arduino board connected via USB:
//    disconect and reconnect then you use the command 'dmesg | grep -i usb' or 
//    'dmesg | grep -i tty'
//      (see https://unix.stackexchange.com/questions/144029/command-to-determine-ports-of-a-device-like-dev-ttyusb0)
//    note: on my MXQ, an Arduino clone using the CH340/CH341 chip (id 1A86:7523) do not create
//    any device, for lack of drivers. 
// 2) test stty: on my MXQ I installed 'Terminal IDE' wich contains 'Busybox'
//    wich contains stty and more Linux commands for Android.
//   (see https://play.google.com/store/apps/details?id=com.spartacusrex.spartacuside&hl=en)
// 3) modify serialArduino.sh: set starting #!/bin/sh, choose where to copy it, test it.
//    In my MXQ i used /data/myfolder/serialArduino.sh.
//    It MUST work with any user: see the status.txt file, updated from any run.
//    I used  'FX-File explorer root Add-on' 
//    (see https://play.google.com/store/apps/details?id=nextapp.fx.rr&hl=en)
// 4) Update LINUXSCRIPT in ArduinoLinuxSerial.php
// 5) Set same baud rate in serialArduino.sh and in Arduino sketch
// 6) You can run the sketch 'serialTest.ino' and control the LED13 using the page testSerial.php.
//
// TROUBLESHOOTING
//  ERROR LCRC: bad CRC Linux -> Arduino
//  ERROR ACRC: bad CRC Arduino -> Linux
//  ERROR CODE: sended a command code not implemented in Arduino
//  ERROR SERIAL: USB not plugged, Arduino not running
//         or fail in open the serial device.
//         In this case the file 'status.txt' contains: 'stty: /dev/ttyACM0: Inappropriate ioctl for device' ??
//         solution: disconect and reconnect Arduino USB
//
// see also USBphpTunnel, (https://github.com/msillano/USBphpTunnel) where Arduino is master.

  define('LINUXSCRIPT', '/data/myfolder/serialArduino.sh' );  // this can change

class ArduinoLinuxSerial{
  
  private $theDevice;
  
  public function ArduinoLinuxSerial($device){
   error_reporting(E_ALL);
   $this->theDevice = $device;
   shell_exec("sh -c ".LINUXSCRIPT);
   }
	
// adds CRC to txdata
private function addCRC($txdata){
  $crc = 0;                        // crc seed, same here and in testSerial.ino
  for ($i = 0; $i < strlen($txdata); $i++){
      $crc ^= ord($txdata[$i]);
      }
  $crc = "00".dechex($crc);    
  return ($txdata.substr($crc,-2));
}
	
// tests and cut CRC from rxdata
private function testCRC($rxdata){
  $data = substr($rxdata,0,-2);
  if ( strcmp ($this->addCRC($data),$rxdata ) == 0)
      return $data;
  else
      return false;   
}

//  low level send receive (adds, cuts '\n')
private function arduinoTXRX($data){
  if (!$as = fopen($this->theDevice,'w+b')){
    return "";
    }
  fwrite($as, $data."\x0A");
  $in = fgets($as);  // wait for reply
  fclose($as);
  return trim($in);
  }

// main function send receive, retry 3 times
public function sendMessage($messg){
//  $id = rand(65,90);   //  random ID for messages
  $tosend = $this->addCRC($messg);      // add CRC
  for ($i = 0; $i<2; $i++){
    $res = $this->arduinoTXRX($tosend);  // send-receive 2 times
    if (($res != "") && ($rx = $this->testCRC($res)))   // cuts CRC. The = is OK: it is assignation, not test
          return($rx);
    usleep(100);
    }
  $res = $this->arduinoTXRX($tosend);  //last send-receive
  if ($res == "")
    return "ERROR SERIAL" ; 
  if ($rx = $this->testCRC($res)){     // The = is OK: it is assignation, not test
    return($rx);
    }
  return "ERROR ACRC" ;   
  }
}
?>
