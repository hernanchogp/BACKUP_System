<?php
chdir(__DIR__);

require_once '../class/S3_Class.php';
require_once '../class/Print_Class.php';
require_once '../config/config.php';

$objPrint = new Print_Class(2, 'Start BACKUP transfer to S3 ...');

/**
 * Creacion de directorio que almacenara los recursos por dia
 */

if (!is_dir(PATH_BACKUP) || !file_exists(PATH_BACKUP)) {

    $objPrint->render('The BACKUP directory does not exist or is not valid. ' . PATH_BACKUP, 5);
    exit;
}

$pathBK = PATH_BACKUP;
$nameZip = PATH_BACKUP . '/' . 'backup_moodle.tar.gz';
if (!file_exists($nameZip)) {
    $objPrint->render('The File BACKUP does not exist or is not valid. ' . $nameZip, 5);
    exit;
}

$objPrint->render('Validation finish backup directory. ', 1);

try {
    $refBucket = date('Ymd');
    $objS3 = new S3_class(S3_ACCESSKEY, S3_SECRETKEY, S3_BUCKET);
    $salida = $objS3->cargarArchivoS3($nameZip, $refBucket);
    $objPrint->render($salida, 2);
    //unlink($nameZip);
} catch (Exception $ex) {
    $objPrint->render('A novelty was file or S3 transfer ' . $ex->getMessage(), 2);
    die;
}
$objPrint->render('The BACKUP generation process ends successfully for the day... ' . $refBucket, 2);

unset($objPrint);
unset($objS3);
//*/
