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
            'http' => [
                'timeout' => 600,          // 10 minutes timeout for operations
                'connect_timeout' => 120,  // 2 minutes for connection
                'read_timeout' => 600,     // 10 minutes for reading response
                'decode_content' => false, // Don't decode to save memory
                'verify' => true,          // Verify SSL certificates
            ],
            'retries' => [
                'mode' => 'standard',      // Retry on network errors
                'max_attempts' => 3,       // Retry up to 3 times
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

        // Handle Contents (files)
        if (isset($objects['Contents'])) {
            foreach ($objects['Contents'] as $object) {
                $key = $object['Key'];
                if (substr($key, -1) === '/') {
                    continue;
                }

                $visibility = $this->getFileVisibility($key);

                $files[] = [
                    'name' => basename($key),
                    'path' => $key,
                    'size' => $object['Size'],
                    'last_modified' => $object['LastModified'],
                    'visibility' => $visibility,
                ];
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

    public function uploadFile($file, $path, $visibility = 'public')
    {
        // Ensure we have enough time for large file uploads
        set_time_limit(600);

        $this->s3Client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $path,
            'Body' => fopen($file->getRealPath(), 'rb'),
            'ACL' => $visibility === 'public' ? 'public-read' : 'private',
            'ContentType' => $file->getMimeType(), // Set correct content type
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

    public function updateFileVisibility($path, $visibility)
    {
        $this->s3Client->putObjectAcl([
            'Bucket' => $this->bucket,
            'Key' => $path,
            'ACL' => $visibility === 'public' ? 'public-read' : 'private',
        ]);
    }

    public function getFileVisibility($path)
    {
        $acl = $this->s3Client->getObjectAcl([
            'Bucket' => $this->bucket,
            'Key' => $path,
        ]);

        foreach ($acl['Grants'] as $grant) {
            if (isset($grant['Grantee']['URI']) && $grant['Grantee']['URI'] === 'http://acs.amazonaws.com/groups/global/AllUsers') {
                return 'public';
            }
        }
        return 'private';
    }

    public function getPublicUrl($path)
    {
        // Check if file is public
        $visibility = $this->getFileVisibility($path);
        if ($visibility !== 'public') {
            return null;
        }

        // Generate a pre-signed URL that will be valid for 1 hour
        $command = $this->s3Client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $path
        ]);

        $request = $this->s3Client->createPresignedRequest($command, '+1 hour');
        $fullUrl = (string) $request->getUri();

        // Remove query parameters - split by '?' and take first part
        $urlParts = explode('?', $fullUrl, 2);
        return $urlParts[0];
    }
}
