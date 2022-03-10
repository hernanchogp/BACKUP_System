<?php
require_once '../includes/aws/aws-autoloader.php';

use Aws\Exception\MultipartUploadException;
use Aws\S3\MultipartUploader;

class S3_class
{
    protected  $s3_AccesKey;
    protected  $s3_SecretKey;
    protected  $s3_Bucket;

    public function __construct($s3_AccesKey,  $s3_SecretKey,  $s3_Bucket)
    {
        $this->s3_AccesKey = $s3_AccesKey;
        $this->s3_SecretKey = $s3_SecretKey;
        $this->s3_Bucket = $s3_Bucket;
    }
    public function tamanoS3($path)
    {

        //
        $sharedConfig = [
            'region' => 'us-east-1',
            'version' => 'latest',
            'credentials' => [
                'key' => $this->s3_AccesKey,
                'secret' => $this->s3_SecretKey
            ]
        ];

        $sdk = new Aws\Sdk($sharedConfig);
        $s3Client = $sdk->createS3();

        $totalSize = 0;
        $objects = $s3Client->getBucket($this->s3_Bucket . '/' . $path);
        foreach ($objects as $name => $val) {
            if (strpos($name, 'directory/sub-directory') !== false) {
                $totalSize += $val['size'];
            }
        }

        //
        $totalSize = $totalSize / 1024;

        //
        unset($s3Client);
        unset($sdk);
        return $totalSize;
    }

    public function recuperarS3($file, $ext)
    {
        $sharedConfig = [
            'region' => 'us-east-1',
            'version' => 'latest',
            'credentials' => [
                'key' => $this->s3_AccesKey,
                'secret' => $this->s3_SecretKey
            ]
        ];
        $s3Client = new Aws\S3\S3Client($sharedConfig);
        try {
            // Get the object
            $cmd = $s3Client->getCommand('GetObject', [
                'Bucket' => $this->s3_Bucket,
                'Key' => $file
            ]);
            $request = $s3Client->createPresignedRequest($cmd, '+20 minutes');
            // Get the actual presigned-url
            $presignedUrl = (string) $request->getUri();
            return $presignedUrl;
        } catch (Exception $e) {
            return false;
        }
    }
    public function existenciaS3($file)
    {
        $retornar = "";
        $sharedConfig = [
            'region' => 'us-east-1',
            'version' => 'latest',
            'credentials' => [
                'key' => $this->s3_AccesKey,
                'secret' => $this->s3_SecretKey
            ]
        ];

        $sdk = new Aws\Sdk($sharedConfig);
        $s3Client = $sdk->createS3();

        try {
            // Get the object
            $s3Client->getObject([
                'Bucket' => $this->s3_Bucket,
                'Key' => $file,
                'Range' => 'bytes=0-99'
            ]);
            $retornar = true;
            $_SESSION["generales"]["mensajerror"] = '';
        } catch (Exception $e) {
            $retornar = false;
        }

        //
        unset($s3Client);
        unset($sdk);
        return $retornar;
    }
    public function cargarArchivoS3($file_Path, $refBucket)
    {
        $retornar = "";
        $sharedConfig = [
            'region' => 'us-east-1',
            'version' => 'latest',
            'credentials' => [
                'key' => $this->s3_AccesKey,
                'secret' => $this->s3_SecretKey
            ]
        ];
        $key = basename($file_Path);
        $sdk = new Aws\Sdk($sharedConfig);
        $s3Client = $sdk->createS3();

        try {
            $result = $s3Client->putObject([
                'Bucket' => $this->s3_Bucket,
                'Key'    => $refBucket . "/" . $key,
                'Body'   => fopen($file_Path, 'r')
            ]);
            $retornar = "Image uploaded successfully. Image path is: " . $result->get('ObjectURL');
        } catch (Aws\S3\Exception\S3Exception $e) {
            throw new Exception($e->getMessage());
        }
        //
        unset($s3Client);
        unset($sdk);
        return $retornar;
    }
    public function cargarArchivoS3Multiple($file_Path, $refBucket)
    {
        /*$retornar = "";
        $sharedConfig = [
            'region' => 'us-east-1',
            'version' => 'latest',
            'credentials' => [
                'key' => $this->s3_AccesKey,
                'secret' => $this->s3_SecretKey
            ]
        ];
        $key = basename($file_Path);
        $sdk = new Aws\Sdk($sharedConfig);
        $s3Client = $sdk->createS3();
        
        $uploader = new MultipartUploader($s3Client, $file_Path, [
            'Bucket' => $this->s3_Bucket,
            'Key'    => $refBucket . "/" . $key,
        ]);
        
        try {
            $result = $uploader->upload();
            $retornar = "Image uploaded successfully. Image path is: " . $result->get('ObjectURL');
        } catch (MultipartUploadException $e) {
            throw new Exception($e->getMessage());
        }
        unset($s3Client);
        unset($sdk);
        return $retornar;//*/

        $retornar = "";
        $sharedConfig = [
            'region' => 'us-east-1',
            'version' => 'latest',
            'credentials' => [
                'key' => $this->s3_AccesKey,
                'secret' => $this->s3_SecretKey
            ]
        ];
        $key = basename($file_Path);
        $sdk = new Aws\Sdk($sharedConfig);
        $s3Client = $sdk->createS3();

        $source = $file_Path;
        $uploader = new MultipartUploader($s3Client, $source, [
            'Bucket' => $this->s3_Bucket,
            'Key'    => $refBucket . "/" . $key,
        ]);

        //Recover from errors
        do {
            try {
                $result = $uploader->upload();
            } catch (MultipartUploadException $e) {
                $uploader = new MultipartUploader($s3Client, $source, [
                    'state' => $e->getState(),
                ]);
            }
        } while (!isset($result));

        //Abort a multipart upload if failed
        try {
            $result = $uploader->upload();
            $retornar = "Image uploaded successfully. Image path is: " . $result->get('ObjectURL');
        } catch (MultipartUploadException $e) {
            // State contains the "Bucket", "Key", and "UploadId"
            $params = $e->getState()->getId();
            $result = $s3Client->abortMultipartUpload($params);
            throw new Exception($result);
        }
        unset($s3Client);
        unset($sdk);
        return $retornar;
    }
}
