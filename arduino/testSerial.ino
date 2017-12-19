
// Lo sketch usa 7144 byte (24%) dello spazio disponibile per i programmi. Il massimo è 28672 byte.
// Le variabili globali usano 208 byte (8%) di memoria dinamica, lasciando altri 2352 byte liberi per le variabili locali. Il massimo è 2560 byte.

// To test ArduinoLinuxSerial.php class, use this with testSerial.php
int ledPin = 13;   // Arduino yun red led
// for serial messages 
char  r1[] = "LED ON";
char  r2[] = "LED OFF";
char err[] = "ERROR CODE";

String inputString = "";         // a String to hold incoming data
boolean stringComplete = false;  // whether the string is complete

void setup() {
  pinMode(ledPin, OUTPUT);
  // reserve 64 bytes for the inputString (same as Serial buffer)
  inputString.reserve(64);
  // initialize serial:
  Serial.begin(115200);          // see serialArduino.sh
  while (!Serial) {
    ; // wait for serial port to connect. Needed for native USB port only
  }
}

//  The simple serial protocol used in test:
//  Input message from php "3xxxxcc"  - sintax: <0..9: command> [<moredata>]<crc-hex><\n>, max 64 bytes
//  Arduino MUST send always a response message:
//  Output message from Arduino "LED ONcc" - sintax: <payload><crc-hex><\n>, any size
//  Of course command, moredata, payload can change to suit needs.
//  Limits: not '\n': it is used as terminator; in <payload> 'ERR'... is reserved to ERROR messages (see ArduinoLinuxSerial.php)

void loop_messages()
{
  inputString.trim();
  String inmess = cutCRC(inputString);  // tests and cts CRC
  String payload = inmess;
  if (!inmess.startsWith("ERR")) {
    switch (inmess.charAt(0))       // here commands switch
    {
      case '1':
        payload = do_command1();    // does command, returns answer
        break;
      case '2':
        payload = do_command2();     // does command, returns answer
        break;
      /* ... more ...*/
      default:
        payload = String(err);       // bad command code
    }
  }
  Serial.print(addCRC(payload));      // send answer
  Serial.write(10);                   // add '\n'
  //  Serial.write(4);
}

void loop() {
  while (Serial.available()) {
    // get the new byte:
    char inChar = (char)Serial.read();
    if (inChar == '\n') {
      // if the incoming character is a newline, set a flag 
      stringComplete = true;
    } else
      // add it to the inputString:
      inputString += inChar;
  }

  if (stringComplete) {
    loop_messages();   // executes incoming message, send answer    
    inputString = "";  // clear the string:
    stringComplete = false;
  }

  // more loop actions
}

// ============================ locals, used by loop_messages, mybe better to put it in a new library

String addCRC(String txmess) {
  byte  crc = 0;        // CRC seed: see ArduinoLinuxSerial.php
  for (unsigned int i = 0; i < txmess.length(); i++) crc ^= (byte)txmess.charAt(i); // the simplest CRC, only XOR
  String str_crc = String(crc, HEX);
  if (str_crc.length() == 1) return txmess + "0" + str_crc;
  return txmess + str_crc;
}

String cutCRC(String rxmess) {
  String clean = rxmess.substring(0, rxmess.length() - 2);
  if ( rxmess.equals(addCRC(clean)))
    return clean;
  return String("ERROR LCRC");
}

//==================================== do commands functions: ON / OFF LED13
// command 1: LED13 ON
String do_command1() {
  digitalWrite(ledPin, HIGH);
  return String(r1);
}

// command 2: LED13 OFF
String do_command2() {
  digitalWrite(ledPin, LOW);
  return String(r2);
}


