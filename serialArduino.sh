#!/system/bin/sh
 tty=/dev/ttyACM0
 if [[ -e $tty ]]
 then
   if [[ ! -w $tty ]]
   then
   su -c "chmod 777 $tty"     
   stty -F $tty cs8 115200  ignbrk -brkint -imaxbel -opost -onlcr -isig -icanon -iexten -echo -echoe -echok -echoctl -echoke noflsh -ixon -crtscts 
   stty -F $tty  -hupcl
   # -hupcl: eliminates the Arduino reboot openning serial 
   # to be updated
   stty  -F $tty &> /data/myfolder/status.txt
   fi
fi
