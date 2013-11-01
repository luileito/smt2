<?php
class LZW {

  // ALELUYAH! http://www.php.net/manual/en/function.mb-split.php#99851
  private static function mb_str_split($str) { 
    return preg_split('/(?<!^)(?!$)/u', $str); 
  }

  private static function charCodeAt($c, $i) {
    return self::uniord($c[$i]);
  }

  private static function unichr($i) {
    return mb_convert_encoding(pack('n', $i), 'UTF-8', 'UTF-16BE');
  }

  private static function uniord($c) {
    list(, $ord) = unpack('N', mb_convert_encoding($c, "UCS-4BE", 'UTF-8'));
    return $ord;
  }

  public static function compress($s) {
    $dict = array();
    $data = str_split($s."");
    $out = array();
    $currChar = "";
    $phrase = $data[0];
    $code = 256;
    for ($i = 1; $i < count($data); ++$i) {
        $currChar = $data[$i];
        if (isset($dict[$phrase.$currChar])) {
            $phrase .= $currChar;
        }
        else {
            $out[] = strlen($phrase) > 1 ? $dict[$phrase] : self::charCodeAt($phrase,0);
            $dict[$phrase.$currChar] = $code;
            $code++;
            $phrase = $currChar;
        }
    }
    $out[] = strlen($phrase) > 1 ? $dict[$phrase] : self::charCodeAt($phrase,0);
    
    for ($i = 0; $i < count($out); ++$i) {
        $out[$i] = self::unichr($out[$i]);
    }
    
    return implode("", $out);
  }

  public static function decompress($s) {
    $dict = array();
    $data = self::mb_str_split($s);
    $currChar = $data[0];
    $oldPhrase = $currChar;
    $out = array($currChar);
    $code = 256;
    $phrase = "";
    for ($i = 1; $i < count($data); ++$i) {
        $currCode = self::uniord($data[$i]);
        if ($currCode < 256) {
            $phrase = $data[$i];
        }
        else {
           $phrase = isset($dict[$currCode]) ? $dict[$currCode] : $oldPhrase.$currChar;
        }
        $out[] = $phrase;
        $currChar = $phrase[0];
        $dict[$code] = $oldPhrase.$currChar;
        $code++;
        $oldPhrase = $phrase;
    }
    return implode("", $out);
  }
}
?>
