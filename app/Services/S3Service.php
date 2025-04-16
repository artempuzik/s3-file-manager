<?php

namespace app\Services;

use Aws\S3\S3Client;

class S3Service
{
    protected $s3Client;
    protected $bucket;
    protected $folder;

    public function __construct()
    {
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => config('filesystems.disks.s3.region'),
            'endpoint' =>  config('filesystems.disks.s3.endpoint'),
            'use_path_style_endpoint' => false,
            'credentials' => [
                'key'    => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);
        $this->bucket = config('filesystems.disks.s3.bucket');
        $this->folder = config('filesystems.disks.s3.path');
    }

    public function listFiles($prefix = '')
    {
        $objects = $this->s3Client->listObjectsV2([
            'Bucket' => $this->bucket,
            'Prefix' => $prefix,
            'Delimiter' => '/',
        ]);

        $files = [];
        $folders = [];
        // Handle CommonPrefixes (folders)
        if (isset($objects['CommonPrefixes'])) {
            foreach ($objects['CommonPrefixes'] as $prefix) {
                $folders[] = [
                    'name' => basename(rtrim($prefix['Prefix'], '/')),
                    'path' => $prefix['Prefix'],
                ];
            }
        }

        // Handle Contents (files and empty folders)
        if (isset($objects['Contents'])) {
            foreach ($objects['Contents'] as $object) {
                $key = $object['Key'];
                if (substr($key, -1) === '/') {
                    // Skip empty folders that are already in CommonPrefixes
                    continue;
                } else {
                    // It's a file
                    $files[] = [
                        'name' => basename($key),
                        'path' => $key,
                        'size' => $object['Size'],
                        'last_modified' => $object['LastModified'],
                    ];
                }
            }
        }

        return [
            'files' => $files,
            'folders' => $folders,
        ];
    }

    public function createFolder($path)
    {
        $path = rtrim($path, '/') . '/';
        $this->s3Client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $path,
            'Body' => '',
        ]);
    }

    public function deleteFile($path)
    {
        $this->s3Client->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $path,
        ]);
    }

    public function getFileUrl($path)
    {
        return $this->s3Client->getObjectUrl($this->bucket, $path);
    }

    public function uploadFile($file, $path)
    {
        $this->s3Client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $path,
            'Body' => fopen($file->getRealPath(), 'rb'),
            'ACL' => 'public-read',
        ]);
    }

    public function downloadFile($path)
    {
        $result = $this->s3Client->getObject([
            'Bucket' => $this->bucket,
            'Key' => $path
        ]);
        
        return [
            'content' => $result['Body'],
            'contentType' => $result['ContentType'],
            'contentLength' => $result['ContentLength'],
            'filename' => basename($path)
        ];
    }
}
