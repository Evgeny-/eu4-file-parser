<?php

class EU4Reader {
   protected $result;

   public function __construct($filename) {
      $this->file = file($filename);
      $this->result = $this->parse();
   }

   protected function getName ($string) {
      $string = explode("=", $string);

      return trim($string[0]);
   }

   protected function getNextEnd ($from=0) {

      for($i = $from; $i < count($this->file); $i++) {
         $string = $this->file[$i];

         if(trim($string) === '}') return $i;
      }

      return count($this->file);
   }

   protected function addArrayValue (&$array, $key, $value) {
      if(isset($array[$key])) {
         if(!is_array($array[$key])) $array[$key] = [$array[$key]];

         $array[$key][] = $value;
      }

      else $array[$key] = $value;
   }

   protected function parse ($from=0, $to=null) {
      if(!$to) $to = count($this->file);
      $length = $to - $from;
      $skipTo = 0;

      $res = [];

      for($i = 0; $i < $length; $i++) {
         $string = $this->file[$i + $from];

         if($i < $skipTo) continue;

         if(strpos($string, '={') !== false) {
            $fr = $from + $i + 1;
            $skipTo = $this->getNextEnd($fr) + 1;

            $res[$this->getName($string)] = $this->parse($fr, $skipTo - 1);
         }
         else {
            $string = trim($string);

            if($string === '}') continue;

            if(preg_match("/([a-z0-9\_]+)\=(.*)/im", $string, $matches)) {
               if(preg_match("/^\"(.*)\"$/im", $string, $matches2)) {
                  $this->addArrayValue($res, $matches[1], $matches2[1]);
               }
               else {
                  $this->addArrayValue($res, $matches[1], $matches[2]);
               }
            }
            elseif(preg_match("/^\"(.*)\"$/im", $string, $matches)) {
               $res[] = $matches[1];
            }
            elseif($string) {
               $res[] = $string;
            }
         }
      }

      return $res;
   }

   public function getResult() {
      return $this->result;
   }
}