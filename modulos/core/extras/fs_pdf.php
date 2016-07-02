<?php

require_once 'modulos/core/extras/ezpdf/Cezpdf.php';

class fs_pdf
{
   
   public $pdf;
   public $table_header;
   public $table_rows;
   
   public function __construct($paper = 'a4', $orientation = 'portrait', $font = 'Helvetica')
   {
      if( !file_exists('tmp/'.FS_TMP_NAME.'pdf') )
      {
         mkdir('tmp/'.FS_TMP_NAME.'pdf');
      }
      
      $this->pdf = new Cezpdf($paper, $orientation);
      $this->pdf->selectFont("modulos/core/extras/ezpdf/fonts/".$font.".afm");
   }
   
   public function get_y()
   {
      return $this->pdf->y;
   }
   
   public function set_y($y)
   {
      $this->pdf->ezSetY($y);
   }
   
   public function show($filename = 'doc.pdf')
   {
      $this->pdf->ezStream( array('Content-Disposition' => $filename) );
   }
   
   public function save($filename)
   {
      if($filename)
      {
         if( file_exists($filename) )
            unlink($filename);
         
         $file = fopen($filename, 'a');
         if($file)
         {
            fwrite($file, $this->pdf->ezOutput());
            fclose($file);
            return TRUE;
         }
         else
            return TRUE;
      }
      else
         return FALSE;
   }
   
   public function center_text($word='', $tot_width=140)
   {
      if( strlen($word) == $tot_width )
      {
         return $word;
      }
      else if( strlen($word) < $tot_width )
      {
         return $this->center_text2($word, $tot_width);
      }
      else
      {
         $result = '';
         $nword = '';
         foreach( explode(' ', $word) as $aux )
         {
            if($nword == '')
            {
               $nword = $aux;
            }
            else if( strlen($nword) + strlen($aux) + 1 <= $tot_width )
            {
               $nword = $nword.' '.$aux;
            }
            else
            {
               if($result != '')
                  $result .= "\n";
               $result .= $this->center_text2($nword, $tot_width);
               $nword = $aux;
            }
         }
         if($nword != '')
         {
            if($result != '')
               $result .= "\n";
            $result .= $this->center_text2($nword, $tot_width);
         }
         return $result;
      }
   }
   
   private function center_text2($word='', $tot_width=140)
   {
      $symbol = " ";
      $middle = round($tot_width / 2);
      $length_word = strlen($word);
      $middle_word = round($length_word / 2);
      $last_position = $middle + $middle_word;
      $number_of_spaces = $middle - $middle_word;
      $result = sprintf("%'{$symbol}{$last_position}s", $word);
      for($i = 0; $i < $number_of_spaces; $i++)
      {
         $result .= "$symbol";
      }
      return $result;
   }
   
   public function new_table()
   {
      $this->table_header = array();
      $this->table_rows = array();
   }
   
   public function add_table_header($header)
   {
      $this->table_header = $header;
   }
   
   public function add_table_row($row)
   {
      $this->table_rows[] = $row;
   }
   
   public function save_table($options)
   {
      if( !$this->table_header )
      {
         foreach( array_keys($this->table_rows[0]) as $k )
            $this->table_header[$k] = '';
      }
      
      $this->pdf->ezTable($this->table_rows, $this->table_header, '', $options);
   }
}
