<?php

declare( strict_types=1 );

namespace Dekode\NinjaForms\Azure;

use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CopyBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

/**
 *
 * @package Dekode\NinjaForms\Azure
 */
class Controller
{
    static $instance = null;
    static $blobClient = false;

    public function errorLog($message)
    {
        if(defined('WP_DEBUG') && WP_DEBUG) {
            error_log($message);
        }
    }
    protected function getContainerName()
    {
        $parse = parse_url(get_site_url());
        $domain = $parse['host'];

        // Naming conventions, only letters
        // Container names must start or end with a letter or number, and can contain only letters, numbers, and the dash (-) character.
        // https://docs.microsoft.com/en-us/rest/api/storageservices/naming-and-referencing-containers--blobs--and-metadata
        $containerName = strtolower(preg_replace("/[^a-zA-Z0-9-]/", "-", $domain));

        return $containerName;
    }

    protected function getBlobClient()
    {
        if(self::$blobClient === false) {
              $blobClient = BlobRestProxy::createBlobService(DEKODE_NINJAFORMS_AZURE_CONNECTION_STRING);

              $containerName = $this->getContainerName();

            try {
                $blobClient->getContainerProperties($containerName);
            } catch(ServiceException $e) {
                $createContainerOptions = new CreateContainerOptions();
                $createContainerOptions->setPublicAccess(PublicAccessType::BLOBS_ONLY);

                // $createContainerOptions->addMetaData("key1", "value1");

                $blobClient->createContainer($containerName, $createContainerOptions);
            }

            self::$blobClient = $blobClient;
        }

        return self::$blobClient;
    }

    public function getBlobUrl($blobName)
    {
        $blobClient = $this->getBlobClient();
        $containerName = $this->getContainerName();

        $sourceBlobPath = $blobClient->getBlobUrl(
            $containerName,
            $blobName
        );
        return $sourceBlobPath;

        // $listBlobsOptions = new ListBlobsOptions();
        // $listBlobsOptions->setPrefix($blobName);
        // $blob_list = $blobClient->listBlobs($containerName, $listBlobsOptions);
        // $blobs = $blob_list->getBlobs();

        // $blobUrl = false;
        // foreach($blobs as $blob)
        // {
        //     if($blob->getName() === $blobName) {
        //         $blobUrl = $blob->getUrl();
        //     }
        // }

        // return $blobUrl;
    }

    public function existsFile($blobName)
    {
        $blobClient = $this->getBlobClient();
        $containerName = $this->getContainerName();

        $blob = false;

        try {
            $blob = $blobClient->getBlob($containerName, $blobName);
        } catch(ServiceException $e) {

        }


        return $blob ? true : false;
    }


    public function renameFile($oldName, $newName)
    {
        $blobClient = $this->getBlobClient();
        $containerName = $this->getContainerName();

        $blobClient->copyBlob($containerName, $newName, $containerName, $oldName);
        $blobClient->deleteBlob($containerName, $oldName);
    }


    public function deleteFile($blobName)
    {
        $blobClient = $this->getBlobClient();
        $containerName = $this->getContainerName();
        $blobClient->deleteBlob($containerName, $blobName);
    }

    public function generateName($fileName, $folder = 'temp')
    {
        $containerName = $this->getContainerName();
        $blogId = get_current_blog_id();

        // Sanitize the filename for encoding
        $fileName   = sanitize_file_name(basename($fileName));

        $fileName = wp_hash($containerName . $fileName . wp_rand(1, 10000)) . '-' . $fileName;

        $fileNameParts = [$blogId, $folder, $fileName];
        $blobName = join('/', $fileNameParts);

        return $blobName;
    }

    public function uploadFile($fileName, $content)
    {
        $blobClient = $this->getBlobClient();
        $containerName = $this->getContainerName();

        $newBlobName = $this->generateName($fileName, join('/', [date('Y'), date('m')]));
        $tempBlobName = $this->generateName($fileName, 'temp');

        $fileType = wp_check_filetype($fileName);
        $contentType = 'plain/text';

        if($fileType && isset($fileType['type']) && $fileType['type'] ) {
            $contentType = $fileType['type'];
        }

        $blobOptions = new CreateBlockBlobOptions();
        $blobOptions->setContentType($contentType);
        $blob = $blobClient->createBlockBlob($containerName, $tempBlobName, $content, $blobOptions);

        if(!$blob) {
            throw new \Exception('File not saved');
        }

        $blobUrl = $this->getBlobUrl($tempBlobName);

        // var_export([$blobUrl, $fileName, $containerName, $blob]);

        return [
        'url' => $blobUrl,
        'tempBlobName' => $tempBlobName,
        'newBlobName' => $newBlobName,
        ];
    }
}

Controller::$instance = new Controller();
