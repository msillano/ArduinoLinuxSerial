# ArduinoLinuxSerial
  This is an helper class for php-Arduino communications on Linux

  After a week of googling and tests, i build this Class to communicate from php running on Linux (master) to an Arduino board via serial USB.
  
  The master message max length is 60 char (serial Arduino rx buffer limit), the response (from Arduino) as no limits. The '\n' char is used internally as terminator. The protocol uses CRC8 to insure correctness. In case of error the message is resended 3 times before exit in error state.
  
 A Bash script (serialArduino.sh) is used to setup the USB device on Linux, after startup or USB connection.
  The serial communication is open for every message: in the Bash script the DTR pin is disabled to avoid the Arduino auto reset.
  (see: https://playground.arduino.cc/Main/DisablingAutoResetOnSerialConnection)

 TROUBLESHOOTING
 
  -  ERROR LCRC: bad CRC Linux -> Arduino
  -  ERROR ACRC: bad CRC Arduino -> Linux
  -  ERROR CODE: sended a command code not implemented in Arduino
  -  ERROR SERIAL: USB not plugged, Arduino not running or php fail in open the serial device.
       In this case the file 'status.txt' contains: 'stty: /dev/ttyACM0: Inappropriate ioctl for device' ?? 
       Solution: disconect and reconnect Arduino USB

 SETUP

   see ArduinoLinuxSerial.php file.

CONCLUSIONS
   
    Now you can develop MySQL and web enabled Arduino applications only working on Arduino and PHP. 
    To keep ligth the Arduino Sketch, you can port all not realtime logic to PHP side.
    At the end your application will works on MXQ+Arduino UNO even 24/7 with only 20 Watt AC power, and can
    be controlled by smartphone via WiFi.
    What more?
    Enjoy.

 ## see also
  
  
I am very interested in using TVboxes (like MXQ) as dedicated WEB servers with Arduino extensions: so about (Linux + Android)-Arduino USB serial communication I tested many solutions:
  
     Using ArduinoLinuxSerial (https://github.com/msillano/ArduinoLinuxSerial) the Master is php, and you need the 
     serial driver (uses devices like /dev/ACMx or /dev/USBx). It is blocking the master and the php->Arduino message
     is limited to 60 char. Fastest.
  
     Using USBphpTunnel (https://github.com/msillano/USBphpTunnel) the master is Arduino, and the Android app uses 
     ports like /dev/bus/dev/00X/00Y, so you don't need the serial driver. It is blocking the master (arduino) and 
     it allows big messages. It works in background of the main php program. Useful for data logging.
     
     Using USBphpTunnel_Fifo (https://github.com/msillano/USBphpTunnel_fifo) the master is php. It is NOT blocking the 
     master, allows payload of 4k, allows concurrence. Works in background, and it is no fast (depending from polling 
     Arduno frequence, 5-15 sec. plus processing time). Well placed for long Arduino processes (like waiting user 
     actions).
     
