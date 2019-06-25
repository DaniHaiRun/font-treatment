<?php
$config = json_decode(file_get_contents('./config.json'), true);
if(sizeof($config) == 0 ) {
  echo "La configuration n'est pas correcte";
} else {
  $filename =  basename($config['file_path']);
  if(!$config['file_path'] || !$config['output_path']) {
    echo "Vous devez ajouter l'url source et l'url de destination";
  } else if(!is_file($config['file_path'])) {
    echo "Impossible de trouver le fichier $filename";
  } else {
    if(!is_dir($config['output_path'])) {
      if(is_file($config['output_path'])) {
        echo "File output! Output configuration not needed\n";
      } else {
        echo "Make file output in source directory\n";
        $output = __DIR__."/".strtotime('now').".css";
        $config['output_path'] = $output;
      }
    } else {
      echo "Make file output\n";
      $output = $config['output_path']."/".strtotime('now').".css";
      $config['output_path'] = $output;
    }
    echo "Beginning file reading\n";
    $fn = fopen($config['file_path'],"r");
    echo "Opening output in write mode\n";
    file_put_contents($config['output_path'], '');
    $output = fopen($config['output_path'],"w");
    $prefix = $config['prefix'] ? $config['prefix']."-" : "";
    $line = 0;
    while(! feof($fn))  {
      $result = fgets($fn);
      echo "File content at line $line \n";
      if(strstr($result, "glyph-name")) {
        $content_array = explode(' ', $result);
        $class = ".";
        $code = "";
        foreach($content_array as $str) {
          if(strstr($str, "unicode")) {
            $textSplit = explode('=', $str);$textSplit = explode('=', $str);
            $code = str_replace('"&#x', '', str_replace(';"', '', $textSplit[1]));
          } else if(strstr($str, "glyph-name")) {
            $textSplit = explode('=', $str);
            $class = ".$prefix".str_ireplace('_', '-', str_ireplace('"', '', $textSplit[1]));
          }
        }
        echo "Contain glyph\n";
        echo "Put line : \n $class:before {\n\tcontent: \"\\$code\"\n}\n";
        fputs($output, "$class:before {\n\tcontent: \"\\$code\"\n}\n");
      }
      $line++;
    }
    echo "\nOutput : \n";
    echo $config['output_path'];
    echo "\nEnd process\n";
    fclose($fn);
    fclose($output);
  }
}