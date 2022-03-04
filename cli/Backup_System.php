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

if (is_dir(PATH_BACKUP)) {

    $path = PATH_BACKUP . 'BK_' . date('Ymd');

    if (!file_exists($path)) {
        if (!mkdir($path, 0777, true)) {

            $objPrint->render('Failed to create directory. ' . $path, 1);
            die;
        }
    } else {
        $objPrint->render('A BACKUP has already been generated for the current date. ' . $path, 5);

        die;
    }
}

$objPrint->render('Finish creation of backup directory. ', 1);

//Generar bk BD

$objPrint->render('Start DB dump generation... ' . $dbname, 2);
$objBD = new Mysql_Class($dbhost, $dbname, $dbuser, $dbpass, $path);
try {
    $nameDump = $objBD->generarDump();
} catch (Exception $ex) {

    $objPrint->render('A novelty was presented in the DUMP generation process.' . $ex, 2);
    die;
}
$objPrint->render('DUMP generation process Finish...', 2);

//Generar bk path moodle
$objPrint->render('Start Moodle resource copy process...', 3);
$objMoodle = new Moodle_Class($pathmoodle, $path);
try {
    $resultCopy = $objMoodle->copydirMoodle();
} catch (Exception $ex) {
    $objPrint->render('A novelty was generated with the copy of Moodle resources.' . $ex, 3);
    die;
}
$objPrint->render('Process copy of moodle resources Finish...', 3);

//Generar zip
$objPrint->render('Start ZIP generation process and upload to S3...', 4);
$objZip = new Zip_Class($path, $path);

try {
    $nameZip = $objZip->generateZip();
    $objPrint->render('Finish zip generation process... ' . basename($nameZip), 4);
    $objPrint->render('Start ZIP file transfer process to S3... ' . basename($nameZip), 5);
    $refBucket = date('Ymd');
    $objS3 = new S3_class(S3_ACCESSKEY, S3_SECRETKEY, S3_BUCKET);
    $objS3->cargarArchivoS3($nameZip, $refBucket);
    $objPrint->render('Finish ZIP file transfer process to S3...', 5);
} catch (Exception $ex) {
    $objPrint->render('A novelty was generated in the construction of the ZIP file or S3 transfer ' . $ex, 4);
    die;
}
$objPrint->render('The BACKUP generation process ends successfully for the day... ' . $refBucket, 5);

unset($objMoodle);
unset($objBD);
//*/
