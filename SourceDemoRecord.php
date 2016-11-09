<?
/**
 * Source & OrangeBox demos header reader.
 * Dev by PoLaRiTy
 * Modified by Kruzya
 *
 * Original: <https://developer.valvesoftware.com/wiki/DEM_Format#Example_of_use>
 */

define('DEMOTYPE_RIE',  0);
define('DEMOTYPE_TV',   1);

class SourceDemoRecord {
    var $DemoProtocol;              // Demo protocol version 
    var $NetworkProtocol;           // Network protocol version
    var $ServerName;                // HOSTNAME in case of TV, and IP:PORT or localhost:PORT in case of RIE (Record In eyes).
    var $ClientName;                // Client name or TV name.
    var $MapName;                   // Map name
    var $GameDir;                   // Root game directory
    var $Time;                      // Playback time (s)
    var $Ticks;                     // Number of ticks
    var $Frames;                    // Number of frames
    var $TickRate;                  // Tickrate
    var $Type = DEMOTYPE_RIE;       // TV or RIE ? (0 = RIE, 1 = TV)
    var $StatusAvailable = false;   // If available status command

    public function __construct($file = "./example.dem", $fast = true) {
        if (!file_exists($file))
            throw new Exception("File is not exists.");
        
        if ($this->ExtOfFile($file) != "dem")
            throw new Exception("Bad file format.");
        
        $hFile = fopen($file, "rb");
        if (!$hFile)
            throw new Exception("Unable to open file.");
        
        if ($this->ReadString($hFile, 8) != "HL2DEMO")
            throw new Exception("Invalid demo file.");
        
        $this->DemoProtocol     = $this->ReadInt($hFile);
        $this->NetworkProtocol  = $this->ReadInt($hFile);
        $this->ServerName         = $this->ReadString($hFile);
        $this->ClientName       = $this->ReadString($hFile);
        $this->MapName          = $this->ReadString($hFile);
        $this->GameDir          = $this->ReadString($hFile);
        $this->Time             = $this->ReadFloat($hFile);
        $this->Ticks            = $this->ReadInt($hFile);
        $this->Frames           = $this->ReadInt($hFile);
        $this->TickRate         = intval($this->Ticks / $this->Time);
        $this->Type             = (($this->IsIP($this->ServerName)) ? DEMOTYPE_RIE : DEMOTYPE_TV);
        
        /* Status available check */
        if (!$fast && $this->Type == DEMOTYPE_RIE) {
            while(!(($l = fgets($handle)) === false)) {
                if(stripos($l, "\x00status\x00") !== false) {
                    $infos->StatusAvailable = true;
                    break;
                }
            }
        }
    }
    
    private function ExtOfFile($filepath) {
        $str = explode('.',$filepath);
        return $str[count($str)-1];
    }
    
    private function ReadString($handle, $n = 260) {
        $buffer = "";
        for($d = 1; ((($char = fgetc($handle)) !== false) && ($d < $n)); $d++) $buffer .= $char;
        return trim($buffer);
    }

    private function ReadInt($handle, $n = 4) {
        $res = unpack("i", fread($handle, $n));
        return $res[1];
    }

    private function ReadFloat($handle) {
        $res = unpack("f", fread($handle, 4));
        return $res[1];
    }

    private function IsIP($string) {
        return preg_match('/(localhost|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\:[0-9]{1,5}/', $string);
    }
}
