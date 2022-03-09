<?php
chdir(__DIR__);
require_once '../class/Mysql_Class.php';
require_once '../class/Moodle_Class.php';
require_once '../class/Zip_Class.php';
require_once '../class/S3_Class.php';
require_once '../class/Print_Class.php';
require_once '../config/config.php';

$dbhost = DB_HOST;
$dbname = DB_NAME;
$dbuser = DB_USER;
$dbpass = DB_PASSWORD;
$pathmoodle = PATH_MOODLE;
$path = '';

$objPrint = new Print_Class(5, 'Start BACKUP generation process...');

/**
 * Creacion de directorio que almacenara los recursos por dia
 */

if (!is_dir(PATH_BACKUP) || !file_exists(PATH_BACKUP)) {

    $objPrint->render('The BACKUP directory does not exist or is not valid. ' . PATH_BACKUP, 5);
    exit;
}

if (!is_array($pathmoodle)) {

    $objPrint->render("The directory Moodle It's not a object " . $pathmoodle, 5);
    exit;
}

$pathBK = PATH_BACKUP;

$objPrint->render('Validation finish backup directory. ', 1);

//Generar bk BD

$objPrint->render('Start DB dump generation... ' . $dbname, 2);
$objBD = new Mysql_Class($dbhost, $dbname, $dbuser, $dbpass, $pathBK);
try {
    $filedump = $objBD->generarDump();
} catch (Exception $ex) {
    die($filedump);
    $objPrint->render('A novelty was presented in the DUMP generation process.' . $ex->getMessage(), 2);
    die;
}
$objPrint->render('DUMP generation process Finish...', 2);

//Generar zip

$objPrint->render('Start ZIP generation process and upload to S3...', 3);

$fileSQL = $filedump;

array_push($pathmoodle, $fileSQL);

$objZip = new Zip_Class($pathmoodle, $pathBK);

try {
    $nameZip = $objZip->generateZip();
    $objPrint->render('Finish zip generation process... ' . basename($nameZip), 4);
    $objPrint->render('Start ZIP file transfer process to S3... ' . basename($nameZip), 5);
    $refBucket = date('Ymd');
    $objS3 = new S3_class(S3_ACCESSKEY, S3_SECRETKEY, S3_BUCKET);
    $objS3->cargarArchivoS3($nameZip, $refBucket);
    $objPrint->render('Finish ZIP file transfer process to S3...', 5);
    unlink($fileSQL);
    unlink($nameZip);
} catch (Exception $ex) {
    $objPrint->render('A novelty was generated in the construction of the ZIP file or S3 transfer ' . $ex->getMessage(), 4);
    die;
}
$objPrint->render('The BACKUP generation process ends successfully for the day... ' . $refBucket, 5);

unset($objMoodle);
unset($objBD);
//*/
