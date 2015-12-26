<?php

class EU4Parser {
   protected $result;

   const START_BLOCK = "={";
   const END_BLOCK = "}";
   const PREG_VAR = "/([a-z0-9\_]+)\=(.*)/im";
   const PREG_STRING = "/^\"(.*)\"$/im";

   public function __construct($filename) {
      $this->file = file($filename);
      $this->result = $this->parse();
   }

   public function getResult() {
      return $this->result;
   }

   protected function getName ($string) {
      $string = explode("=", $string);

      return trim($string[0]);
   }

   protected function getBlockEnd ($from=0) {
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
      if($to === null) {
         $to = count($this->file);
      }

      $res = [];
      $skipUntil = 0;

      for($i = 0; $i < $to - $from; $i++) {
         $string = $this->file[$i + $from];

         if($i < $skipUntil) continue;

         if(strpos($string, static::START_BLOCK) !== false) {
            $blockStart = $from + $i + 1;
            $skipUntil = $this->getBlockEnd($blockStart) + 1;

            $res[$this->getName($string)] = $this->parse($blockStart, $skipUntil - 1);
         }
         else {
            $string = trim($string);

            if(!$string || $string === static::END_BLOCK) continue;

            if(preg_match(static::PREG_VAR, $string, $matches)) {
               if(preg_match(static::PREG_STRING, $string, $matches2)) {
                  $this->addArrayValue($res, $matches[1], $matches2[1]);
               }
               else {
                  $this->addArrayValue($res, $matches[1], $matches[2]);
               }
            }
            elseif(preg_match(static::PREG_STRING, $string, $matches)) {
               $res[] = $matches[1];
            }
            else {
               $res[] = $string;
            }
         }
      }

      return $res;
   }
}