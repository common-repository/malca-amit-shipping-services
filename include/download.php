<?php
/*error_reporting(E_ALL); 
ini_set('display_errors', 1);*/
if(isset($_POST)){
    $is_file = false;

    if($_POST['type'] == 'bulk'){

        require(MALCA_FOLDER.'/vendor/autoload.php');
        require(MALCA_FOLDER.'/PDFMerger.php');

        $file_names = $_POST['filename'];
        
        $file_name = $filename ='Malca-labels.pdf';
       
        $pdf = new \Clegginabox\PDFMerger\PDFMerger;
       
        $i = 0;
       
        foreach($file_names as $files){
            $pdf->addPDF($files, 'all');
        }
        $pdf->merge('download',$filename);
        
        $is_file = true;
        
    }

    if($_POST['type'] == 'uniq'){
        $is_file = true;
        $filename = $_POST['filename'];  
        $file_name = basename( $filename );   
    }
    if($is_file){
        header("Expires: 0");
        header("Content-Type: application/octet-stream");
        header("Cache-Control: no-cache, no-store, must-revalidate"); 
        header('Cache-Control: pre-check=0, post-check=0, max-age=0', false); 
        header("Pragma: no-cache");   
        header("Content-type: application/force-download");
        header("Content-Disposition:attachment; filename=".$file_name);
        header("Content-Type: application/force-download");
        $fp = fopen($filename, "r");
        while (!feof($fp)){
            echo fread($fp, 65536);
            flush(); // this is essential for large downloads
        } 
        fclose($fp); 
        if($filename == 'Malca-labels.pdf') unlink($filename);
    }
}
exit;
?>